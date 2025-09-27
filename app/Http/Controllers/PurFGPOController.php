<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccounts;
use App\Models\PaymentVoucher;
use App\Models\ProductAttributes;
use App\Models\ProductCategory;
use App\Models\Products;
use App\Models\PurFGPO;
use App\Models\PurFGPOAttachements;
use App\Models\PurFGPODetails;
use App\Models\PurFGPORec;
use App\Models\PurFGPORecDetails;
use App\Models\PurFGPOVoucherDetails;
use App\Services\myPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurFGPOController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('status'); // Values: pending, partially received, completed, all (optional)

        $purpos = PurFGPO::with(['vendor', 'details.product', 'details.variation.attribute_values'])->get();

        foreach ($purpos as $po) {
            // Total ordered quantity (sum of qty in PO details)
            $totalOrderedQty = $po->details->sum('qty');

            // Total received quantity from pur_fgpos_rec_details
            $receivedQty = DB::table('pur_fgpos_rec_details')
                ->join('pur_fgpos_rec', 'pur_fgpos_rec.id', '=', 'pur_fgpos_rec_details.pur_fgpos_rec_id')
                ->where('pur_fgpos_rec.fgpo_id', $po->id)
                ->sum('pur_fgpos_rec_details.qty');

            // Determine status
            if ($receivedQty <= 0) {
                $po->status_text = 'Pending';
                $po->status_class = 'badge bg-danger text-light';
            } elseif ($receivedQty < $totalOrderedQty) {
                $po->status_text = 'Partially Received';
                $po->status_class = 'badge bg-warning text-dark';
            } else {
                $po->status_text = 'Completed';
                $po->status_class = 'badge bg-success';
            }
        }

        // Apply filter if needed
        if ($filter && strtolower($filter) !== 'all') {
            $purpos = $purpos->filter(function ($po) use ($filter) {
                return strtolower($po->status_text) === strtolower($filter);
            })->values(); // Reindex array
        }

        return view('purchasing.fg-po.index', compact('purpos', 'filter'));
    }

    public function create()
    {
        $coa = ChartOfAccounts::where('account_type','vendor')->get();  // Get all product categories
        $fabrics = Products::where('item_type', 'raw')->get();  // Get all product categories
        $articles = Products::whereIn('item_type', ['fg', 'mfg'])->get();
        $attributes = ProductAttributes::with('values')->get();
        $prodCat = ProductCategory::all();  // Get all product categories

        return view('purchasing.fg-po.create', compact('coa', 'fabrics', 'articles', 'attributes', 'prodCat'));
    }

    public function edit($id)
    {
        $purPo = PurFGPO::with([
            'details.product',
            'details.variation',
            'voucherDetails.product',
            'voucherDetails.purPO',
            'attachments',
            'challans'
        ])->findOrFail($id);

        $coa       = ChartOfAccounts::where('account_type','vendor')->get();
        $fabrics   = Products::where('item_type', 'raw')->get();
        $articles  = Products::whereIn('item_type', ['fg', 'mfg'])->get();
        $attributes= ProductAttributes::with('values')->get();
        $prodCat   = ProductCategory::all();

        return view('purchasing.fg-po.edit', compact('purPo', 'coa', 'fabrics', 'articles', 'attributes', 'prodCat'));
    }

    public function update(Request $request, $id)
    {
        $purPo = PurFGPO::findOrFail($id);

        $request->validate([
            'vendor_id' => 'required|exists:chart_of_accounts,id',
            'category_id' => 'required|exists:product_categories,id',
            'order_date' => 'required|date',
            'item_order' => 'required|array',
            'item_order.*.product_id' => 'required|exists:products,id',
            'item_order.*.variation_id' => 'required|exists:product_variations,id',
            'item_order.*.sku' => 'required|string',
            'item_order.*.qty' => 'required|integer|min:1',
            'voucher_amount' => 'required|numeric',
            'voucher_details' => 'required|array',
            'voucher_details.*.product_id' => 'required|exists:products,id',
            'voucher_details.*.qty' => 'required|numeric',
            'voucher_details.*.item_rate' => 'required|numeric',
        ]);

        DB::beginTransaction();

        try {
            \Log::info('Updating FGPO', ['fgpo_id' => $purPo->id, 'request' => $request->all()]);

            // 1️⃣ Update FGPO main record
            $purPo->update([
                'vendor_id' => $request->vendor_id,
                'order_date' => $request->order_date,
                'category_id' => $request->category_id,
            ]);

            // 2️⃣ Delete old product details
            $purPo->details()->delete();

            // 3️⃣ Delete old vouchers and voucher details
            $voucherIds = $purPo->voucherDetails()->pluck('voucher_id')->unique();
            if ($voucherIds->isNotEmpty()) {
                PurFGPOVoucherDetails::whereIn('voucher_id', $voucherIds)->delete();
                PaymentVoucher::whereIn('id', $voucherIds)->delete();
            }

            // 4️⃣ Add new product details
            foreach ($request->item_order as $detail) {
                $purPo->details()->create([
                    'product_id' => $detail['product_id'],
                    'variation_id' => $detail['variation_id'],
                    'sku' => $detail['sku'],
                    'qty' => $detail['qty'],
                ]);
            }

            // 5️⃣ Create new Payment Voucher
            $voucher = PaymentVoucher::create([
                'ac_dr_sid' => $request->vendor_id,
                'ac_cr_sid' => 4, // your credit account
                'payment_mode' => 'credit',
                'reference_no' => 'Job PO#' . $id,
                'amount' => $request->voucher_amount,
                'date' => $request->order_date,
                'remarks' => 'Transaction Against FGPO',
            ]);

            // 6️⃣ Create new voucher details
            foreach ($request->voucher_details as $detail) {
                $purPo->voucherDetails()->create([
                    'voucher_id' => $voucher->id,
                    'po_id' => ($detail['po_id'] ?? null) == 0 ? null : $detail['po_id'],
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'rate' => $detail['item_rate'],
                    'width' => $detail['width'] ?? null,
                    'description' => $detail['description'] ?? null,
                ]);
            }

            // 7️⃣ Handle attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filePath = $file->store('attachments/fgpo_'.$purPo->id, 'public');
                    $purPo->attachments()->create(['att_path' => $filePath]);
                }
            }

            DB::commit();
            \Log::info('FGPO Updated Successfully', ['fgpo_id' => $purPo->id]);

            return redirect()->route('pur-fgpos.index')->with('success', 'FGPO updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('FGPO Update Failed', [
                'fgpo_id' => $purPo->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return redirect()->route('pur-fgpos.index')->with('error', 'FGPO update failed: '.$e->getMessage());
        }
    }

    public function store(Request $request)
    {
        \Log::info('Starting FGPO Store process', $request->all());
    
        // Validate the incoming data
        $request->validate([
            'vendor_id' => 'required|exists:chart_of_accounts,id',
            'category_id' => 'required|exists:product_categories,id',
            'order_date' => 'required|date',
            'item_name' => 'required|exists:products,id',
            'item_order' => 'required|array',
            'item_order.*.product_id' => 'required|exists:products,id',
            'item_order.*.variation_id' => 'required|exists:product_variations,id',
            'item_order.*.sku' => 'required|string',
            'item_order.*.qty' => 'required|integer',
            'voucher_amount' => 'required|numeric',
            'voucher_details' => 'required|array',
            'voucher_details.*.product_id' => 'required|exists:products,id',
            'voucher_details.*.qty' => 'required|numeric',
            'voucher_details.*.item_rate' => 'required|numeric',
        ]);
    
        DB::beginTransaction();  // Start the transaction
    
        try {
            // Step 1: Create FGPO record
            \Log::info('Creating FGPO record');
            $fgpo = PurFGPO::create([
                'doc_code' => 'JobPO',
                'vendor_id' => $request->vendor_id,
                'order_date' => $request->order_date,
                'category_id' => $request->category_id,
            ]);

            \Log::info('FGPO Created', ['fgpo_id' => $fgpo->id]);
    
            // Step 2: Add Product Variations
            \Log::info('Adding FGPO Product Variations');
            foreach ($request->item_order as $detail) {
                \Log::info('Processing Item Order:', $detail);
                PurFGPODetails::create([
                    'fgpo_id' => $fgpo->id,
                    'product_id' => $detail['product_id'],
                    'variation_id' => $detail['variation_id'],
                    'sku' => $detail['sku'],
                    'qty' => $detail['qty'],
                ]);
            }
            \Log::info('FGPO Product Variations Added');
    
            // Step 3: Create Journal Voucher
            \Log::info('Creating Journal Voucher');
            $voucher = PaymentVoucher::create([
                'ac_dr_sid' => $request->vendor_id,
                'ac_cr_sid' => '4',
                'amount' => $request->voucher_amount,
                'payment_mode' => 'credit',
                'reference_no' => 'Job PO#' . $fgpo->id,
                'date' => $request->order_date,
                'remarks' => 'Transaction Against Job PO',
                // 'ref_doc_id' => $fgpo->id,
                // 'ref_doc_code' => 'FGPO',
            ]);
            \Log::info('Journal Voucher Created', ['voucher_id' => $voucher->id]);
    
            // Step 4: Add Voucher Details
            \Log::info('Adding Voucher Details');
            foreach ($request->voucher_details as $detail) {
                \Log::info('Processing Voucher Detail:', $detail);
                PurFGPOVoucherDetails::create([
                    'fgpo_id' => $fgpo->id,
                    'voucher_id' => $voucher->id,
                    'po_id' => ($detail['po_id'] ?? null) == 0 ? null : $detail['po_id'],
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'rate' => $detail['item_rate'],
                    'width' => $detail['width'],
                    'description' => $detail['description'],
                ]);
            }
            \Log::info('Voucher Details Added');
    
            // Step 5: Attach files if any
            if ($request->hasFile('attachments')) {
                \Log::info('Processing Attachments');
                foreach ($request->file('attachments') as $file) {
                    $filePath = $file->store('attachments/fgpo_'.$fgpo->id, 'public');
                    PurFGPOAttachements::create([
                        'fgpo_id' => $fgpo->id,
                        'att_path' => $filePath,
                    ]);
                    \Log::info('Attachment Stored', ['path' => $filePath]);
                }
            }
    
            // Commit the transaction if everything is successful
            DB::commit();
            \Log::info('Transaction Committed Successfully');
    
            // Redirect with success message
            return redirect()->route('pur-fgpos.index')->with('success', 'Purchase Order created successfully!');
    
        } catch (\Exception $e) {
            // Rollback in case of any failure
            DB::rollBack();
            \Log::error('Error creating Purchase Order', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    
            // Return with error message
            return redirect()->route('pur-fgpos.index')->with('error', 'Error creating Purchase Order: '.$e->getMessage());
        }
    }
    
    public function newChallan()
    {
        $purpos = PurFGPO::get('id');
        $coa = ChartOfAccounts::all();  // Get all product categories
        $products = Products::all();  // Get all product categories
        $attributes = ProductAttributes::with('values')->get();

        return view('purchasing.fg-po.new-challan', compact('coa', 'products', 'attributes', 'purpos'));
    }

    public function receiving($id)
    {
        $purpo = PurFGPO::with('details.product', 'details.variation')->findOrFail($id);

        // Fetch total received quantity grouped by variation_id for this FGPO
        $receivedQuantities = PurFGPORecDetails::whereHas('receiving', function ($query) use ($id) {
            $query->where('fgpo_id', $id);
        })->selectRaw('variation_id, SUM(qty) as total_received')
            ->groupBy('variation_id')
            ->pluck('total_received', 'variation_id');

        // Loop through order details and assign the received quantity
        foreach ($purpo->details as $detail) {
            // Get received quantity for this variation_id
            $totalReceived = $receivedQuantities[$detail->variation_id] ?? 0;

            // Assign calculated values to be used in the Blade file
            $detail->total_received = $totalReceived;
        }

        return view('purchasing.fg-po.receiving', compact('purpo'));
    }

    public function storeReceiving(Request $request)
    {
        $request->validate([
            'fgpo_id' => 'required|exists:pur_fgpos,id',
            'rec_date' => 'required|date',
            'received_qty' => 'required|array',
            'received_qty.*' => 'nullable|integer|min:1',
        ]);

        // Create a new receiving record
        $receiving = PurFGPORec::create([
            'fgpo_id' => $request->fgpo_id,
            'rec_date' => $request->rec_date,
        ]);

        // Loop through received items and store details
        foreach ($request->received_qty as $fgpoDetailId => $receivedQty) {
            if ($receivedQty > 0) {
                // Fetch the ordered product details
                $orderDetail = PurFGPODetails::find($fgpoDetailId);

                if ($orderDetail) {
                    PurFGPORecDetails::create([
                        'pur_fgpos_rec_id' => $receiving->id,
                        'product_id' => $orderDetail->product_id,
                        'variation_id' => $orderDetail->variation_id,
                        'sku' => $orderDetail->sku ?? '',
                        'qty' => $receivedQty,
                    ]);
                }
            }
        }

        return redirect()->route('pur-fgpos.index')->with('success', 'Receiving recorded successfully.');
    }

    public function getDetails(Request $request)
    {
        try {
            $poIds = array_map('intval', (array) $request->input('po_ids'));

            if (empty($poIds)) {
                return response()->json(['success' => false, 'message' => 'No PO selected']);
            }

            // 🚀 Fetch FG (Finished Goods) Product Details
            $orderedProducts = PurFGPODetails::whereIn('fgpo_id', $poIds)
                ->with('product:id,name')
                ->select('fgpo_id', 'product_id', DB::raw('SUM(qty) as total_qty'))
                ->groupBy('fgpo_id', 'product_id')
                ->get();

            if ($orderedProducts->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No data found in PurFGPODetails']);
            }

            // 🚀 Fetch Fabric Details for each Purchase Order (PO)
            $fabricDetails = PurFGPOVoucherDetails::select(
                'fgpo_id',
                'product_id',
                'rate as fabric_rate',
                DB::raw('SUM(qty) as total_fabric_qty'),
                DB::raw('SUM(qty * rate) as total_fabric_amount'),
                'products.name as fabric_name',
                'products.measurement_unit as unit',
            )
                ->leftJoin('products', 'pur_fgpos_voucher_details.product_id', '=', 'products.id')
                ->whereIn('fgpo_id', $poIds)
                ->groupBy('fgpo_id', 'product_id', 'products.name', 'rate', 'unit' )
                ->get()
                ->groupBy('fgpo_id');

            // 🚀 Fetch Received Quantity (if applicable)
            $receivedQty = DB::table('pur_fgpos_rec_details')
                ->select('product_id', DB::raw('SUM(qty) as total_received_qty'))
                ->whereIn('pur_fgpos_rec_id', function ($query) use ($poIds) {
                    $query->select('id')
                        ->from('pur_fgpos_rec')
                        ->whereIn('fgpo_id', $poIds);
                })
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id'); // ✅ Quick lookup by product_id

            // 🚀 Group and Format Data by PO ID
            $summary = $orderedProducts->groupBy('fgpo_id')->map(function ($products, $fgpo_id) use ($fabricDetails, $receivedQty) {
                return [
                    'fgpo_id' => $fgpo_id,
                    'fabrics' => $fabricDetails->get($fgpo_id, collect())->map(function ($fabric) {
                        return [
                            'fabric_name' => $fabric->fabric_name ?? 'N/A',
                            'fabric_unit' => $fabric->unit ?? 'N/A',
                            'fabric_qty' => $fabric->total_fabric_qty ?? 0,
                            'fabric_rate' => $fabric->fabric_rate ?? 0,
                            'fabric_amount' => $fabric->total_fabric_amount ?? 0,
                        ];
                    })->values(),
                    'products' => $products->map(function ($product) use ($receivedQty) {
                        return [
                            'product_id'   => $product->product_id, // ✅ include product_id
                            'product_name' => optional($product->product)->name ?? 'N/A',
                            'ordered_qty' => $product->total_qty,
                            'received_qty' => $receivedQty->get($product->product_id)->total_received_qty ?? 0,
                        ];
                    })->values(),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'summary' => $summary,
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Error fetching PO details:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving PO details.',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
        }
    }

    public function print($id)
    {
        // Fetch the purchase order with related data
        $purpos = PurFGPO::with(['vendor', 'details.product', 'details.product.attachments' , 'details.variation.attribute_values' , 'voucherDetails.purPO'])->findOrFail($id);

        $voucherIds = $purpos->voucherDetails->pluck('voucher_id')->unique()->implode(', ');

        // Get the first non-null po_code from the related purPo

        if (! $purpos) {
            abort(404, 'Purchase Order not found.');
        }

        $pdf = new MyPDF;

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TGM');
        $pdf->SetTitle('Job PO-' . $purpos->id);
        $pdf->SetSubject('Job PO-' . $purpos->id);
        $pdf->SetKeywords('Job PO, TCPDF, PDF');

        $pdf->AddPage();
        $pdf->setCellPadding(1.2);

        // Logo
        $logoPath = public_path('assets/img/TGM-Logo.jpg');
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 10, 10, 30);
        }

        // Company Info next to logo
        $pdf->SetXY(45, 12);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(100, 5,
            "The Great Master\nTariq Road Karachi.\nTel #",
            0, 'L', false, 1, '', '', true, 0, false, true, 0, 'T'
        );

        // PO Details just below PO box
        $pdf->SetXY(150, 12);
        $invoiceInfo = '
        <table cellpadding="2" style="font-size:10px;">
            <tr><td><b>PO #</b></td><td>' . $purpos->doc_code.'-'.$purpos->id .'</td></tr>
            <tr><td><b>Date</b></td><td>' . \Carbon\Carbon::parse($purpos->order_date)->format('d/m/Y') . '</td></tr>
            <tr><td><b>Unit </b></td><td>' . $purpos->vendor->name . '</td></tr>
        </table>';
        $pdf->writeHTML($invoiceInfo, false, false, false, false, '');
                
        // Horizontal Line (left to PO box only)
        $pdf->Line(10, 45, 150, 45); // Line ends just before the blue box

        // Blue PO box (same position)
        $pdf->SetXY(150, 41);
        $pdf->SetFillColor(23, 54, 93); // Blue
        $pdf->SetTextColor(255, 255, 255); // White
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 8, 'Job PO', 0, 1, 'C', 1);
        $pdf->SetTextColor(0, 0, 0); // Reset to black

        $pdf->SetXY(10, 55);

        // Items Table Header
        $html = '<table border="0.3" style="text-align:center;margin-top:5px">
            <tr>
                <th width="5%" style="font-size:10px;font-weight:bold;color:#17365D">S/N</th>
                <th width="40%" style="font-size:10px;font-weight:bold;color:#17365D">Product</th>
                <th width="40%" style="font-size:10px;font-weight:bold;color:#17365D">Variation</th>
                <th width="15%" style="font-size:10px;font-weight:bold;color:#17365D">Qty</th>
            </tr>
       ';

        // Items Table Data
        $total_amount = 0;
        $html = '<table border="1" cellpadding="3" cellspacing="0" width="100%">
        <thead>
            <tr style="font-size:10px;text-align:center;">
                <th width="5%">#</th>
                <th width="40%">Product</th>
                <th width="40%">Variation</th>
                <th width="15%">Qty</th>
            </tr>
        </thead>
        <tbody>';

        $count = 1;
        $groupedItems = [];

        foreach ($purpos->details as $item) {
            $groupedItems[$item->product_id][] = $item;
        }

        foreach ($groupedItems as $product_id => $items) {
            $total_qty = 0;

            $product_name = $items[0]->product->name ?? 'N/A';
            $product_m_unit = $items[0]->product->measurement_unit ?? 'N/A';

            foreach ($items as $item) {
                $variation_name = 'N/A';
                if ($item->variation && $item->variation->attribute_values) {
                    $variation_name = $item->variation->attribute_values->value ?? 'N/A';
                }

                $html .= '<tr style="font-size:10px;text-align:center;">
                    <td width="5%">'.$count.'</td>
                    <td width="40%">'.$product_name.'</td>
                    <td width="40%">'.$variation_name.'</td>
                    <td width="15%">'.$item->qty.' '.$product_m_unit.'</td>
                </tr>';

                $total_qty += $item->qty;
                $count++;
            }

            // Total row for product
            $html .= '<tr style="font-size:10px;text-align:right;">
                <td colspan="3"><strong>'.$product_name.' Total Pcs :</strong></td>
                <td style="text-align:center;"><strong>'.$total_qty.' '.$product_m_unit.'</strong></td>
            </tr>';
        }

        $html .= '</tbody></table>';

        // Output HTML to PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        $challanTable = '
        <table border="1" cellpadding="5" cellspacing="0" style="font-size:10px;">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th width="28%"><strong>PO No./Fabric ID</strong></th>
                    <th width="30%"><strong>Description</strong></th>
                    <th width="8%"><strong>Width</strong></th>
                    <th width="10%"><strong>Qty</strong></th>
                    <th width="10%"><strong>Rate</strong></th>
                    <th width="13%"><strong>Total</strong></th>
                </tr>
            </thead>
            <tbody>';

        $totalAmount = 0;

        foreach ($purpos->voucherDetails as $item) {
            $poCode = $item->purPO->po_code ?? 'N/A';
            $fabID = $item->product->id ?? 'N/A';
            $fabName = $item->product->name ?? 'N/A';
            $description = $item->description ?? '';
            $width = $item->width ?? 0;
            $qty = $item->qty ?? 0;
            $unit = $item->product->measurement_unit ?? '';
            $rate = number_format($item->rate ?? 0, 2);
            $total = number_format(($item->qty ?? 0) * ($item->rate ?? 0), 2);

            $totalAmount += ($item->qty ?? 0) * ($item->rate ?? 0);

            $challanTable .= '
            <tr>
                <td width="28%">' . $poCode . '/' . $fabName . '</td>
                <td width="30%">' . $description . '</td>
                <td width="8%">' . $width . '"</td>
                <td width="10%">' . $qty . ' ' . $unit . '</td>
                <td width="10%">' . $rate . '</td>
                <td width="13%">' . $total . '</td>
            </tr>';
        }

        $challanTable .= '</tbody></table>';
        $pdf->writeHTML($challanTable, true, false, true, false, '');
        
        $pdf->writeHTML('<h3 style="text-align:right;"><strong>Total Amount: </strong>'.number_format($totalAmount, 2).' PKR</h3>', true, false, true, false, '');

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Attachments:', 0, 1, 'L');
        
        $shownProductIds = [];
        
        $imageWidth = 65;
        $imageHeight = 85;
        $textHeight = 10;
        $gap = 10;
        
        $leftMargin = $pdf->getMargins()['left'];
        $rightMargin = $pdf->getMargins()['right'];
        $pageWidth = $pdf->getPageWidth();
        $pageHeight = $pdf->getPageHeight();
        $bottomMargin = $pdf->getBreakMargin();
        
        $maxX = $pageWidth - $rightMargin;
        $startX = $leftMargin;
        $currentX = $startX;
        $currentY = $pdf->GetY();
        
        foreach ($purpos->details as $item) {
            $product = $item->product;
            if (!$product || in_array($product->id, $shownProductIds)) {
                continue;
            }
        
            $shownProductIds[] = $product->id;
        
            if ($product->attachments->isNotEmpty()) {
                $attachment = $product->attachments->first();
                $imagePath = storage_path('app/public/' . $attachment->image_path);
        
                if (file_exists($imagePath)) {
        
                    // Wrap to next row if image exceeds page width
                    if ($currentX + $imageWidth > $maxX) {
                        $currentX = $startX;
                        $currentY += $imageHeight + $textHeight + $gap;
                    }
        
                    // Check vertical space and add page if needed
                    $availableHeight = $pageHeight - $currentY - $bottomMargin;
                    if ($availableHeight < $imageHeight + $textHeight + $gap) {
                        $pdf->AddPage();
                        $currentY = $pdf->GetY();
                        $currentX = $startX;
                    }
        
                    // Draw image
                    $pdf->Image($imagePath, $currentX, $currentY, $imageWidth, $imageHeight);
        
                    // Draw product name below image
                    $pdf->SetXY($currentX, $currentY + $imageHeight + 2);
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->MultiCell($imageWidth, 5, $product->name, 0, 'C');
        
                    // Move to next column
                    $currentX += $imageWidth + $gap;
                }
            }
        }

        $pdf->SetY(-40); // Adjust value if needed to position correctly

        $lineWidth = 60; // Line width in mm
        $yPosition = $pdf->GetY(); // Get current Y position for alignment

        // Draw lines for signatures
        $pdf->Line(28, $yPosition, 20 + $lineWidth, $yPosition); // Approved By
        $pdf->Line(130, $yPosition, 120 + $lineWidth, $yPosition); // Received By

        $pdf->Ln(5); // Move cursor below the line

        // Text below the lines
        $pdf->SetXY(23, $yPosition);
        $pdf->Cell($lineWidth, 10, 'Approved By', 0, 0, 'C');

        $pdf->SetXY(125, $yPosition);
        $pdf->Cell($lineWidth, 10, 'Received By', 0, 0, 'C');


        $pdf->AddPage(); // Start a new page for Challan

        // Logo
        $logoPath = public_path('assets/img/TGM-Logo.jpg');
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 10, 10, 30);
        }

        // Company Info next to logo
        $pdf->SetXY(45, 12);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(100, 5,
            "The Great Master\nTariq Road Karachi.\nTel #",
            0, 'L', false, 1, '', '', true, 0, false, true, 0, 'T'
        );

        // PO Details just below PO box
        $pdf->SetXY(150, 12);
        $invoiceInfo = '
        <table cellpadding="2" style="font-size:10px;">
            <tr><td><b>PO #</b></td><td>' . $purpos->doc_code.'-'.$purpos->id .'</td></tr>
            <tr><td><b>Date</b></td><td>' . \Carbon\Carbon::parse($purpos->order_date)->format('d/m/Y') . '</td></tr>
            <tr><td><b>Unit </b></td><td>' . $purpos->vendor->name . '</td></tr>
        </table>';
        $pdf->writeHTML($invoiceInfo, false, false, false, false, '');
                
        // Horizontal Line (left to PO box only)
        $pdf->Line(10, 45, 150, 45); // Line ends just before the blue box

        // Blue PO box (same position)
        $pdf->SetXY(150, 41);
        $pdf->SetFillColor(23, 54, 93); // Blue
        $pdf->SetTextColor(255, 255, 255); // White
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 8, 'Fabric Challan', 0, 1, 'C', 1);
        $pdf->SetTextColor(0, 0, 0); // Reset to black

        $pdf->SetXY(10, 55);

        $pdf->SetFont('helvetica', '', 10);

        // Top Info Row (Vendor, PO No, Date)
        $challanTable = '
        <table border="1" cellpadding="5" cellspacing="0" style="font-size:10px;">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th width="30%"><strong>PO No./Fabric ID</strong></th>
                    <th width="28%"><strong>Description</strong></th>
                    <th width="8%"><strong>Width</strong></th>
                    <th width="10%"><strong>Qty</strong></th>
                    <th width="10%"><strong>Rate</strong></th>
                    <th width="13%"><strong>Total</strong></th>
                </tr>
            </thead>
            <tbody>';

        $totalAmount = 0;

        foreach ($purpos->voucherDetails as $item) {
            $poCode = $item->purPO->po_code ?? 'N/A';
            $fabricID = $item->product->id ?? 'N/A';
            $fabName = $item->product->name ?? 'N/A';

            $description = $item->description ?? '';
            $width = $item->width ?? 0;
            $qty = $item->qty ?? 0;
            $unit = $item->product->measurement_unit ?? '';
            $rate = number_format($item->rate ?? 0, 2);
            $total = number_format(($item->qty ?? 0) * ($item->rate ?? 0), 2);

            $totalAmount += ($item->qty ?? 0) * ($item->rate ?? 0);

            $challanTable .= '
            <tr>
                <td width="30%">' . $poCode . '/' . $fabName . '</td>
                <td width="28%">' . $description . '</td>
                <td width="8%">' . $width . '"</td>
                <td width="10%">' . $qty . ' ' . $unit . '</td>
                <td width="10%">' . $rate . '</td>
                <td width="13%">' . $total . '</td>
            </tr>';
        }

        $challanTable .= '</tbody></table>';
        $pdf->writeHTML($challanTable, true, false, true, false, '');
    

        // Total Amount
        $pdf->writeHTML('<h3 style="text-align:right;"><strong>Total Amount: </strong>'.number_format($totalAmount, 2).' PKR</h3>', true, false, true, false, '');

        // Move to the bottom of the page
        $pdf->SetY(-50); // Adjust value if needed to position correctly

        $lineWidth = 60; // Line width in mm
        $yPosition = $pdf->GetY(); // Get current Y position for alignment

        // Draw lines for signatures
        $pdf->Line(28, $yPosition, 20 + $lineWidth, $yPosition); // Approved By
        $pdf->Line(130, $yPosition, 120 + $lineWidth, $yPosition); // Received By

        $pdf->Ln(5); // Move cursor below the line

        // Text below the lines
        $pdf->SetXY(23, $yPosition);
        $pdf->Cell($lineWidth, 10, 'Approved By', 0, 0, 'C');

        $pdf->SetXY(125, $yPosition);
        $pdf->Cell($lineWidth, 10, 'Received By', 0, 0, 'C');

        // Output the PDF
        $pdf->Output('Job-PO-'.$purpos->id.'.pdf', 'I');
    }
}

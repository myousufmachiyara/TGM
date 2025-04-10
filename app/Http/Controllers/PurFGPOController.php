<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccounts;
use App\Models\JournalVoucher1;
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

class PurFGPOController extends Controller
{
    public function index()
    {
        $purpos = PurFGPO::with(['vendor', 'details.product', 'details.variation.attribute_values'])->get();

        return view('purchasing.fg-po.index', compact('purpos'));
    }

    public function create()
    {
        $coa = ChartOfAccounts::all();  // Get all product categories
        $fabrics = Products::where('item_type', 'raw')->get();  // Get all product categories
        $articles = Products::where('item_type', 'fg')->get();  // Get all product categories
        $attributes = ProductAttributes::with('values')->get();
        $prodCat = ProductCategory::all();  // Get all product categories

        return view('purchasing.fg-po.create', compact('coa', 'fabrics', 'articles', 'attributes', 'prodCat'));
    }

    public function store(Request $request)
    {
        \Log::info('Starting FGPO Store process', $request->all());
    
        // Validate the incoming data
        $request->validate([
            'vendor_id' => 'required|exists:chart_of_accounts,id',
            'category_id' => 'required|exists:product_categories,id',
            'order_date' => 'required|date',
            'product_id' => 'required|exists:products,id',
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
    
        DB::beginTransaction();  // Start the transaction
    
        try {
            // Step 1: Create FGPO record
            \Log::info('Creating FGPO record');
            $fgpo = PurFGPO::create([
                'doc_code' => 'FGPO',
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
            $voucher = JournalVoucher1::create([
                'debit_acc_id' => $request->vendor_id,
                'credit_acc_id' => '4',
                'amount' => $request->voucher_amount,
                'date' => $request->order_date,
                'narration' => 'Transaction Against FGPO',
                'ref_doc_id' => $fgpo->id,
                'ref_doc_code' => 'FGPO',
            ]);
            \Log::info('Journal Voucher Created', ['voucher_id' => $voucher->id]);
    
            // Step 4: Add Voucher Details
            \Log::info('Adding Voucher Details');
            foreach ($request->voucher_details as $detail) {
                \Log::info('Processing Voucher Detail:', $detail);
                PurFGPOVoucherDetails::create([
                    'fgpo_id' => $fgpo->id,
                    'voucher_id' => $voucher->id,
                    'po_id' => $detail['po_id'],
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'rate' => $detail['item_rate'],
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

    // public function receiving($id) {
    //     $purpo = PurFGPO::with(['details', 'details.product'])->findOrFail($id);
    //     return view('purchasing.fg-po.receiving', compact('purpo'));
    // }

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
                'products.name as fabric_name'
            )
                ->leftJoin('products', 'pur_fgpos_voucher_details.product_id', '=', 'products.id')
                ->whereIn('fgpo_id', $poIds)
                ->groupBy('fgpo_id', 'product_id', 'products.name', 'rate')
                ->get()
                ->groupBy('fgpo_id'); // ✅ Grouping by PO ID to return multiple fabrics per PO

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
                            'fabric_qty' => $fabric->total_fabric_qty ?? 0,
                            'fabric_rate' => $fabric->fabric_rate ?? 0,
                            'fabric_amount' => $fabric->total_fabric_amount ?? 0,
                        ];
                    })->values(),
                    'products' => $products->map(function ($product) use ($receivedQty) {
                        return [
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
        $purpos = PurFGPO::with(['vendor', 'details.product', 'details.product.attachments' , 'details.variation.attribute_values'])->findOrFail($id);

        if (! $purpos) {
            abort(404, 'Purchase Order not found.');
        }

        $pdf = new MyPDF;

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TGM');
        $pdf->SetTitle('Purchase Order - '.$purpos->id);
        $pdf->SetSubject('Purchase Order - '.$purpos->id);
        $pdf->SetKeywords('Purchase Order, TCPDF, PDF');

        // Add a page
        $pdf->AddPage();
        $pdf->setCellPadding(1.2);

        // Purchase Order Heading
        $heading = '<h1 style="font-size:20px;text-align:center;font-style:italic;text-decoration:underline;color:#17365D">Purchase Order</h1>';
        $pdf->writeHTML($heading, true, false, true, false, '');

        // Purchase Order Details Table
        $html = '<table style="margin-bottom:10px">
            <tr>
                <td style="font-size:10px;font-weight:bold;color:#17365D">PO No: <span style="text-decoration: underline;color:#000">'.$purpos->id.'</span></td>
                <td style="font-size:10px;font-weight:bold;color:#17365D">Date: <span style="color:#000">'.\Carbon\Carbon::parse($purpos->order_date)->format('d-m-Y').'</span></td>
                <td style="font-size:10px;font-weight:bold;color:#17365D">Quotation No: <span style="text-decoration: underline;color:#000">N/A</span></td>
            </tr>
        </table>';

        // Vendor and Account Details
        $html .= '<table border="0.1" style="border-collapse: collapse;">
            <tr>
                <td width="20%" style="font-size:10px;font-weight:bold;color:#17365D">Vendor Name</td>
                <td width="30%" style="font-size:10px;">'.$purpos->vendor->name.'</td>
                <td width="20%" style="font-size:10px;font-weight:bold;color:#17365D">Address</td>
                <td width="30%" style="font-size:10px;">'.$purpos->vendor->address.'</td>
            </tr>
            <tr>
                <td width="20%" style="font-size:10px;font-weight:bold;color:#17365D">Phone</td>
                <td width="30%" style="font-size:10px;">'.$purpos->vendor->phone_no.'</td>
                <td width="20%" style="font-size:10px;font-weight:bold;color:#17365D">Remarks</td>
                <td width="30%" style="font-size:10px;">'.$purpos->remarks.'</td>
            </tr>
        </table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // Items Table Header
        $html = '<table border="0.3" style="text-align:center;margin-top:10px">
            <tr>
                <th width="10%" style="font-size:10px;font-weight:bold;color:#17365D">S/N</th>
                <th width="40%" style="font-size:10px;font-weight:bold;color:#17365D">Product</th>
                <th width="40%" style="font-size:10px;font-weight:bold;color:#17365D">Variation</th>
                <th width="10%" style="font-size:10px;font-weight:bold;color:#17365D">Qty</th>
            </tr>
       ';

        // Items Table Data
        $total_amount = 0;
        $count = 1;

        foreach ($purpos->details as $item) {
            $product_name = $item->product->name ?? 'N/A';
            $variation_name = 'N/A';
            if ($item->variation && $item->variation->attribute_values) {
                $variation_name = $item->variation->attribute_values->value ?? 'N/A';
            }
            $product_m_unit = $item->product->measurement_unit ?? 'N/A'; // Assuming 'measurement_unit' is the column name

            $html .= '<tr>
                <td width="10%" style="font-size:10px;text-align:center;">'.$count.'</td>
                <td width="40%" style="font-size:10px;text-align:center;">'.$product_name.'</td>
                <td width="40%" style="font-size:10px;text-align:center;">'.$variation_name.'</td>
                <td width="10%" style="font-size:10px;text-align:center;">'.$item->qty.' '.$product_m_unit.'</td>
            </tr>';
            $count++;
        }

        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // Attachments (Images)
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Attachments:', 0, 1, 'L');

        $shownProductIds = []; // Track which products' images are already shown

    
        foreach ($purpos->details as $item) {
            $product = $item->product;
        
            // Skip if we've already shown this product's image
            if (!$product || in_array($product->id, $shownProductIds)) {
                continue;
            }
        
            // Mark this product as shown
            $shownProductIds[] = $product->id;
        
            // Show only the first image of this product (optional: you can loop if you want multiple)
            if ($product->attachments->isNotEmpty()) {
                $attachment = $product->attachments->first();
                $imagePath = storage_path('app/public/' . $attachment->image_path);
        
                if (file_exists($imagePath)) {
                    $pdf->Image($imagePath, '', '', 40, 60, '', '', '', false, 300, '', false, false, 0, false, false, false);
                    $pdf->Ln(55); // spacing after image
                }
            }
        }

        $pdf->AddPage(); // Start a new page for Challan

        $pdf->SetFont('helvetica', '', 10);

        // Header
        $pdf->writeHTML('<h3 style="text-align:center; color:#000;">Payment Voucher</h3><hr>', true, false, true, false, '');

        // Top Info Row (Vendor, PO No, Date)
        $challanInfo = '
        <table cellspacing="0" cellpadding="5" style="font-size:10px;">
            <tr>
                <td width="33%"><strong>Vendor:</strong> '.$purpos->vendor->name.'</td>
                <td width="33%"><strong>PO No:</strong> FGPO-'.$purpos->id.'</td>
                <td width="33%"><strong>Date:</strong> '.\Carbon\Carbon::parse($purpos->order_date)->format('d-m-Y').'</td>
            </tr>
        </table>';
        $pdf->writeHTML($challanInfo, true, false, true, false, '');

        // Items Table
        $challanTable = '
        <table border="1" cellpadding="5" cellspacing="0" style="font-size:10px;">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th><strong>Fabric Name</strong></th>
                    <th><strong>Description</strong></th>
                    <th><strong>Quantity</strong></th>
                    <th><strong>Rate</strong></th>
                    <th><strong>Total</strong></th>
                </tr>
            </thead>
            <tbody>';

        $totalAmount = 0;

        foreach ($purpos->details as $item) {
            $fabricName = $item->product->name ?? 'N/A';
            $description = $item->description ?? '';
            $qty = $item->qty ?? 0;
            $unit = $item->product->measurement_unit ?? '';
            $rate = number_format($item->rate ?? 0, 2);
            $total = number_format(($item->qty ?? 0) * ($item->rate ?? 0), 2);

            $totalAmount += ($item->qty ?? 0) * ($item->rate ?? 0);

            $challanTable .= "
                <tr>
                    <td>{$fabricName}</td>
                    <td>{$description}</td>
                    <td>{$qty} {$unit}</td>
                    <td>{$rate}</td>
                    <td>{$total}</td>
                </tr>";
        }

        $challanTable .= '</tbody></table>';
        $pdf->writeHTML($challanTable, true, false, true, false, '');

        // Total Amount
        $pdf->writeHTML('<h4 style="text-align:right;"><strong>Total Amount: </strong>'.number_format($totalAmount, 2).' PKR</h4>', true, false, true, false, '');

        // Signature Section
        $signatures = '
        <table cellspacing="0" cellpadding="10" style="margin-top:30px;">
            <tr>
                <td width="50%">
                    <strong>Authorized By:</strong><br><br>
                    ________________________
                </td>
            </tr>
        </table>';
        $pdf->writeHTML($signatures, true, false, true, false, '');

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
        $pdf->Output('Purchase_Order_'.$purpos->id.'.pdf', 'I');
    }
}

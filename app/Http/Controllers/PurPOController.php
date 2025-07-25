<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccounts;
use App\Models\ProductCategory;
use App\Models\Products;
use App\Models\PurPO;
use App\Models\PurPoAttachment;
use App\Models\PurPosDetail;
use App\Models\PurPORec;
use App\Models\PurPORecDetails;
use App\Services\myPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PurPOController extends Controller
{
    public function index()
    {
        $purpos = PurPO::with(['vendor', 'details.product'])->get();
        return view('purchasing.po.index', compact('purpos'));
    }

    public function create()
    {
        $prodCat = ProductCategory::all();  // Get all product categories
        $vendors = ChartOfAccounts::where('account_type', 'vendor')->get();
        $products = Products::get();

        return view('purchasing.po.create', compact('prodCat', 'vendors', 'products'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();  // Begin the transaction
    
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'vendor_id' => 'required|exists:chart_of_accounts,id', // Ensure vendor exists
                'category_id' => 'required|exists:product_categories,id',
                'order_date' => 'required|date',
                'order_by' => 'required|string|max:255',
                'remarks' => 'nullable|string|max:255',
                'details' => 'required|array',
                'details.*.item_id' => 'required|exists:products,id',
                'details.*.width' => 'required|numeric|min:0',
                'details.*.description' => 'nullable|string|max:255',
                'details.*.item_rate' => 'required|numeric|min:0',
                'details.*.item_qty' => 'required|numeric|min:0',
                'att.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Image validation
            ]);
    
            // Generate PO code in the format: PO-SequenceNo-CategoryCode
            $latestPo = PurPo::latest()->first();
            $sequenceNo = $latestPo ? $latestPo->id + 1 : 1;
            
            // Pad the number with leading zeros to make it 5 digits
            $sequencePadded = str_pad($sequenceNo, 5, '0', STR_PAD_LEFT);
            
            $poCode = "PO-{$sequencePadded}";
    
            // Create the Purchase Order
            $purpo = PurPo::create([
                'vendor_id' => $validatedData['vendor_id'],
                'category_id' => $validatedData['category_id'],
                'po_code' => $poCode,  // Use the generated PO code
                'order_date' => $validatedData['order_date'],
                'order_by' => $validatedData['order_by'],
                'remarks' => $validatedData['remarks'],
                'created_by' => Auth::id()
            ]);
    
            // Store Purchase Order Details
            foreach ($validatedData['details'] as $detail) {
                PurPosDetail::create([
                    'pur_pos_id' => $purpo->id, // Foreign key
                    'item_id' => $detail['item_id'],
                    'width' => $detail['width'],
                    'description' => $detail['description'],
                    'item_rate' => $detail['item_rate'],
                    'item_qty' => $detail['item_qty'],
                ]);
            }
    
            // Handle Images (if provided)
            if ($request->hasFile('att')) {
                foreach ($request->file('att') as $file) {
                    $filePath = $file->store('purchase_orders', 'public'); // Store in storage/app/public/purchase_orders
    
                    PurPoAttachment::create([
                        'pur_po_id' => $purpo->id,
                        'att_path' => $filePath,
                    ]);
                }
            }
    
            // Commit the transaction if all operations are successful
            DB::commit();
    
            return redirect()->route('pur-pos.index')->with('success', 'Purchase Order created successfully!');
    
        } catch (\Exception $e) {
            // Rollback the transaction if any error occurs
            DB::rollBack();
    
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    public function show(PurPO $purpo)
    {
        $purpo->load('details'); // Eager load details

        return view('purpos.show', compact('purpo'));
    }

    public function edit($id)
    {
        $purPo = PurPo::with(['details', 'attachments'])->findOrFail($id);

        $categories = ProductCategory::all();         // Product categories
        $vendors = ChartOfAccounts::where('account_type', 'vendor')->get(); // Only vendors
        $products = Products::all();               // List of all products

        return view('purchasing.po.edit', compact('purPo', 'categories', 'vendors', 'products'));
    }

    public function receiving($id)
    {
        // Load PO with product relation
        $purpo = PurPo::with('details.product')->findOrFail($id);

        // Sum of received quantities by product_id
        $receivedQuantities = PurPORecDetails::whereHas('receiving', function ($query) use ($id) {
            $query->where('po_id', $id);
        })->selectRaw('product_id, SUM(qty) as total_received')
        ->groupBy('product_id')
        ->pluck('total_received', 'product_id'); // [product_id => received]

        // Attach received and remaining quantities to each detail
        foreach ($purpo->details as $detail) {
            $productId = $detail->item_id; // This is the product_id

            $detail->product_name = $detail->product->name ?? 'N/A';
            $detail->total_received = $receivedQuantities[$productId] ?? 0;
            $detail->remaining_qty = $detail->item_qty - $detail->total_received;
        }

        return view('purchasing.po.receiving', compact('purpo'));
    }

    public function storeReceiving(Request $request)
    {                                                                                                                                               
        Log::debug('--- storeReceiving() CALLED ---');
        Log::debug('Request Data:', $request->all());

        $request->validate([
            'po_id' => 'required|exists:pur_pos,id',
            'rec_date' => 'required|date',
            'received_qty' => 'required|array',
            'received_qty.*' => 'nullable|numeric|min:0.01',
            'received_rate' => 'required|array',
            'received_rate.*' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Create the receiving record
            $receiving = PurPORec::create([
                'po_id'      => $request->po_id,
                'rec_date'   => $request->rec_date,
                'created_by' => auth()->id() ?? 0,
            ]);

            Log::debug('Created PurPosRec ID: ' . $receiving->id);

            $stored = false;

            foreach ($request->received_qty as $detailId => $qty) {
                Log::debug("Processing detail ID: $detailId with qty: $qty");

                if ($qty > 0) {
                    $orderDetail = DB::table('pur_pos_details')->where('id', $detailId)->first();

                    if (!$orderDetail) {
                        Log::error("❌ PurPosDetail not found for ID: $detailId");
                        continue;
                    }

                    if (!$orderDetail->item_id) {
                        Log::error("❌ item_id is NULL for PurPosDetail ID: $detailId");
                        continue;
                    }

                    $rate = isset($request->received_rate[$detailId]) ? floatval($request->received_rate[$detailId]) : 0;

                    PurPORecDetails::create([
                        'pur_pos_rec_id' => $receiving->id,
                        'product_id'     => $orderDetail->item_id,
                        'sku'            => $orderDetail->sku ?? '',
                        'qty'            => $qty,
                        'rate'           => $rate,
                    ]);

                    Log::info("✅ Received: product_id={$orderDetail->item_id}, qty=$qty, rate=$rate");
                    $stored = true;
                }
            }

            if (!$stored) {
                DB::rollBack();
                Log::warning('❌ No valid items saved. Rolling back.');
                return back()->with('error', 'No items were recorded. Please check your input.');
            }

            DB::commit();
            Log::info('✅ Receiving transaction committed successfully.');

            return redirect()->route('pur-pos.index')->with('success', 'Receiving recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Exception in storeReceiving: ' . $e->getMessage());
            return back()->with('error', 'Receiving failed: ' . $e->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            Log::info("Purchase Order Update Start", ['id' => $id]);

            $validatedData = $request->validate([
                'vendor_id' => 'required|exists:chart_of_accounts,id',
                'category_id' => 'required|exists:product_categories,id',
                'order_date' => 'required|date',
                'order_by' => 'required|string|max:255',
                'remarks' => 'nullable|string|max:255',
                'details' => 'required|array',
                'details.*.item_id' => 'required|exists:products,id',
                'details.*.width' => 'required|numeric|min:0',
                'details.*.description' => 'nullable|string|max:255',
                'details.*.item_rate' => 'required|numeric|min:0',
                'details.*.item_qty' => 'required|numeric|min:0',
                'att.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            Log::info("Validated Data", $validatedData);

            $purPo = PurPo::findOrFail($id);

            $purPo->update([
                'vendor_id'     => $validatedData['vendor_id'],
                'category_id'   => $validatedData['category_id'],
                'order_date'    => $validatedData['order_date'],
                'order_by'      => $validatedData['order_by'],
                'remarks'       => $validatedData['remarks'],
                'updated_by'    => Auth::id(),
            ]);

            Log::info("Purchase Order Updated", ['pur_po_id' => $purPo->id]);

            // Delete old details
            $purPo->details()->delete();

            // Re-create new details
            foreach ($validatedData['details'] as $detail) {
                $purPo->details()->create([
                    'item_id'     => $detail['item_id'],
                    'width'       => $detail['width'],
                    'description' => $detail['description'] ?? '',
                    'item_rate'   => $detail['item_rate'],
                    'item_qty'    => $detail['item_qty'],
                ]);
            }

            Log::info("Purchase Order Details Updated");

            // Handle new attachments (if any)
            if ($request->hasFile('att')) {
                foreach ($request->file('att') as $file) {
                    $filePath = $file->store('purchase_orders', 'public');

                    PurPoAttachment::create([
                        'pur_po_id' => $purPo->id,
                        'att_path'  => $filePath,
                    ]);
                }

                Log::info("Attachments Uploaded", ['files' => count($request->file('att'))]);
            }

            DB::commit();

            Log::info("Purchase Order Update Completed", ['pur_po_id' => $purPo->id]);

            return redirect()->route('pur-pos.index', $purPo->id)->with('success', 'Purchase Order updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Purchase Order Update Failed", [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);

            return back()->withErrors(['error' => 'Update failed: ' . $e->getMessage()]);
        }
    }

    public function destroy(PurPo $purpo)
    {
        $purpo->details()->delete(); // Delete associated details
        $purpo->delete(); // Delete the Purchase Order

        return redirect()->route('purpos.index')->with('success', 'Purchase Order deleted successfully!');
    }

    public function indexAPI()
    {
        $purpos = PurPO::paginate(10); // Add pagination for large datasets

        return PurPOResource::collection($purpos);
    }

    public function getPoCodes(Request $request)
    {
        $productId = $request->product_id;

        $poIds = PurPosDetail::where('item_id', $productId)
                    ->pluck('pur_pos_id')
                    ->unique();

        $poCodes = PurPo::whereIn('id', $poIds)
                    ->pluck('po_code');

        return response()->json([
            'po_ids' => $poIds,
            'po_codes' => $poCodes,
        ]);
    }

    public function getWidth(Request $request)
    {
        $productId = $request->product_id;
        $poId = $request->po_id;

        $width = PurPosDetail::where('item_id', $productId)
            ->where('pur_pos_id', $poId)
            ->value('width');

        return response()->json(['width' => $width]);
    }

    public function showAPI(PurPO $purpo)
    {
        return new PurPOResource($purpo);
    }

    public function print($id)
    {
        $purpos = PurPO::with([
            'vendor', 
            'details.product', 
            'attachments'
        ])->findOrFail($id);

        $pdf = new MyPDF;

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TGM');
        $pdf->SetTitle('Purchase Order Print');
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);

        // Order Header
        $pdf->Cell(0, 10, 'Purchase Order', 0, 1, 'C');
        $pdf->Ln(5);

        // Vendor Info
        $pdf->Cell(0, 10, 'Vendor: ' . optional($purpos->vendor)->name, 0, 1);
        $pdf->Cell(0, 10, 'PO Code: ' . $purpos->po_code, 0, 1);
        $pdf->Cell(0, 10, 'Date: ' . $purpos->order_date->format('d-m-Y'), 0, 1);
        $pdf->Ln(5);

        // Table of Products
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(10, 8, '#', 1);
        $pdf->Cell(60, 8, 'Product', 1);
        $pdf->Cell(20, 8, 'Width', 1);
        $pdf->Cell(20, 8, 'Qty', 1);
        $pdf->Cell(25, 8, 'Rate', 1);
        $pdf->Cell(25, 8, 'Total', 1);
        $pdf->Ln();

        $pdf->SetFont('helvetica', '', 10);
        $count = 1;
        foreach ($purpos->details as $row) {
            $pdf->Cell(10, 8, $count++, 1);
            $pdf->Cell(60, 8, optional($row->product)->name, 1);
            $pdf->Cell(20, 8, $row->width, 1);
            $pdf->Cell(20, 8, $row->item_qty, 1);
            $pdf->Cell(25, 8, number_format($row->item_rate, 2), 1);
            $pdf->Cell(25, 8, number_format($row->item_qty * $row->item_rate, 2), 1);
            $pdf->Ln();
        }

        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Product Images', 0, 1, 'L');
        $pdf->Ln(5);

        // === Image Grid ===
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $rowHeight = 70;
        $imageWidth = 40;
        $margin = 10;
        $maxX = $pdf->getPageWidth() - $pdf->GetMargins()['right'];

        $pdf->SetFont('helvetica', '', 9);
        foreach ($purpos->details as $row) {
            $product = $row->product;
            $attachments = $product ? $product->attachments ?? [] : [];

            foreach ($attachments as $attachment) {
                $imagePath = public_path('storage/' . $attachment->image_path);

                if (file_exists($imagePath)) {
                    $availableHeight = $pdf->getPageHeight() - $pdf->GetY() - $pdf->getBreakMargin();
                    if ($availableHeight < $rowHeight) {
                        $pdf->AddPage();
                        $x = $pdf->GetMargins()['left'];
                        $y = $pdf->GetY();
                    }

                    if ($x + $imageWidth > $maxX) {
                        $x = $pdf->GetMargins()['left'];
                        $y += $rowHeight;
                    }

                    // Show image
                    $pdf->Image($imagePath, $x, $y, $imageWidth, 50, '', '', '', false, 300, '', false, false, 0, false, false, false);

                    // Show product name below
                    $pdf->SetXY($x, $y + 52);
                    $pdf->MultiCell($imageWidth, 10, optional($product)->name ?? 'No name', 0, 'C');

                    $x += $imageWidth + $margin;
                }
            }
        }

        return response()->streamDownload(function () use ($pdf) {
            $pdf->Output('purchase_order.pdf', 'I');
        }, 'purchase_order.pdf');
    }

}

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
        $purpos = PurPo::with(['vendor', 'details.product', 'attachments'])->findOrFail($id);

        $pdf = new MyPDF;

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TGM');
        $pdf->SetTitle('PO-' . $purpos->id);
        $pdf->SetSubject('PO-' . $purpos->id);
        $pdf->SetKeywords('PO, TCPDF, PDF');

        $pdf->AddPage();
        $pdf->setCellPadding(1.2);

        // Heading
        $heading = '<h1 style="font-size:20px;text-align:center;font-style:italic;text-decoration:underline;color:#17365D">Purchase Order</h1>';
        $pdf->writeHTML($heading, true, false, true, false, '');

        // Basic Info Table
        $html = '<table style="margin-bottom:10px">
            <tr>
                <td style="font-size:10px;font-weight:bold;color:#17365D">PO No: <span style="color:#000">' . $purpos->po_code . '</span></td>
                <td style="font-size:10px;font-weight:bold;color:#17365D">Date: <span style="color:#000">' . \Carbon\Carbon::parse($purpos->order_date)->format('d-m-Y') . '</span></td>
                <td style="font-size:10px;font-weight:bold;color:#17365D">Vendor: <span style="text-decoration: underline;color:#000">' . $purpos->vendor->name . '</span></td>
                <td style="font-size:10px;font-weight:bold;color:#17365D">Order By: <span style="text-decoration: underline;color:#000">' . $purpos->order_by . '</span></td>
            </tr>
        </table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // Table Headers
        $html = '<table border="0.3" style="text-align:center;margin-top:15px">
            <tr>
                <th width="5%" style="font-size:10px;font-weight:bold;color:#17365D">S/N</th>
                <th width="28%" style="font-size:10px;font-weight:bold;color:#17365D">Name(ID)</th>
                <th width="18%" style="font-size:10px;font-weight:bold;color:#17365D">Description</th>
                <th width="8%" style="font-size:10px;font-weight:bold;color:#17365D">Width</th>
                <th width="15%" style="font-size:10px;font-weight:bold;color:#17365D">Qty</th>
                <th width="12%" style="font-size:10px;font-weight:bold;color:#17365D">Rate</th>
                <th width="15%" style="font-size:10px;font-weight:bold;color:#17365D">Total</th>
            </tr>';

        $total_qty = 0;
        $count = 0;

        foreach ($purpos->details as $item) {
            $count++;
            $product = $item->product;
            $total = $item->item_rate * $item->item_qty;
            $total_qty += $item->item_qty;

            $html .= '<tr>
                <td style="font-size:10px;">' . $count . '</td>
                <td style="font-size:10px;">' . ($product->name ?? '-') . '(' . ($product->id ?? '-') .')'. '</td>
                <td style="font-size:10px;">' . ($item->description ?? '-') . '</td>
                <td style="font-size:10px;">' . ($item->width ?? '-') . '</td>
                <td style="font-size:10px;">' . $item->item_qty . ' ' . ($product->measurement_unit ?? '-') . '</td>
                <td style="font-size:10px;">' . number_format($item->item_rate, 2) . '</td>
                <td style="font-size:10px;">' . number_format($total, 2) . '</td>
            </tr>';
        }

        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // Summary
        $summary = '<table border="0.3" cellpadding="2" width="35%">
            <tr><td><strong>Total Quantity</strong></td><td>' . $total_qty . '</td></tr>
            <tr><td><strong>Total Items</strong></td><td>' . $count . '</td></tr>
        </table>';
        $pdf->writeHTML($summary, true, false, true, false, '');

        // ✅ PurPoAttachment Images
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'PO Attachments:', 0, 1, 'L');

        $imageWidth = 50;
        $imageHeight = 50;
        $margin = 10;
        $maxX = $pdf->getPageWidth() - $pdf->getMargins()['right'];
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $rowHeight = $imageHeight + 5;

        foreach ($purpos->attachments as $attachment) {
            $imagePath = storage_path('app/public/' . $attachment->image_path);

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

                $pdf->Image($imagePath, $x, $y, '40', '60', '', '', '', false, 300, '', false, false, 0, false, false, false);

                // Caption below image
                $pdf->SetXY($x, $y + 62);
                $pdf->SetFont('helvetica', '', 8);
                $pdf->MultiCell(40, 10, 'Attachment', 0, 'C', false, 1);

                $x += $imageWidth + $margin;
            }
        }

        $pdf->SetY($y + $rowHeight);

        // Footer Signatures
        $pdf->SetY(-50);
        $lineWidth = 60;
        $yPosition = $pdf->GetY();

        $pdf->Line(28, $yPosition, 20 + $lineWidth, $yPosition);
        $pdf->Line(130, $yPosition, 120 + $lineWidth, $yPosition);
        $pdf->Ln(5);

        $pdf->SetXY(23, $yPosition);
        $pdf->Cell($lineWidth, 10, 'Approved By', 0, 0, 'C');

        $pdf->SetXY(125, $yPosition);
        $pdf->Cell($lineWidth, 10, 'Received By', 0, 0, 'C');

        return $pdf->Output('PO-' . $purpos->id . '.pdf', 'I');
    }
}

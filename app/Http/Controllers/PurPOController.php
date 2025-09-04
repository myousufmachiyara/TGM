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
    public function index(Request $request)
    {
        $filter = $request->input('status'); // Values: pending, partially received, completed, all (optional)

        $purpos = PurPO::with(['vendor', 'details.product'])->get();

        foreach ($purpos as $po) {
            $totalOrderedQty = $po->details->sum('item_qty');

            $receivedQty = DB::table('pur_pos_rec_details')
                ->join('pur_pos_rec', 'pur_pos_rec.id', '=', 'pur_pos_rec_details.pur_pos_rec_id')
                ->where('pur_pos_rec.po_id', $po->id)
                ->sum('pur_pos_rec_details.qty');

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

        // Apply filter if not "all" or empty
        if ($filter && strtolower($filter) !== 'all') {
            $purpos = $purpos->filter(function ($po) use ($filter) {
                return strtolower($po->status_text) === strtolower($filter);
            })->values(); // Reindex
        }

        return view('purchasing.po.index', compact('purpos', 'filter'));
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

        // Get the record
        $details = PurPosDetail::where('item_id', $productId)
            ->where('pur_pos_id', $poId)
            ->first(['width', 'item_rate']); // fetch both columns

        if (!$details) {
            return response()->json([
                'error' => 'Product details not found'
            ], 404);
        }

        return response()->json([
            'width' => $details->width,
            'item_rate' => $details->item_rate
        ]);
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
        $pdf->SetTitle($purpos->po_code);
        $pdf->SetSubject($purpos->po_code);
        $pdf->SetKeywords('PO, TCPDF, PDF');

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
            <tr><td><b>PO #</b></td><td>' . $purpos->po_code . '</td></tr>
            <tr><td><b>Date</b></td><td>' . \Carbon\Carbon::parse($purpos->order_date)->format('d/m/Y') . '</td></tr>
            <tr><td><b>Order By </b></td><td>' . $purpos->order_by . '</td></tr>
        </table>';
        $pdf->writeHTML($invoiceInfo, false, false, false, false, '');
        
        $pdf->SetXY(10, 40); // reset position to left side, Y around 60mm

        $partyname = '
        <table cellpadding="2" style="font-size:12px;">
            <tr><td><b>Party Name:</b>' . $purpos->vendor->name . '</td></tr>
        </table>';
        $pdf->writeHTML($partyname, false, false, false, false, '');
        
        // Horizontal Line (left to PO box only)
        $pdf->Line(10, 53, 150, 53); // Line ends just before the blue box

        // Blue PO box (same position)
        $pdf->SetXY(150, 49);
        $pdf->SetFillColor(23, 54, 93); // Blue
        $pdf->SetTextColor(255, 255, 255); // White
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 8, 'Purchase Order', 0, 1, 'C', 1);
        $pdf->SetTextColor(0, 0, 0); // Reset to black

        $pdf->SetXY(10, 63);

        // Items Table Header
        $html = '<table border="0.3" cellpadding="4" style="font-size:10px;">
            <tr style="background-color:#f5f5f5;">
                <th width="5%">S.#</th>
                <th width="35%">Item Name</th>
                <th width="15%">Qty</th>
                <th width="15%">Rate</th>
                <th width="30%">Amount</th>
            </tr>';

        $count = 0;
        $grandTotal = 0;
        $grandDiscount = 0;
        $netTotal = 0;

        foreach ($purpos->details as $item) {
            $count++;
            $product = $item->product;
            $qty = $item->item_qty;
            $rate = $item->item_rate;
            $amount = $qty * $rate;
            $grandTotal += $amount;

            $html .= '
            <tr>
                <td align="center">' . $count . '</td>
                <td>' . ($product->name ?? '-') . ' (' . ($product->id ?? '-') . ')</td>
                <td align="center">' . $qty . '</td>
                <td align="right">' . number_format($rate, 2) . '</td>
                <td align="right">' . number_format($amount, 2) . '</td>
            </tr>';
        }

        // Totals row
        $html .= '
        <tr>
            <td colspan="4" align="right"><b>Total</b></td>
            <td align="right"><b>' . number_format($grandTotal, 2) . '</b></td>
        </tr>
        </table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        // Footer Signature Lines
        $pdf->Ln(20);
        $lineWidth = 60;
        $yPosition = $pdf->GetY();

        $pdf->Line(28, $yPosition, 20 + $lineWidth, $yPosition);
        $pdf->Line(130, $yPosition, 120 + $lineWidth, $yPosition);
        $pdf->Ln(5);

        $pdf->SetXY(23, $yPosition);
        $pdf->Cell($lineWidth, 10, 'Prepared / Checked By', 0, 0, 'C');

        return $pdf->Output($purpos->po_code. '.pdf', 'I');
    }

}

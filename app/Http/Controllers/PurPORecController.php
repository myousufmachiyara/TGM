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

class PurPORecController extends Controller
{
    public function index()
    {
        $receivings = PurPORec::with('PO')->latest()->get();
        return view('purchasing.po-rec.index', compact('receivings'));
    }

    public function create(){
    }
    
    public function createForm($id)
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

        return view('purchasing.po-rec.create', compact('purpo'));
    }

    public function store(Request $request)
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

            return redirect()->route('pur-po-rec.index')->with('success', 'Receiving recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Exception in storeReceiving: ' . $e->getMessage());
            return back()->with('error', 'Receiving failed: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $rec = PurPORec::with('details')->findOrFail($id);
        $purpo = PurPo::with('details.product')->findOrFail($rec->po_id);

        // Calculate remaining quantities
        $receivedQuant = PurPORecDetails::where('pur_pos_rec_id', '<>', $rec->id)
            ->whereHas('receiving', fn($q) => $q->where('po_id', $purpo->id))
            ->selectRaw('product_id, SUM(qty) as total_received')
            ->groupBy('product_id')
            ->pluck('total_received', 'product_id');

        foreach ($purpo->details as $d) {
            $d->product_name = $d->product->name ?? 'N/A';
            $prevRec = $rec->details->first(fn($x)=> $x->product_id==$d->item_id);
            $d->received_in_this = $prevRec->qty ?? 0;
            $d->rate_in_this = $prevRec->rate ?? '';
            $already = $receivedQuant[$d->item_id] ?? 0;
            $d->remaining_qty = $d->item_qty - $already;
        }

        return view('purchasing.po-rec.edit', compact('purpo', 'rec'));
    }

    public function update(Request $request, $id)
    {
        Log::debug('Update receiving', $request->all());

        $request->validate([
            'rec_date' => 'required|date',
            'received_qty.*' => 'nullable|numeric|min:0',
            'received_rate.*' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $rec = PurPORec::findOrFail($id);
            $rec->rec_date = $request->rec_date;
            $rec->save();

            // Remove old details
            $rec->details()->delete();

            $saved = false;
            foreach ($request->received_qty as $detId => $qty) {
                if ($qty > 0) {
                    $rate = floatval($request->received_rate[$detId] ?? 0);
                    PurPORecDetails::create([
                    'pur_pos_rec_id' => $rec->id,
                    'product_id' => PurPosDetail::find($detId)->item_id,
                    'sku' => '', 'qty' => $qty, 'rate' => $rate,
                    ]);
                    $saved = true;
                }
            }

            if (!$saved) throw new \Exception('No items with qty > 0 to save');

            DB::commit();
            return redirect()->route('pur-po-rec.index')->with('success','PO Receiving updated');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update error', ['msg'=>$e->getMessage()]);
            return back()->with('error','Update failed: '.$e->getMessage());
        }
    }

    public function print($id)
    {
        $rec = PurPORec::with(['details.product', 'details.product.category', 'po.vendor'])->findOrFail($id);
        $po = $rec->po;

        $pdf = new MyPDF;

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TGM');
        $pdf->SetTitle('GRN-' . $rec->id);
        $pdf->SetSubject('Goods Receiving Note');
        $pdf->SetKeywords('GRN, TCPDF, PDF');

        $pdf->AddPage();
        $pdf->setCellPadding(1.2);

        // Heading
        $heading = '<h1 style="font-size:20px;text-align:center;font-style:italic;text-decoration:underline;color:#17365D">Goods Receiving Note (GRN)</h1>';
        $pdf->writeHTML($heading, true, false, true, false, '');

        // Basic Info Table
        $html = '<table style="margin-bottom:10px">
            <tr>
                <td style="font-size:10px;font-weight:bold;color:#17365D">GRN #: <span style="color:#000">GRN-' . $rec->id . '</span></td>
                <td style="font-size:10px;font-weight:bold;color:#17365D">PO #: <span style="color:#000">PO-' . $po->id . '</span></td>
                <td style="font-size:10px;font-weight:bold;color:#17365D">Vendor: <span style="text-decoration: underline;color:#000">' . ($po->vendor->name ?? '-') . '</span></td>
                <td style="font-size:10px;font-weight:bold;color:#17365D">Receiving Date: <span style="text-decoration: underline;color:#000">' . \Carbon\Carbon::parse($rec->rec_date)->format('d-m-Y') . '</span></td>
            </tr>
        </table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // Table Headers
        $html = '<table border="0.3" style="text-align:center;margin-top:15px">
            <tr>
                <th width="5%" style="font-size:10px;font-weight:bold;color:#17365D">S/N</th>
                <th width="20%" style="font-size:10px;font-weight:bold;color:#17365D">Name(ID)</th>
                <th width="20%" style="font-size:10px;font-weight:bold;color:#17365D">Category</th>
                <th width="15%" style="font-size:10px;font-weight:bold;color:#17365D">Description</th>
                <th width="8%" style="font-size:10px;font-weight:bold;color:#17365D">Width</th>
                <th width="12%" style="font-size:10px;font-weight:bold;color:#17365D">Qty</th>
                <th width="10%" style="font-size:10px;font-weight:bold;color:#17365D">Rate</th>
                <th width="12%" style="font-size:10px;font-weight:bold;color:#17365D">Total</th>
            </tr>';

        $total_qty = 0;
        $count = 0;

        foreach ($rec->details as $item) {
            $count++;
            $product = $item->product;
            $total = $item->rate * $item->qty;
            $total_qty += $item->qty;

            $html .= '<tr>
            <td style="font-size:10px;">' . $count . '</td>
            <td style="font-size:10px;">' . ($product->name ?? '-') . ' (' . ($product->id ?? '-') . ')</td>
            <td style="font-size:10px;">' . ($product->category->name ?? '-') . '</td>
            <td style="font-size:10px;">' . ($product->description ?? '-') . '</td>
            <td style="font-size:10px;">' . ($product->width ?? '-') . '</td>
            <td style="font-size:10px;">' . number_format($item->qty, 2) . ' ' . ($product->measurement_unit ?? '-') . '</td>
            <td style="font-size:10px;">' . number_format($item->rate, 2) . '</td>
            <td style="font-size:10px;">' . number_format($total, 2) . '</td>
            </tr>';
        }

        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // Summary
        $summary = '<table border="0.3" cellpadding="3" width="35%">
            <tr><td style="font-size:11px"><strong>Total Quantity</strong></td><td>' . number_format($total_qty, 2) . '</td></tr>
            <tr><td style="font-size:11px"><strong>Total Items</strong></td><td>' . $count . '</td></tr>
        </table>';
        $pdf->writeHTML($summary, true, false, true, false, '');

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

        return $pdf->Output('GRN-' . $rec->id . '.pdf', 'I');
    }

}

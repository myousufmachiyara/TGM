<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurFGPO;
use App\Models\PurFGPODetails;
use App\Models\PurFGPORec;
use App\Models\PurFGPORecDetails;

class PurFGPORecController extends Controller
{
    /**
     * List all receivings
     */
    public function index()
    {
        $receivings = PurFGPORec::with(['FGPO', 'details'])->get();

        return view('purchasing.fg-po-rec.index', compact('receivings'));
    }

    /**
     * Show create receiving form for given FGPO
     */
    public function create($id)
    {
        $purpo = PurFGPO::with('details.product', 'details.variation')->findOrFail($id);

        // Fetch total received quantity grouped by variation_id for this FGPO
        $receivedQuantities = PurFGPORecDetails::whereHas('receiving', function ($query) use ($id) {
            $query->where('fgpo_id', $id);
        })->selectRaw('variation_id, SUM(qty) as total_received')
            ->groupBy('variation_id')
            ->pluck('total_received', 'variation_id');

        foreach ($purpo->details as $detail) {
            $detail->total_received = $receivedQuantities[$detail->variation_id] ?? 0;
        }

        return view('purchasing.fg-po.receiving', compact('purpo'));
    }

    /**
     * Store receiving record
     */
    public function store(Request $request)
    {
        $request->validate([
            'fgpo_id'       => 'required|exists:pur_fgpos,id',
            'rec_date'      => 'required|date',
            'received_qty'  => 'required|array',
            'received_qty.*'=> 'nullable|integer|min:1',
        ]);

        $receiving = PurFGPORec::create([
            'fgpo_id'    => $request->fgpo_id,
            'rec_date'   => $request->rec_date,
            'created_by' => auth()->id(),
        ]);

        foreach ($request->received_qty as $fgpoDetailId => $receivedQty) {
            if ($receivedQty > 0) {
                $orderDetail = PurFGPODetails::find($fgpoDetailId);

                if ($orderDetail) {
                    PurFGPORecDetails::create([
                        'pur_fgpos_rec_id' => $receiving->id,
                        'product_id'       => $orderDetail->product_id,
                        'variation_id'     => $orderDetail->variation_id,
                        'sku'              => $orderDetail->sku ?? '',
                        'qty'              => $receivedQty,
                    ]);
                }
            }
        }

        return redirect()->route('fgpo-receivings.index')->with('success', 'Receiving recorded successfully.');
    }

    // Edit receiving
    public function edit($id)
    {
        $receiving = PurFGPORec::with(['fgpo.details.product', 'fgpo.details.variation', 'details'])->findOrFail($id);

        // Map received quantities per detail
        $receivedQtys = $receiving->details->pluck('qty', 'variation_id')->toArray();

        foreach ($receiving->fgpo->details as $detail) {
            $alreadyReceived = PurFGPORecDetails::whereHas('receiving', function($q) use ($receiving){
                $q->where('fgpo_id', $receiving->fgpo_id)->where('id', '!=', $receiving->id);
            })->where('variation_id', $detail->variation_id)->sum('qty');

            $detail->total_received = $alreadyReceived;
            $detail->current_received = $receivedQtys[$detail->variation_id] ?? 0;
            $detail->remaining_qty = $detail->qty - $detail->total_received;
        }

        return view('purchasing.fg-po-rec.edit', compact('receiving'));
    }

    // Update receiving
    public function update(Request $request, $id)
    {
        $request->validate([
            'rec_date'      => 'required|date',
            'received_qty'  => 'required|array',
            'received_qty.*'=> 'nullable|integer|min:1',
        ]);

        $receiving = PurFGPORec::findOrFail($id);
        $receiving->update([
            'rec_date' => $request->rec_date,
            'updated_by' => auth()->id(),
        ]);

        // Delete old details
        $receiving->details()->delete();

        // Save new details
        foreach ($request->received_qty as $fgpoDetailId => $receivedQty) {
            if ($receivedQty > 0) {
                $orderDetail = PurFGPODetails::find($fgpoDetailId);
                if ($orderDetail) {
                    PurFGPORecDetails::create([
                        'pur_fgpos_rec_id' => $receiving->id,
                        'product_id'       => $orderDetail->product_id,
                        'variation_id'     => $orderDetail->variation_id,
                        'sku'              => $orderDetail->sku ?? '',
                        'qty'              => $receivedQty,
                    ]);
                }
            }
        }

        return redirect()->route('pur-fgpo-rec.index')->with('success', 'Receiving updated successfully.');
    }

    public function print($id)
    {
        $rec = \App\Models\PurFGPORec::with(['fgpo.vendor', 'details.product'])->findOrFail($id);

        $pdf = new \App\Services\myPDF;

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TGM');
        $pdf->SetTitle('FGPO Receiving - ' . $rec->id);
        $pdf->SetSubject('FGPO Receiving');
        $pdf->SetKeywords('FGPO, TCPDF, PDF');

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

        // GRN Info
        $pdf->SetXY(150, 12);
        $info = '
        <table cellpadding="2" style="font-size:10px;">
            <tr><td><b>GRN #</b></td><td>' . $rec->id . '</td></tr>
            <tr><td><b>Date</b></td><td>' . \Carbon\Carbon::parse($rec->date)->format('d/m/Y') . '</td></tr>
            <tr><td><b>PO #</b></td><td>' . ($rec->fgpo->po_code ?? '-') . '</td></tr>
        </table>';
        $pdf->writeHTML($info, false, false, false, false, '');

        $pdf->SetXY(10, 40);

        // Vendor
        $partyname = '
        <table cellpadding="2" style="font-size:12px;">
            <tr><td><b>Vendor:</b> ' . ($rec->fgpo->vendor->name ?? '-') . '</td></tr>
        </table>';
        $pdf->writeHTML($partyname, false, false, false, false, '');

        // Horizontal Line
        $pdf->Line(10, 53, 150, 53);

        // Blue Title Box
        $pdf->SetXY(150, 49);
        $pdf->SetFillColor(23, 54, 93);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 8, 'FGPO Receiving', 0, 1, 'C', 1);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetXY(10, 63);

        // Items Table
        $html = '<table border="0.3" cellpadding="4" style="font-size:10px;">
            <tr style="background-color:#f5f5f5;">
                <th width="5%">S.#</th>
                <th width="35%">Item</th>
                <th width="15%">Qty</th>
                <th width="15%">Rate</th>
                <th width="30%">Amount</th>
            </tr>';

        $count = 0;
        $grandTotal = 0;

        foreach ($rec->details as $item) {
            $count++;
            $product = $item->product;
            $qty = $item->qty;
            $rate = $item->rate;
            $amount = $qty * $rate;
            $grandTotal += $amount;

            $html .= '
            <tr>
                <td align="center">' . $count . '</td>
                <td>' . ($product->name ?? '-') . '</td>
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

        // Footer signatures
        $pdf->Ln(20);
        $lineWidth = 60;
        $yPosition = $pdf->GetY();

        $pdf->Line(28, $yPosition, 20 + $lineWidth, $yPosition);
        $pdf->Line(130, $yPosition, 120 + $lineWidth, $yPosition);
        $pdf->Ln(5);

        $pdf->SetXY(23, $yPosition);
        $pdf->Cell($lineWidth, 10, 'Prepared / Checked By', 0, 0, 'C');

        return $pdf->Output('FGPO-Rec-' . $rec->id . '.pdf', 'I');
    }

}

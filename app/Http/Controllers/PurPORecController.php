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
}

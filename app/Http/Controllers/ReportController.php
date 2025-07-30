<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\ChartOfAccounts;

class ReportController extends Controller
{
    public function itemLedger(Request $request)
    {
        $items = Products::select('id', 'name')->orderBy('name')->get();

        $itemId = $request->item_id;
        $from = $request->from_date ?? now()->startOfMonth()->toDateString();
        $to = $request->to_date ?? now()->endOfMonth()->toDateString();

        $ledger = [];

        if ($itemId) {
            $ledger = collect();

            // PO Receiving - Qty In
            $poIn = DB::table('pur_pos_rec_details as d')
                ->join('pur_pos_rec as r', 'r.id', '=', 'd.pur_pos_rec_id')
                ->where('d.product_id', $itemId)
                ->whereBetween('r.rec_date', [$from, $to])
                ->select('r.rec_date as date', DB::raw("'PO Receiving' as type"), DB::raw("'' as description"), 'd.qty as qty_in', DB::raw('0 as qty_out'))
                ->get();

            // Sent to Production - Qty Out (Assumed table: production_raw_materials)
            $prodOut = DB::table('pur_fgpos_voucher_details as d')
                ->join('pur_fgpos as p', 'p.id', '=', 'd.fgpo_id')
                ->where('d.product_id', $itemId)
                ->whereBetween('p.order_date', [$from, $to])
                ->select('p.order_date as date', DB::raw("'Issued to Production' as type"), DB::raw("'' as description"), DB::raw('0 as qty_in'), 'd.qty as qty_out')
                ->get();

            // Received from Production - Qty In
            $prodIn = DB::table('pur_fgpos_rec_details as d')
                ->join('pur_fgpos_rec as r', 'r.id', '=', 'd.pur_fgpos_rec_id')
                ->where('d.product_id', $itemId)
                ->whereBetween('r.rec_date', [$from, $to])
                ->select('r.rec_date as date', DB::raw("'Received from Production' as type"), DB::raw("'' as description"), 'd.qty as qty_in', DB::raw('0 as qty_out'))
                ->get();

            // Merge & sort by date
            $ledger = $poIn->merge($prodOut)->merge($prodIn)->sortBy('date')->values();
        }

        return view('reports.item_ledger', compact('items', 'itemId', 'from', 'to', 'ledger'));
    }

    public function partyLedger(Request $request)
    {
        $vendors = ChartOfAccounts::orderBy('name')->get();
        $vendorId = $request->vendor_id;
        $from = $request->from_date ?? now()->startOfMonth()->toDateString();
        $to = $request->to_date ?? now()->endOfMonth()->toDateString();
        $ledger = [];

        if ($vendorId) {
            $ledger = collect();

            // PO Receiving = Credit
            $poCredit = DB::table('pur_pos_rec_details as rd')
                ->join('pur_pos_rec as r', 'r.id', '=', 'rd.pur_pos_rec_id')
                ->join('pur_pos as po', 'po.id', '=', 'r.po_id')
                ->where('po.vendor_id', $vendorId)
                ->whereBetween('r.rec_date', [$from, $to])
                ->select(
                    'r.rec_date as date',
                    DB::raw("'PO Receiving' as type"),
                    DB::raw("'' as description"),
                    DB::raw("rd.qty * rd.rate as credit"),
                    DB::raw("0 as debit")
                )
                ->get();

            // Payments = Debit
            $payments = DB::table('payment_vouchers')
                ->where(function ($q) use ($vendorId) {
                    $q->where('ac_cr_sid', $vendorId)
                      ->orWhere('ac_dr_sid', $vendorId);
                })
                ->whereBetween('date', [$from, $to])
                ->select(
                    'date',
                    DB::raw("'Payment' as type"),
                    'remarks as description',
                    DB::raw("0 as credit"),
                    'amount as debit'
                )
                ->get();

            $ledger = $poCredit->merge($payments)->sortBy('date')->values();
        }

        return view('reports.party_ledger', compact('vendors', 'vendorId', 'from', 'to', 'ledger'));
    }
}

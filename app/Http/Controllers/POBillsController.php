<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccounts;
use App\Models\PurFGPO;
use Illuminate\Http\Request;

class POBillsController extends Controller
{

    public function index()
    {
        return view('purchasing.fgpo-billing.index');
    }

    public function create()
    {
        $coa = ChartOfAccounts::all();  // Get all product categories
        $fgpo = PurFGPO::all();  // Get all product categories

        return view('purchasing.fgpo-billing.create', compact('coa', 'fgpo'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:chart_of_accounts,id',
            'bill_date' => 'required|date',
            'ref_bill'  => 'nullable|string|max:255',
            'details'   => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            $bill = FgpoBill::create([
                'vendor_id'    => $request->vendor_id,
                'bill_date'    => $request->bill_date,
                'ref_bill'     => $request->ref_bill,
                'created_by'   => auth()->id(),
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($request->details as $poDetail) {
                $productionId   = $poDetail['production_id'];
                $adjustedAmount = $poDetail['adjusted_amount'] ?? 0;

                if (!empty($poDetail['products'])) {
                    foreach ($poDetail['products'] as $product) {
                        $rate = $product['rate'] ?? 0;

                        FgpoBillDetail::create([
                            'bill_id'       => $bill->id,
                            'production_id' => $productionId,
                            'product_id'    => $product['product_id'],
                            'rate'          => $rate,
                            'adjusted_amount' => $adjustedAmount, // saved for each product row
                        ]);

                        $totalAmount += $rate;
                    }
                }

                // add adjustment once per PO (not per product)
                $totalAmount += $adjustedAmount;
            }

            $bill->update([
                'total_amount' => $totalAmount,
            ]);

            DB::commit();

            return redirect()->route('pur-fgpos.index')->with('success', 'Bill added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Error saving Bill: ' . $e->getMessage()]);
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}

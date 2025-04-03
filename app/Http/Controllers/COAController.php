<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccounts;
use App\Models\SubHeadOfAccounts;
use Illuminate\Http\Request;

class COAController extends Controller
{
    public function index()
    {
        $chartOfAccounts = ChartOfAccounts::with('subHeadOfAccount')->get();
        $subHeadOfAccounts = SubHeadOfAccounts::with('headOfAccount')->get();

        return view('accounts.coa', compact('chartOfAccounts', 'subHeadOfAccounts'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'shoa_id' => 'required|exists:sub_head_of_accounts,id',
                'name' => 'required|string|max:255|unique:chart_of_accounts',
                'receivables' => 'required|numeric',
                'payables' => 'required|numeric',
                'opening_date' => 'required|date',
                'remarks' => 'nullable|string|max:800',
                'address' => 'nullable|string|max:250',
                'phone_no' => 'nullable|string|max:250',
                'credit_limit' => 'required|numeric',
                'days_limit' => 'required|integer',
            ]);

            ChartOfAccounts::create($request->all());

            return redirect()->route('coa.index')->with('success', 'Chart of Account created successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $chartOfAccount = ChartOfAccounts::with('subHeadOfAccount')->findOrFail($id);

        return view('coa.show', compact('chartOfAccount'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'shoa_id' => 'required|exists:sub_head_of_accounts,id',
            'name' => 'required|string|max:255',
            'receivables' => 'required|numeric',
            'payables' => 'required|numeric',
            'opening_date' => 'required|date',
            'remarks' => 'nullable|string|max:800',
            'address' => 'nullable|string|max:250',
            'phone_no' => 'nullable|string|max:250',
            'credit_limit' => 'required|numeric',
            'days_limit' => 'required|integer',
        ]);

        $chartOfAccount = ChartOfAccounts::findOrFail($id);
        $chartOfAccount->update($request->all());

        return redirect()->route('coa.index')->with('success', 'Chart of Account updated successfully.');
    }

    public function destroy($id)
    {
        $chartOfAccount = ChartOfAccounts::findOrFail($id);
        $chartOfAccount->delete();

        return redirect()->route('coa.index')->with('success', 'Chart of Account deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccounts;
use App\Models\PurFGPO;
use Illuminate\Http\Request;

class POBillsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('purchasing.fgpo-billing.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $coa = ChartOfAccounts::all();  // Get all product categories
        $fgpo = PurFGPO::all();  // Get all product categories

        return view('purchasing.fgpo-billing.create', compact('coa', 'fgpo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

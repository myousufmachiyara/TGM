<?php

namespace App\Http\Controllers;

use App\Models\PurPO;
use App\Http\Resources\PurPOResource;
use Illuminate\Http\Request;

class PurPOController extends Controller
{
    /**
     * Display a listing of the resource for web.
     */
    public function index()
    {
        $purpos = PurPO::all();
        return view('purchasing.po.index', compact('purpos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('purchasing.po.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fabric' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0',
            'payment_term' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'vendor_name' => 'required|string|max:255',
        ]);

        PurPO::create($validated);

        return redirect()->route('purchasing.po.index')->with('success', 'Purchase Order created successfully!');
    }

    /**
     * Display the specified resource for web.
     */
    public function show(PurPO $purpo)
    {
        return view('purchasing.po.show', compact('purpo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurPO $purpo)
    {
        return view('purchasing.po.edit', compact('purpo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurPO $purpo)
    {
        $validated = $request->validate([
            'fabric' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0',
            'payment_term' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'vendor_name' => 'required|string|max:255',
        ]);

        $purpo->update($validated);

        return redirect()->route('purchasing.po.index')->with('success', 'Purchase Order updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurPO $purpo)
    {
        $purpo->delete();

        return redirect()->route('purchasing.po.index')->with('success', 'Purchase Order deleted successfully!');
    }

    /**
     * API: Display a listing of the resource.
     */
    public function indexAPI()
    {
        $purpos = PurPO::paginate(10); // Add pagination for large datasets
        return PurPOResource::collection($purpos);
    }

    /**
     * API: Display the specified resource.
     */
    public function showAPI(PurPO $purpo)
    {
        return new PurPOResource($purpo);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PurPo;
use App\Models\PurPosDetail;
use App\Models\ProductCategory;
use App\Models\ChartOfAccounts;
use App\Models\Products;

use Illuminate\Http\Request;

class PurPOController extends Controller
{

    public function index()
    {
        $purpos = PurPo::with('details')->get(); // Include details with the purchase orders
        return view('purchasing.po.index', compact('purpos'));
    }

    public function create()
    {
        $prodCat = ProductCategory::all();  // Get all product categories
        $coa = ChartOfAccounts::all();  // Get all product categories
        $products = Products::all();  // Get all product categories

        
        return view('purchasing.po.create', compact('prodCat', 'coa', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'order_date' => 'required|date',
            'delivery_date' => 'required|date',
            'payment_term' => 'required|string|max:255',
            'details.*.item_name' => 'required|string|max:255',
            'details.*.category_id' => 'required|exists:product_categories,id',
            'details.*.item_rate' => 'required|numeric|min:0',
            'details.*.item_qty' => 'required|numeric|min:0',
        ]);
        
        $purpo = PurPo::create($validated);

        foreach ($validated['details'] as $detail) {
            $detail['pur_pos_id'] = $purpo->id; // Set the foreign key
            PurPosDetail::create($detail);
        }

        return redirect()->route('purpos.index')->with('success', 'Purchase Order created successfully!');
    }

    public function show(PurPos $purpo)
    {
        $purpo->load('details'); // Eager load details
        return view('purpos.show', compact('purpo'));
    }

    public function edit(PurPo $purpo)
    {
        $purpo->load('details'); // Eager load details
        $prodCat = ProductCategory::all();
        $produnits = ProductMeasurementUnit::all();

        return view('purchasing.po.edit', compact('purpo', 'prodCat', 'produnits'));
    }

    public function update(Request $request, PurPos $purpo)
    {
        $validated = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'order_date' => 'required|date',
            'delivery_date' => 'required|date',
            'payment_term' => 'required|string|max:255',
            'details.*.id' => 'nullable|exists:pur_pos_details,id',
            'details.*.fabric' => 'required|string|max:255',
            'details.*.category_id' => 'required|exists:categories,id',
            'details.*.rate' => 'required|numeric|min:0',
            'details.*.quantity' => 'required|numeric|min:0',
        ]);

        // Update Purchase Order
        $purpo->update($validated);

        // Update or create associated details
        foreach ($validated['details'] as $detail) {
            if (isset($detail['id'])) {
                // Update existing detail
                $detailModel = PurPosDetail::findOrFail($detail['id']);
                $detailModel->update($detail);
            } else {
                // Create a new detail
                $detail['pur_pos_id'] = $purpo->id;
                PurPosDetail::create($detail);
            }
        }

        return redirect()->route('purpos.index')->with('success', 'Purchase Order updated successfully!');
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

    public function showAPI(PurPO $purpo)
    {
        return new PurPOResource($purpo);
    }
    
}

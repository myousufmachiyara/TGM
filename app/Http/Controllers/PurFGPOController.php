<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PurFGPO;
use App\Models\PurPosDetail;
use App\Models\ChartOfAccounts;
use App\Models\Products;
use App\Models\ProductAttributes;

use App\Services\myPDF;

class PurFGPOController extends Controller
{
    public function index()
    {
        $purpos = PurFGPO::with('vendor')->get();
        return view('purchasing.fg-po.index', compact('purpos'));
    }

    public function create()
    {
        $coa = ChartOfAccounts::all();  // Get all product categories
        $fabrics = Products::where('item_type' , 'raw')->get();  // Get all product categories
        $articles = Products::where('item_type' , 'fg')->get();  // Get all product categories
        $attributes = ProductAttributes::with('values')->get();
        
        return view('purchasing.fg-po.create', compact( 'coa', 'fabrics', 'articles', 'attributes'));
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'vendor_name' => 'required|exists:vendors,id',
            'order_date' => 'required|date',
            'details' => 'required|array',
            'details.*.item_id' => 'required|exists:fabrics,id',
            'details.*.item_rate' => 'required|numeric|min:0',
            'details.*.item_qty' => 'required|numeric|min:0',
            'att.*' => 'nullable|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    
        try {
            DB::beginTransaction();
    
            // Create new Purchase Order
            $po = new PurFGPO();
            $po->vendor_id = $request->vendor_name;
            $po->order_date = $request->order_date;
            $po->total_amount = 0;
            $po->save();
    
            $totalAmount = 0;
    
            // Store Purchase Order Details (Fabrics)
            foreach ($request->details as $index => $detail) {
                $fabric = new PurchaseOrderFabric();
                $fabric->purchase_order_id = $po->id;
                $fabric->fabric_id = $detail['item_id'];
                $fabric->description = $detail['for'] ?? null;
                $fabric->rate = $detail['item_rate'];
                $fabric->quantity = $detail['item_qty'];
                $fabric->total = $detail['item_rate'] * $detail['item_qty'];
                $fabric->save();
    
                $totalAmount += $fabric->total;
            }
    
            // Update total amount
            $po->total_amount = $totalAmount;
            $po->save();
    
            // Handle file uploads (attachments)
            if ($request->hasFile('att')) {
                foreach ($request->file('att') as $file) {
                    $filePath = $file->store('purchase_orders', 'public');
                    $po->attachments()->create(['file_path' => $filePath]);
                }
            }
    
            DB::commit();
    
            return redirect()->route('pur-fgpos.index')->with('success', 'Purchase Order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Something went wrong! ' . $e->getMessage()]);
        }
    }

    public function newChallan(){
        $purpos = PurFGPO::get('id');
        $coa = ChartOfAccounts::all();  // Get all product categories
        $products = Products::all();  // Get all product categories
        $attributes = ProductAttributes::with('values')->get();
        
        return view('purchasing.fg-po.new-challan', compact( 'coa', 'products', 'attributes','purpos'));
    }

    public function receiving(){
        $purpos = PurFGPO::get();
        return view('purchasing.fg-po.receiving', compact('purpos'));
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PurFGPO;
use App\Models\PurFGPODetails;
use App\Models\ChartOfAccounts;
use App\Models\JournalVoucher1;
use App\Models\PurFGPOVoucherDetails;
use App\Models\PurFGPOAttachements;
use App\Models\Products;
use App\Models\ProductAttributes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        \Log::info('Starting FGPO Store process', $request->all());
    
        $request->validate([
            'vendor_id' => 'required|exists:chart_of_accounts,id',
            'order_date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'width' => 'required|numeric',
            'consumption' => 'required|numeric',
            'item_order' => 'required|array',
            'item_order.*.product_id' => 'required|exists:products,id',
            'item_order.*.variation_id' => 'required|exists:product_variations,id',
            'item_order.*.sku' => 'required|string',
            'item_order.*.qty' => 'required|integer|min:1',
            'voucher_amount' => 'required|numeric',
            'voucher_details' => 'required|array',
            'voucher_details.*.product_id' => 'required|exists:products,id',
            'voucher_details.*.description' => 'required|string',
            'voucher_details.*.qty' => 'required|numeric',
            'voucher_details.*.unit' => 'required|string',
            'voucher_details.*.item_rate' => 'required|numeric',
        ]);
    
        DB::beginTransaction();
        
        try {
            \Log::info('Creating FGPO record');
            $fgpo = PurFGPO::create([
                'doc_code' => 'FGPO',
                'vendor_id' => $request->vendor_id,
                'order_date' => $request->order_date,
                'width' => $request->width,
                'consumption' => $request->consumption,
            ]);
            \Log::info('FGPO Created', ['fgpo_id' => $fgpo->id]);
    
            \Log::info('Adding FGPO Product Variations');
            foreach ($request->item_order as $detail) {
                \Log::info('Processing Item Order:', $detail);
                PurFGPODetails::create([
                    'fgpo_id' => $fgpo->id,
                    'product_id' => $detail['product_id'],
                    'variation_id' => $detail['variation_id'],
                    'sku' => $detail['sku'],
                    'qty' => $detail['qty'],
                ]);
            }
            \Log::info('FGPO Product Variations Added');
    
            \Log::info('Creating Journal Voucher');
            $voucher = JournalVoucher1::create([
                'debit_acc_id' => $request->vendor_id,
                'credit_acc_id' => "4",
                'amount' => $request->voucher_amount,
                'date' => $request->order_date,
                'narration' => "Transaction Against FGPO",
                'ref_doc_id' => $fgpo->id,
                'ref_doc_code' => 'FGPO',
            ]);
            \Log::info('Journal Voucher Created', ['voucher_id' => $voucher->id]);
    
            \Log::info('Adding Voucher Details');
            foreach ($request->voucher_details as $detail) {
                \Log::info('Processing Voucher Detail:', $detail);
                PurFGPOVoucherDetails::create([
                    'fgpo_id' => $fgpo->id,
                    'voucher_id' => $voucher->id,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'unit' => $detail['unit'],
                    'rate' => $detail['item_rate'],
                    'description' => $detail['description'],
                ]);
            }
            \Log::info('Voucher Details Added');
    
            if ($request->hasFile('attachments')) {
                \Log::info('Processing Attachments');
                foreach ($request->file('attachments') as $file) {
                    $filePath = $file->store('attachments/fgpo_' . $fgpo->id, 'public');
                    PurFGPOAttachements::create([
                        'fgpo_id' => $fgpo->id,
                        'att_path' => $filePath,
                    ]);
                    \Log::info('Attachment Stored', ['path' => $filePath]);
                }
            }
    
            DB::commit();
            \Log::info('Transaction Committed Successfully');
            return redirect()->route('pur-fgpos.index')->with('success', 'Purchase Order created successfully!');
        
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating Purchase Order', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('pur-fgpos.index')->with('error', 'Error creating Purchase Order: ' . $e->getMessage());
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

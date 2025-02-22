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
        $purpos = PurFGPO::with(['vendor','details.product','details.variation.attribute_values'])->get();
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

    public function print($id)
    {
        // Fetch the purchase order with related data
        $purpos = PurFGPO::with(['vendor', 'details.product', 'attachments','details.product'])->findOrFail($id);

        if (!$purpos) {
            abort(404, 'Purchase Order not found.');
        }
    
        $pdf = new MyPDF();
    
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TGM');
        $pdf->SetTitle('Purchase Order - ' . $purpos->id);
        $pdf->SetSubject('Purchase Order - ' . $purpos->id);
        $pdf->SetKeywords('Purchase Order, TCPDF, PDF');
    
        // Add a page
        $pdf->AddPage();
        $pdf->setCellPadding(1.2); 
    
        // Purchase Order Heading
        $heading = '<h1 style="font-size:20px;text-align:center;font-style:italic;text-decoration:underline;color:#17365D">Purchase Order</h1>';
        $pdf->writeHTML($heading, true, false, true, false, '');
    
        // Purchase Order Details Table
        $html = '<table style="margin-bottom:10px">
            <tr>
                <td style="font-size:10px;font-weight:bold;color:#17365D">PO No: <span style="text-decoration: underline;color:#000">'.$purpos->id.'</span></td>
                <td style="font-size:10px;font-weight:bold;color:#17365D">Date: <span style="color:#000">'.\Carbon\Carbon::parse($purpos->order_date)->format('d-m-Y').'</span></td>
                <td style="font-size:10px;font-weight:bold;color:#17365D">Quotation No: <span style="text-decoration: underline;color:#000">N/A</span></td>
            </tr>
        </table>';
    
        // Vendor and Account Details
        $html .= '<table border="0.1" style="border-collapse: collapse;">
            <tr>
                <td width="20%" style="font-size:10px;font-weight:bold;color:#17365D">Vendor Name</td>
                <td width="30%" style="font-size:10px;">'.$purpos->vendor->name.'</td>
                <td width="20%" style="font-size:10px;font-weight:bold;color:#17365D">Address</td>
                <td width="30%" style="font-size:10px;">'.$purpos->vendor->address.'</td>
            </tr>
            <tr>
                <td width="20%" style="font-size:10px;font-weight:bold;color:#17365D">Phone</td>
                <td width="30%" style="font-size:10px;">'.$purpos->vendor->phone_no.'</td>
                <td width="20%" style="font-size:10px;font-weight:bold;color:#17365D">Remarks</td>
                <td width="30%" style="font-size:10px;">'.$purpos->remarks.'</td>
            </tr>
        </table>';
        $pdf->writeHTML($html, true, false, true, false, '');
    
       
        // Items Table Header
        $html = '<table border="0.3" style="text-align:center;margin-top:10px">
            <tr>
                <th width="6%" style="font-size:10px;font-weight:bold;color:#17365D">S/N</th>
                <th width="10%" style="font-size:10px;font-weight:bold;color:#17365D">Qty</th>
                <th width="30%" style="font-size:10px;font-weight:bold;color:#17365D">Product Name</th>
                <th width="30%" style="font-size:10px;font-weight:bold;color:#17365D">Description</th>
                <th width="12%" style="font-size:10px;font-weight:bold;color:#17365D">Rate</th>
                <th width="12%" style="font-size:10px;font-weight:bold;color:#17365D">Total</th>
            </tr>
       ';
    
        // Items Table Data
        $total_amount = 0;
        $count = 1;
    
        foreach ($purpos->details as $item) {
            $product_name = $item->category->name ?? 'N/A'; // Fetch product name safely
            $product_m_unit = $item->product->measurement_unit ?? 'N/A'; // Assuming 'measurement_unit' is the column name
            $description = $item->product->description ?? 'N/A'; // Assuming 'measurement_unit' is the column name

            $total = $item->item_rate * $item->item_qty;
            $total_amount += $total;
    
            $html .= '<tr>
                <td width="6%" style="font-size:10px;text-align:center;">'.$count.'</td>
                <td width="10%" style="font-size:10px;text-align:center;">'.$item->item_qty." ".$product_m_unit.'</td>
                <td width="30%" style="font-size:10px;text-align:center;">'.$product_name.'</td>
                <td width="30%" style="font-size:10px;">'.$description.'</td>
                <td width="12%" style="font-size:10px;text-align:center;">'.$item->item_rate.'</td>
                <td width="12%" style="font-size:10px;text-align:center;">'.$total.'</td>
            </tr>';
            $count++;
        }
    
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
    
        // Summary Table
        $net_amount = round($total_amount + $purpos->other_exp - $purpos->bill_discount);
        
        $summary = '<table border="1" cellpadding="5" width="50%">
            <tr><td><b>Total Amount:</b></td><td>'.$total_amount.'</td></tr>
            <tr><td><b>Other Expenses:</b></td><td>'.$purpos->other_exp.'</td></tr>
            <tr><td><b>Discount:</b></td><td>'.$purpos->bill_discount.'</td></tr>
            <tr><td><b>Net Amount:</b></td><td>'.$net_amount.'</td></tr>
        </table>';
        $pdf->writeHTML($summary, true, false, true, false, '');
    
        // Attachments (Images)
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Attachments:', 0, 1, 'L');
    
        foreach ($purpos->attachments as $attachment) {
            $imagePath = storage_path('app/public/' . $attachment->att_path);
            
            if (file_exists($imagePath)) {
                $pdf->Image($imagePath, '', '', 50, 50, '', '', '', false, 300, '', false, false, 0, false, false, false);
                $pdf->Ln(55); // Move cursor down after each image
            }
        }
    
        // Signature Section
        $pdf->Ln(30);
        $pdf->SetFont('helvetica', '', 12);
        
        $lineWidth = 60; // Line width in mm
        $yPosition = $pdf->GetY(); // Get current Y position for alignment
        
        // Draw lines for signatures
        $pdf->Line(30, $yPosition, 30 + $lineWidth, $yPosition); // Approved By
        $pdf->Line(130, $yPosition, 130 + $lineWidth, $yPosition); // Received By
    
        $pdf->Ln(5); // Move cursor below the line
        
        // Text below the lines
        $pdf->SetXY(30, $yPosition + 5);
        $pdf->Cell($lineWidth, 10, 'Approved By', 0, 0, 'C');
    
        $pdf->SetXY(130, $yPosition + 5);
        $pdf->Cell($lineWidth, 10, 'Received By', 0, 0, 'C');
    
        // Output the PDF
        $pdf->Output('Purchase_Order_'.$purpos->id.'.pdf', 'I');
    }
}

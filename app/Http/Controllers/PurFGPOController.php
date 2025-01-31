<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PurPo;
use App\Models\PurPosDetail;
use App\Models\ChartOfAccounts;
use App\Models\Products;
use App\Models\ProductAttributes;

use App\Services\myPDF;

class PurFGPOController extends Controller
{
    public function index()
    {
        $purpos = PurPo::with('vendor')->get();
        return view('purchasing.fg-po.index', compact('purpos'));
    }

    public function create()
    {
        $coa = ChartOfAccounts::all();  // Get all product categories
        $products = Products::all();  // Get all product categories
        $attributes = ProductAttributes::all();
        
        return view('purchasing.fg-po.create', compact( 'coa', 'products', 'attributes'));
    }

    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'vendor_name' => 'required|exists:chart_of_accounts,id', // Ensure vendor exists
                'order_date' => 'required|date',
                'delivery_date' => 'required|date',
                'details' => 'required|array',
                'details.*.item_id' => 'required|string|max:255',
                'details.*.item_rate' => 'required|numeric|min:0',
                'details.*.item_qty' => 'required|numeric|min:0',
                'other_exp' => 'nullable|numeric|min:0',
                'bill_discount' => 'nullable|numeric|min:0',
                'att.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Image validation
            ]);
    
            // Create the Purchase Order
            $purpo = PurPo::create([
                'vendor_name' => $validatedData['vendor_name'],
                'order_date' => $validatedData['order_date'],
                'delivery_date' => $validatedData['delivery_date'],
                'other_exp' => $validatedData['other_exp'] ?? 0,
                'bill_discount' => $validatedData['bill_discount'] ?? 0,
            ]);
    
            // Store Purchase Order Details
            foreach ($validatedData['details'] as $detail) {
                PurPosDetail::create([
                    'pur_pos_id' => $purpo->id, // Foreign key
                    'item_id' => $detail['item_id'],
                    'item_rate' => $detail['item_rate'],
                    'item_qty' => $detail['item_qty'],
                ]);
            }
    
            // Handle Images (if provided)
            // if ($request->hasFile('att')) {
            //     foreach ($request->file('att') as $file) {
            //         $filePath = $file->store('purchase_orders', 'public'); // Store in storage/app/public/purchase_orders
    
            //         PurPoAttachment::create([
            //             'pur_po_id' => $purpo->id,
            //             'image_path' => $filePath,
            //         ]);
            //     }
            // }
    
            return redirect()->route('pur-pos.index')->with('success', 'Purchase Order created successfully!');
    
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function print($id)
    {
        // Fetch the purchase order along with related vendor and details
        $purpos = PurPo::with(['vendor', 'details.category'])->findOrFail($id);
    
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
            $total = $item->item_rate * $item->item_qty;
            $total_amount += $total;
    
            $html .= '<tr>
                <td width="6%" style="font-size:10px;text-align:center;">'.$count.'</td>
                <td width="10%" style="font-size:10px;text-align:center;">'.$item->item_qty.'</td>
                <td width="30%" style="font-size:10px;text-align:center;">'.$product_name.'</td>
                <td width="30%" style="font-size:10px;"></td>
                <td width="12%" style="font-size:10px;text-align:center;">'.$item->item_rate.'</td>
                <td width="12%" style="font-size:10px;text-align:center;">'.$total.'</td>
            </tr>';
            $count++;
        }
    
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
    
        // Summary Table
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(23, 54, 93);
    
        $pdf->SetXY(120, $pdf->GetY() + 10);
        $pdf->Cell(45, 5, 'Total Amount', 1,1);
        $pdf->SetXY(120, $pdf->GetY());
        $pdf->Cell(45, 5, 'Other Expenses', 1,1);
        $pdf->SetXY(120, $pdf->GetY());
        $pdf->Cell(45, 5, 'Discount', 1,1);
        $pdf->SetXY(120, $pdf->GetY());
        $pdf->Cell(45, 5, 'Net Amount', 1, 1);
    
        $pdf->SetFont('helvetica','', 10);
        $pdf->SetTextColor(0, 0, 0);
    
        $net_amount = round($total_amount + $purpos->other_exp - $purpos->bill_discount);
        $num_to_words = $pdf->convertCurrencyToWords($net_amount);
    
        $pdf->SetXY(165, $pdf->GetY() - 27.26);
        $pdf->Cell(35, 5, $total_amount, 1, 'R');
        $pdf->SetXY(165, $pdf->GetY());
        $pdf->Cell(35, 5, $purpos->other_exp, 1, 'R');
        $pdf->SetXY(165, $pdf->GetY());
        $pdf->Cell(35, 5, $purpos->bill_discount, 1, 'R');
        $pdf->SetXY(165, $pdf->GetY());
        $pdf->SetFont('helvetica','B');
        $pdf->Cell(35, 5, $net_amount, 1, 'R');
    
        // Terms & Conditions
        $pdf->SetFont('helvetica','BIU', 14);
        $pdf->SetTextColor(23, 54, 93);
        $pdf->SetXY(10, $pdf->GetY() + 15);
        // $pdf->Cell(35, 5, 'Terms & Conditions:', 0, 'L');
    
        $pdf->SetFont('helvetica','', 11);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->MultiCell(185, 10, $purpos->tc, 0, 'L', 0, 1);
    
        $pdf->Output('Purchase_Order_'.$purpos->id.'.pdf', 'I');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PurPosDetail;
use App\Models\ProductCategory;
use App\Models\ChartOfAccounts;
use App\Models\Products;
use App\Models\PurPoAttachment;
use App\Models\PurPo;
use App\Services\myPDF;


class PurPOController extends Controller
{

    public function index()
    {
        $purpos = PurPo::with('vendor')->get();
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
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'vendor_name' => 'required|exists:chart_of_accounts,id', // Ensure vendor exists
                'order_date' => 'required|date',
                'delivery_date' => 'date',
                'details' => 'required|array',
                'details.*.item_id' => 'required|string|max:255',
                'details.*.item_rate' => 'required|numeric|min:0',
                'details.*.item_qty' => 'required|numeric|min:0',
                'other_exp' => 'nullable|numeric|min:0',
                'bill_discount' => 'nullable|numeric|min:0',
                'att.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Image validation
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
            if ($request->hasFile('att')) {
                foreach ($request->file('att') as $file) {
                    $filePath = $file->store('purchase_orders', 'public'); // Store in storage/app/public/purchase_orders
    
                    PurPoAttachment::create([
                        'pur_po_id' => $purpo->id,
                        'att_path' => $filePath,
                    ]);
                }
            }
    
            return redirect()->route('pur-pos.index')->with('success', 'Purchase Order created successfully!');
    
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
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
    
    public function print($id)
    {
        // Fetch the purchase order with related data
        $purpos = PurPo::with(['vendor', 'details.category', 'attachments','details.product'])->findOrFail($id);
        
        dd(print_r($purpos->attachments));
        foreach ($purpos->attachments as $attachment) {
            $imagePath = storage_path('app/public/' . $attachment->att_path);
            dd($imagePath);
            if (file_exists($imagePath)) {
                $pdf->Image($imagePath, '', '', 50, 50, '', '', '', false, 300, '', false, false, 0, false, false, false);
                $pdf->Ln(55); // Move cursor down after each image
            }
        }

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

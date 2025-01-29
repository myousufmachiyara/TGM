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
    
            return redirect()->route('purpos.index')->with('success', 'Purchase Order created successfully!');
    
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
    
    public function print(){
        $purchase = po::where('pur_id',$id)
        ->join('ac','po.ac_cod','=','ac.ac_code')
        ->first();

        $purchase_items = po_2::where('pur_cod',$id)
                ->join('item_entry','po_2.item_cod','=','item_entry.it_cod')
                ->get();

        $pdf = new MyPDF();

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TGM');
        $pdf->SetTitle('Purchase Order-'.$purchase['prefix'].$purchase['pur_id']);
        $pdf->SetSubject('Purchase Order-'.$purchase['prefix'].$purchase['pur_id']);
        $pdf->SetKeywords('Purchase Order, TCPDF, PDF');
                   
        // Add a page
        $pdf->AddPage();
           
        $pdf->setCellPadding(1.2); // Set padding for all cells in the table

        // margin top
        $margin_top = '.margin-top {
            margin-top: 10px;
        }';
        // $pdf->writeHTML('<style>' . $margin_top . '</style>', true, false, true, false, '');

        // margin bottom
        $margin_bottom = '.margin-bottom {
            margin-bottom: 4px;
        }';

        // $pdf->writeHTML('<style>' . $margin_bottom . '</style>', true, false, true, false, '');

        $heading='<h1 style="font-size:20px;text-align:center;font-style:italic;text-decoration:underline;color:#17365D">Purchase Order</h1>';
        $pdf->writeHTML($heading, true, false, true, false, '');
        $pdf->writeHTML('<style>' . $margin_bottom . '</style>', true, false, true, false, '');

        $html = '<table style="margin-bottom:1rem">';
        $html .= '<tr>';
        $html .= '<td style="font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">PO No: &nbsp;<span style="text-decoration: underline;color:#000">'.$purchase['prefix'].$purchase['pur_id'].'</span></td>';
        $html .= '<td style="font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">Date: &nbsp;<span style="color:#000">'.\Carbon\Carbon::parse($purchase['pur_date'])->format('d-m-y').'</span></td>';
        $html .= '<td style="font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">Qoutation No: <span style="text-decoration: underline;color:#000">'.$purchase['pur_bill_no'].'</span></td>';
        // $html .= '<td style="font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">Login: &nbsp; <span style="text-decoration: underline;color:#000">Hamza</span></td>';
        $html .= '</tr>';
        $html .= '</table>';

        // $pdf->writeHTML($html, true, false, true, false, '');

        $html .= '<table border="0.1px" style="border-collapse: collapse;">';
        $html .= '<tr>';
        $html .= '<td width="20%" style="font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">Account Name </td>';
        $html .= '<td width="30%" style="font-size:10px;font-family:poppins;">'.$purchase['ac_name'].'</td>';
        $html .= '<td width="20%" width="20%" style="font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">Name Of Person</td>';
        $html .= '<td width="30%" style="font-size:10px;font-family:poppins;">'.$purchase['cash_saler_name'].'</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td width="20%" width="20%" style="font-size:10px;font-weight:bold;font-family:poppins;color:#17365D" >Address </td>';
        $html .= '<td width="30%" style="font-size:10px;font-family:poppins;">'.$purchase['address'].'</td>';
        $html .= '<td width="20%" style="font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">Persons Address</td>';
        $html .= '<td width="30%" style="font-size:10px;font-family:poppins;">'.$purchase['cash_saler_address'].'</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td width="20%" style="font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">Phone </td>';
        $html .= '<td width="30%" style="font-size:10px;font-family:poppins;">'.$purchase['phone_no'].'</td>';
        $html .= '<td width="20%" style="font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">Persons Phone</td>';
        $html .= '<td width="30%" style="font-size:10px;font-family:poppins;">'.$purchase['cash_pur_phone'].'</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">Remarks </td>';
        $html .= '<td width="80%" style="font-size:10px;font-family:poppins;">'.$purchase['pur_remarks'].'</td>';
        $html .= '</tr>';
        $html .= '</table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
    
        $html = '<table border="0.3" style="text-align:center;margin-top:10px" >';
        $html .= '<tr>';
        $html .= '<th style="width:6%;font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">S/R</th>';
        $html .= '<th style="width:8%;font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">Qty</th>';
        $html .= '<th style="width:26%;font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">Item Name</th>';
        $html .= '<th style="width:24%;font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">Description</th>';
        $html .= '<th style="width:12%;font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">Price</th>';
        $html .= '<th style="width:12%;font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">Weight</th>';
        $html .= '<th style="width:12%;font-size:10px;font-weight:bold;font-family:poppins;color:#17365D">Amount</th>';
        $html .= '</tr>';
        $html .= '</table>';

        $pdf->setTableHtml($html);

        $count=1;
        $total_weight=0;
        $total_quantity=0;
        $total_amount=0;
        $net_amount=0;

        $html .= '<table cellspacing="0" cellpadding="5">';
        foreach ($purchase_items as $items) {
            if($count%2==0)
            {
                $html .= '<tr style="background-color:#f1f1f1">';
                $html .= '<td style="width:6%;border-right:0.3px dashed #000;border-left:0.3px dashed #000;font-size:10px;font-family:poppins;text-align:center;">'.$count.'</td>';
                $html .= '<td style="width:8%;border-right:0.3px dashed #000;font-size:10px;font-family:poppins;text-align:center">'.$items['pur_qty2'].'</td>';
                $total_quantity=$total_quantity+$items['pur_qty2'];
                $html .= '<td style="width:26%;border-right:0.3px dashed #000;font-size:10px;font-family:poppins;">'.$items['item_name'].'</td>';
                $html .= '<td style="width:24%;border-right:0.3px dashed #000;font-size:10px;font-family:poppins;">'.$items['remarks'].'</td>';
                $html .= '<td style="width:12%;border-right:0.3px dashed #000;font-size:10px;font-family:poppins;text-align:center">'.$items['pur_price'].'</td>';
                $html .= '<td style="width:12%;border-right:0.3px dashed #000;font-size:10px;font-family:poppins;text-align:center">'.$items['pur_qty'].'</td>';
                $total_weight=$total_weight+$items['pur_qty'];
                $html .= '<td style="width:12%;border-right:0.3px dashed #000;font-size:10px;font-family:poppins;">'.$items['pur_qty']*$items['pur_price'].'</td>';
                $total_amount=$total_amount+($items['pur_qty']*$items['pur_price']);
                $html .= '</tr>';
            }
            else{
                $html .= '<tr>';
                $html .= '<td style="width:6%;border-right:0.3px dashed #000;border-left:0.3px dashed #000;font-size:10px;font-family:poppins;text-align:center">'.$count.'</td>';
                $html .= '<td style="width:8%;border-right:0.3px dashed #000;font-size:10px;font-family:poppins;text-align:center">'.$items['pur_qty2'].'</td>';
                $total_quantity=$total_quantity+$items['pur_qty2'];
                $html .= '<td style="width:26%;border-right:0.3px dashed #000;font-size:10px;font-family:poppins;">'.$items['item_name'].'</td>';
                $html .= '<td style="width:24%;border-right:0.3px dashed #000;font-size:10px;font-family:poppins;">'.$items['remarks'].'</td>';
                $html .= '<td style="width:12%;border-right:0.3px dashed #000;font-size:10px;font-family:poppins;text-align:center">'.$items['pur_price'].'</td>';
                $html .= '<td style="width:12%;border-right:0.3px dashed #000;font-size:10px;font-family:poppins;text-align:center">'.$items['pur_qty'].'</td>';
                $total_weight=$total_weight+$items['pur_qty'];
                $html .= '<td style="width:12%;border-right:0.3px dashed #000;font-size:10px;font-family:poppins;">'.$items['pur_qty']*$items['pur_price'].'</td>';
                $total_amount=$total_amount+($items['pur_qty']*$items['pur_price']);
                $html .= '</tr>';
            }
            $count++;
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        $currentY = $pdf->GetY();
        
        if(($pdf->getPageHeight()-$pdf->GetY())<57){
            $pdf->AddPage();
            $currentY = $pdf->GetY()+15;
        }

        $pdf->SetFont('helvetica','B', 10);
        $pdf->SetTextColor(23, 54, 93);

        $pdf->SetXY(10, $currentY);
        $pdf->Cell(40, 5, 'Total Weight(kg)', 1,1);
        $pdf->Cell(40, 5, 'Total Quantity', 1,1);

        // // Column 2
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(50, $currentY);
        $pdf->Cell(42, 5,  $total_weight, 1, 'R');
        $pdf->SetXY(50, $currentY+6.8);
        $pdf->SetFont('helvetica','', 10);

        $pdf->Cell(42, 5, $total_quantity, 1,'R');

        $roundedTotal= round($total_amount+$purchase['pur_labor_char']+$purchase['pur_convance_char']-$purchase['pur_discount']);
        $num_to_words=$pdf->convertCurrencyToWords($roundedTotal);
       

        // Column 3
        $pdf->SetFont('helvetica','B', 10);
        $pdf->SetTextColor(23, 54, 93);

        $pdf->SetXY(120, $currentY);
        $pdf->Cell(45, 5, 'Total Amount', 1,1);
        $pdf->SetXY(120, $currentY+6.8);
        $pdf->Cell(45, 5, 'Labour Charges', 1,1);
        $pdf->SetXY(120, $currentY+13.7);
        $pdf->Cell(45, 5, 'Convance Charges', 1,1);
        $pdf->SetXY(120, $currentY+20.5);
        $pdf->Cell(45, 5, 'Discount(Rs)', 1,1);
       
        // Change font size to 11 for "Net Amount"
        $pdf->SetFont('helvetica', 'B', 12);  
        $pdf->SetXY(120, $currentY+27.3);
        $pdf->Cell(45, 5, 'Net Amount', 1, 1);
        
        // // Column 4
        $pdf->SetFont('helvetica','', 10);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetXY(165, $currentY);
        $pdf->Cell(35, 5, $total_amount, 1, 'R');
        $pdf->SetXY(165, $currentY+6.8);
        $pdf->Cell(35, 5, $purchase['pur_labor_char'], 1, 'R');
        $pdf->SetXY(165, $currentY+13.7);
        $pdf->Cell(35, 5, $purchase['pur_convance_char'], 1, 'R');
        $pdf->SetXY(165, $currentY+20.5);
        $pdf->Cell(35, 5, $purchase['pur_discount'], 1, 'R');
        $pdf->SetXY(165, $currentY+27.3);
        $net_amount=number_format(round($total_amount+$purchase['pur_labor_char']+$purchase['pur_convance_char']-$purchase['pur_discount']));
        $pdf->SetFont('helvetica','B', 12);
        $pdf->Cell(35, 5,  $net_amount, 1, 'R');
        
        $pdf->SetFont('helvetica','BIU', 14);
        $pdf->SetTextColor(23, 54, 93);

        $pdf->SetXY(10, $currentY+20);
        $width = 100;
        $pdf->MultiCell($width, 10, $num_to_words, 0, 'L', 0, 1, '', '', true);
        $pdf->SetFont('helvetica','', 10);
        

        // terms and condition starts here
        $currentY = $pdf->GetY();

        $pdf->SetFont('helvetica','BIU', 14);
        $pdf->SetTextColor(23, 54, 93);

        $pdf->SetXY(10, $currentY+10);
        $pdf->Cell(35, 5,  'Terms & Conditions:' , 0, 'L');

        $pdf->SetFont('helvetica','', 11);
        $pdf->SetTextColor(255, 0, 0);

        $width = 185;
        $pdf->MultiCell($width, 10, $purchase['tc'], 0, 'L', 0, 1, '', '', true);

        // terms and condition ends here

        // Close and output PDF
        $pdf->Output('Purchase Order_'.$purchase['prefix'].$purchase['pur_id'].'.pdf', 'I');
    }
}

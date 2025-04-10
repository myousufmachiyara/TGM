@extends('layouts.app')

@section('title', 'Purchasing | Edit PO')

@section('content')
  <div class="row">
      <div class="col">
        <form action="{{ route('pur-pos.update', $purPo->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT') <!-- Since this is an update operation, we need the PUT method -->
            <section class="card">
              <header class="card-header">
                <div style="display: flex;justify-content: space-between;">
                    <h2 class="card-title">Edit PO</h2>
                </div>
                @if ($errors->has('error'))
                    <strong class="text-danger">{{ $errors->first('error') }}</strong>
                @endif
              </header>
                <div class="card-body">
                    <div class="row pb-2">

                        <div class="col-12 col-md-1">
                            <label>PO #</label>
                            <input type="number" class="form-control" placeholder="PO #" value="{{ $purPo->po_code }}" disabled />
                        </div>

                        <div class="col-12 col-md-2">
                            <label>Category<span style="color: red;"><strong>*</strong></span></label>
                            <select data-plugin-selecttwo class="form-control select2-js" name="category_id" required>
                                <option value="" selected disabled>Select Category</option>
                                @foreach ($prodCat as $cat)
                                    <option value="{{ $cat->id }}" {{ $purPo->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option> 
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-2">
                            <label>Vendor Name</label>
                            <select data-plugin-selecttwo class="form-control select2-js" name="vendor_id" required>
                                <option value="" selected disabled>Select Vendor</option>
                                @foreach ($coa as $item)
                                    <option value="{{ $item->id }}" {{ $purPo->vendor_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option> 
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-2">
                            <label>Order Date</label>
                            <input type="date" name="order_date" class="form-control" value="{{ $purPo->order_date->format('Y-m-d') }}" required/>
                        </div>

                        <div class="col-12 col-md-2">
                            <label>Delivery Date</label>
                            <input type="date" name="delivery_date" class="form-control" value="{{ $purPo->delivery_date ? $purPo->delivery_date->format('Y-m-d') : '' }}" />
                        </div>
                        
                        <div class="col-12 col-md-3">
                            <label>Attachments</label>
                            <input type="file" class="form-control" name="att[]" multiple accept="image/png, image/jpeg, image/jpg, image/webp">
                            @foreach ($purPo->attachments as $attachment)
                                <p>{{ basename($attachment->att_path) }}</p>
                            @endforeach
                        </div>

                    </div>
                </div>
            </section>

            <section class="card">
                <header class="card-header">
                    <div style="display: flex;justify-content: space-between;">
                        <h2 class="card-title">Item Details</h2>
                    </div>
                    @if ($errors->has('error'))
                        <strong class="text-danger">{{ $errors->first('error') }}</strong>
                    @endif
                </header>
                <div class="card-body" style="max-height:400px; overflow-y:auto">
                    <table class="table table-bordered" id="myTable">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Rate</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="PurPOTbleBody">
                          @foreach ($purPo->details as $index => $detail)
                            <tr>
                                <td>
                                    <select data-plugin-selecttwo class="form-control select2-js" name="details[{{ $index }}][item_id]" onchange="updateUnit({{ $index }})" required>
                                        <option value="" disabled>Select Item</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" {{ $detail->item_id == $product->id ? 'selected' : '' }}>{{ $product->sku }} - {{ $product->name }}</option>
                                        @endforeach
                                    </select>  
                                </td>
                                <td><input type="number" name="details[{{ $index }}][item_rate]" step="any" value="{{ $detail->item_rate }}" class="form-control" onchange="rowTotal({{ $index }})" required/></td>
                                <td><input type="number" name="details[{{ $index }}][item_qty]" step="any" value="{{ $detail->item_qty }}" class="form-control" onchange="rowTotal({{ $index }})" required/></td>
                                <td>
                                    <input type="text" id="unitSuffix{{ $index }}" class="form-control" value="{{ $detail->product->measurement_unit ?? 'N/A' }}" disabled/>
                                </td>
                                <td><input type="number" id="item_total{{ $index }}" class="form-control" value="{{ $detail->item_rate * $detail->item_qty }}" disabled/></td>
                                <td>
                                  <button type="button" class="btn btn-primary" onclick="addNewRow()" ><i class="fa fa-plus"></i></button>
                                  <button type="button" onclick="removeRow(this)" class="btn btn-danger"><i class="fas fa-times"></i></button>
                                </td>
                            </tr>
                          @endforeach
                        </tbody>
                    </table>
                </div>

                <footer class="card-footer">
                    <div class="row">
                        <div class="col-12 col-md-2">
                            <label>Total Quantity</label>
                            <input type="number" class="form-control" id="total_qty" placeholder="Total Quantity" disabled/>
                        </div>
                        <div class="col-12 col-md-2">
                            <label>Total Amount</label>
                            <input type="number" class="form-control" id="total_amt" placeholder="Total Amount" disabled />
                        </div>
                        <div class="col-12 col-md-2">
                            <label>Other Expenses</label>
                            <input type="number" class="form-control" name="other_exp" id="other_exp" onchange="netTotal()" value="{{ $purPo->other_exp }}" placeholder="Other Expenses" />
                        </div>
                        <div class="col-12 col-md-2">
                            <label>Bill Discount</label>
                            <input type="number" class="form-control" name="bill_discount" id="bill_disc" onchange="netTotal()" value="{{ $purPo->bill_discount }}" placeholder="Bill Discount"  />
                        </div>
                        <div class="col-12 pb-sm-3 pb-md-0 text-end">
                            <h3 class="font-weight-bold mt-3 mb-0 text-5 text-primary">Net Amount</h3>
                            <span>
                                <strong class="text-4 text-primary">PKR <span id="netTotal" class="text-4 text-danger">{{ number_format($purPo->net_amount, 2) }}</span></strong>
                            </span>
                        </div>
                    </div>
                </footer>

                <footer class="card-footer text-end">
                    <a class="btn btn-danger" href="{{ route('pur-pos.index') }}">Discard</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </footer>
            </section>
        </form>
      </div>
  </div>
  <script>

    var index = {{ count($purPo->details) + 1 }}; // Adjust for new rows after initial details

    function removeRow(button) {
      var tableRows = $("#PurPOTbleBody tr").length;
      if(tableRows>1){
        var row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
        index--;	
      } 
      tableTotal();
    }

    function addNewRow(){
      var lastRow =  $('#PurPOTbleBody tr:last');
      latestValue=lastRow[0].cells[0].querySelector('select').value;

      if(latestValue!=""){
        var table = document.getElementById('myTable').getElementsByTagName('tbody')[0];
        var newRow = table.insertRow(table.rows.length);

        var cell1 = newRow.insertCell(0);
        var cell2 = newRow.insertCell(1);
        var cell3 = newRow.insertCell(2);
        var cell4 = newRow.insertCell(3);
        var cell5 = newRow.insertCell(4);
        var cell6 = newRow.insertCell(5);

        cell1.innerHTML  = '<select data-plugin-selecttwo id="productSelect'+index+'" class="form-control select2-js" onchange="updateUnit('+index+')" name="details['+index+'][item_id]">'+
                            '<option value="" disabled selected>Select Category</option>'+
                            @foreach ($products as $item)
                              '<option value="{{ $item->id }}" data-unit="{{ $item->measurement_unit }}">{{$item->sku }} - {{$item->name }}</option>'+
                            @endforeach
                          '</select>';
        cell2.innerHTML  = '<input type="number" name="details['+index+'][item_rate]" step="any" id="item_rate'+index+'"  onchange="rowTotal('+index+')" class="form-control" placeholder="Rate" required/>';
        cell3.innerHTML  = '<input type="number" name="details['+index+'][item_qty]" step="any" id="item_qty'+index+'"  onchange="rowTotal('+index+')" class="form-control" placeholder="Quantity" required/>';
        cell4.innerHTML  = '<input type="text" id="unitSuffix'+index+'" class="form-control" placeholder="Quantity" disabled  />';
        cell5.innerHTML  = '<input type="number" id="item_total'+index+'" class="form-control" placeholder="Total" disabled/>';
        cell6.innerHTML  = '<button type="button" onclick="removeRow(this)" class="btn btn-danger" tabindex="1"><i class="fas fa-times"></i></button> '+
                          '<button type="button" class="btn btn-primary" onclick="addNewRow()" ><i class="fa fa-plus"></i></button>';
        index++;

        rowTotal(index-1); // Handle the new row total immediately.
      }
    }

    function updateUnit(index){
      const selectedOption = document.getElementById('productSelect'+index).selectedOptions[0];
      const unit = selectedOption.getAttribute('data-unit');
      document.getElementById('unitSuffix'+index).value = unit;
    }

    function rowTotal(index) {
      let rate = parseFloat(document.getElementById("item_rate"+index).value);
      let qty = parseFloat(document.getElementById("item_qty"+index).value);
      let total = (rate * qty).toFixed(2);
      document.getElementById("item_total"+index).value = total;
      tableTotal();
    }

    function tableTotal() {
      var totalQty = 0, totalAmount = 0;
      $("#PurPOTbleBody tr").each(function() {
        var qty = parseFloat($(this).find('input[name$="[item_qty]"]').val()) || 0;
        var amount = parseFloat($(this).find('input[name$="[item_rate]"]').val()) || 0;
        totalQty += qty;
        totalAmount += qty * amount;
      });
      $("#total_qty").val(totalQty);
      $("#total_amt").val(totalAmount.toFixed(2));
      netTotal();
    }

    function netTotal() {
      let totalAmount = parseFloat($("#total_amt").val()) || 0;
      let otherExpenses = parseFloat($("#other_exp").val()) || 0;
      let discount = parseFloat($("#bill_disc").val()) || 0;
      let net = totalAmount + otherExpenses - discount;
      $("#netTotal").text(net.toFixed(2));
    }

  </script>
@endsection

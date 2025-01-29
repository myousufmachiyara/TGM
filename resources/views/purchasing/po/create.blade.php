@extends('layouts.app')

@section('title', 'Purchasing | New PO')

@section('content')
    <div class="row">
      <div class="col">
      <form action="{{ route('purpos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <section class="card">
          <header class="card-header">
						<div style="display: flex;justify-content: space-between;">
              <h2 class="card-title">New PO</h2>
              <button type="button" class="btn btn-primary" onclick="addNewRow_btn()"> <i class="fas fa-plus"></i> Add New Row </button>
						</div>
						@if ($errors->has('error'))
							<strong class="text-danger">{{ $errors->first('error') }}</strong>
						@endif
					</header>
          <div class="card-body">
            <div class="row">
              <div class="col-12 col-md-3">
                <label>Vendor Name</label>
                <select data-plugin-selecttwo class="form-control select2-js" name="vendor_name" required>  <!-- Added name attribute for form submission -->
                  <option value="" selected disabled>Select Vendor</option>
                  @foreach ($coa as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option> 
                  @endforeach
                </select>
              </div>
              <div class="col-12 col-md-3">
                <label>Order Date</label>
                <input type="date" name="order_date" class="form-control" value="<?php echo date('Y-m-d'); ?>"   placeholder="Order Date" required/>
              </div>
              <div class="col-12 col-md-3">
                <label>Delivery Date</label>
                <input type="date" name="delivery_date" class="form-control" placeholder="Delivery Date" required/>
              </div>
             
              <div class="col-12 col-md-3">
                <label>Attachements</label>
                <input type="file" class="form-control" name="att[]" multiple accept="image/png, image/jpeg, image/jpg">
              </div>

            </div>
          </div>

          <div class="card-body" style="max-height:400px; overflow-y:auto">
            <div class="card-title mb-3">Item Details</div>
            <table class="table table-bordered" id="myTable">
              <thead>
                <tr>
                  <th>Item Name</th>
                  <th>Rate</th>
                  <th>Quantity</th>
                  <th>Total</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="PurPOTbleBody">
                <tr>
                  <td>
                    <select data-plugin-selecttwo class="form-control select2-js" name="details[0][item_id]" required>  <!-- Added name attribute for form submission -->
                      <option value="" selected disabled>Select Item</option>
                      @foreach ($products as $item)
                        <option value="{{ $item->id }}">{{$item->sku }} - {{$item->name }}</option> 
                      @endforeach
                    </select>  
                  </td>
                  <td><input type="number" name="details[0][item_rate]"  id="item_rate1" onchange="rowTotal(1)" step="any" class="form-control" placeholder="Rate" required/></td>
                  <td><input type="number" name="details[0][item_qty]"   id="item_qty1" onchange="rowTotal(1)" step="any" class="form-control" placeholder="Quantity" required/></td>
                  <td><input type="number" id="item_total1" class="form-control" placeholder="Total" disabled/></td>
                  <td>
										<button type="button" onclick="removeRow(this)" class="btn btn-danger" tabindex="1"><i class="fas fa-times"></i></button>
                    <button type="button" class="btn btn-primary" onclick="addNewRow()" ><i class="fa fa-plus"></i></button></td>
                </tr>
              </tbody>
            </table>
          </div>

          <footer class="card-footer">
            <div class="card-title mb-3">Summary</div>
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
                <input type="number" class="form-control" name="other_exp" id="other_exp" onchange="netTotal()" value=0 placeholder="Other Expenses" />
              </div>
              <div class="col-12 col-md-2">
                <label>Bill Discount</label>
                <input type="number" class="form-control" name="bill_discount" id="bill_disc" onchange="netTotal()" value=0 placeholder="Bill Discount"  />
              </div>
              <div class="col-12 pb-sm-3 pb-md-0 text-end">
                <h3 class="font-weight-bold mt-3 mb-0 text-5 text-primary">Net Amount</h3>
                <span>
                  <strong class="text-4 text-primary">PKR <span id="netTotal" class="text-4 text-danger">0.00 </span></strong>
                </span>
              </div>
            </div>
          </footer>
          <footer class="card-footer text-end">
            <a class="btn btn-danger" href="{{ route('purpos.index') }}" >Discard</a>
            <button type="submit" class="btn btn-primary">Create</button>
          </footer>
        </section>
      </form>
    </div>
  </div>
  <script>

    var index=2;

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

        cell1.innerHTML  = '<select data-plugin-selecttwo class="form-control select2-js" name="details['+index+'][item_name]">'+
                            '<option value="" disabled selected>Select Category</option>'+
                            @foreach ($products as $item)
                              '<option value="{{ $item->id }}">{{$item->sku }} - {{$item->name }}</option>'+
                            @endforeach
                          '</select>';
        cell2.innerHTML  = '<input type="number" name="details['+index+'][item_rate]" step="any" id="item_rate'+index+'"  onchange="rowTotal('+index+')" class="form-control" placeholder="Rate" required/>';
        cell3.innerHTML  = '<input type="number" name="details['+index+'][item_qty]" step="any" id="item_qty'+index+'"  onchange="rowTotal('+index+')" class="form-control" placeholder="Quantity" required/>';
        cell4.innerHTML  = '<input type="number" id="item_total'+index+'" class="form-control" placeholder="Total" disabled/>';
        cell5.innerHTML  = '<button type="button" onclick="removeRow(this)" class="btn btn-danger" tabindex="1"><i class="fas fa-times"></i></button> '+
                          '<button type="button" class="btn btn-primary" onclick="addNewRow()" ><i class="fa fa-plus"></i></button>';
        index++;
        tableTotal();
      }
      $('#myTable select[data-plugin-selecttwo]').select2();

    }

    function rowTotal(index){
      var item_rate = parseFloat($('#item_rate'+index+'').val());
      var item_qty = parseFloat($('#item_qty'+index+'').val());   
      var item_total = item_rate * item_qty;

      $('#item_total'+index+'').val(item_total.toFixed());
      
      tableTotal();
    }

    function tableTotal(){
      var totalQuantity=0;
      var totalAmount=0;
      var tableRows = $("#PurPOTbleBody tr").length;
      var table = document.getElementById('myTable').getElementsByTagName('tbody')[0];

      for (var i = 0; i < tableRows; i++) {
        var currentRow =  table.rows[i];
        totalQuantity = totalQuantity + Number(currentRow.cells[2].querySelector('input').value);
        totalAmount = totalAmount + Number(currentRow.cells[3].querySelector('input').value);
      }

      $('#total_qty').val(totalQuantity);
      $('#total_amt').val(totalAmount.toFixed());

      netTotal();
    }

    function netTotal(){
      var netTotal = 0;
      var total = Number($('#total_amt').val());
      var other_exp = Number($('#other_exp').val());
      var bill_discount = Number($('#bill_disc').val());

      netTotal = total + other_exp - bill_discount;
      netTotal = netTotal.toFixed(0);
      FormattednetTotal = formatNumberWithCommas(netTotal);
      document.getElementById("netTotal").innerHTML = '<span class="text-4 text-danger">'+FormattednetTotal+'</span>';
      $('#net_amount').val(netTotal);
    }
    function formatNumberWithCommas(number) {
      // Convert number to string and add commas
      return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

  </script>
@endsection
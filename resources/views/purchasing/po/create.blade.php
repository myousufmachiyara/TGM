@extends('layouts.app')

@section('title', 'Purchasing | New PO')

@section('content')
    <div class="row">
      <div class="col">
      <form action="{{ route('pur-pos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <section class="card">
          <header class="card-header">
						<div style="display: flex;justify-content: space-between;">
              <h2 class="card-title">New PO</h2>
						</div>
						@if ($errors->has('error'))
							<strong class="text-danger">{{ $errors->first('error') }}</strong>
						@endif
					</header>
          <div class="card-body">
            <div class="row pb-2">

              <div class="col-12 col-md-1">
                <label>PO #</label>
                <input type="number" class="form-control" placeholder="PO #" disabled />
              </div>

              <div class="col-12 col-md-2">
                <label>Category<span style="color: red;"><strong>*</strong></span></label>
                <select data-plugin-selecttwo class="form-control select2-js" name="category_id" required>  <!-- Added name attribute for form submission -->
                  <option value="" selected disabled>Select Category</option>
                  @foreach ($prodCat as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option> 
                  @endforeach
                </select>
              </div>

              <div class="col-12 col-md-2">
                <label>Vendor Name <span style="color: red;"><strong>*</strong></span></label>
                <select data-plugin-selecttwo class="form-control select2-js" name="vendor_id" required>  <!-- Added name attribute for form submission -->
                  <option value="" selected disabled>Select Vendor</option>
                  @foreach ($vendors as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option> 
                  @endforeach
                </select>
              </div>

              <div class="col-12 col-md-2">
                <label>Order Date <span style="color: red;"><strong>*</strong></span></label>
                <input type="date" name="order_date" class="form-control" value="<?php echo date('Y-m-d'); ?>"   placeholder="Order Date" required/>
              </div>
             <div class="col-12 col-md-2">
                <label>Order By <span style="color: red;"><strong>*</strong></span></label>
                <input type="text" class="form-control" name="order_by" placeholder="Order By" required/>
              </div>
              <div class="col-12 col-md-3">
                <label>Attachements</label>
                <input type="file" class="form-control" name="att[]" multiple accept="image/png, image/jpeg, image/jpg, image/webp">
              </div>
              <div class="col-12 col-md-3 mt-3">
                <label>Remarks</label>
                <textarea class="form-control" name="remarks"></textarea>
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
                  <th width="2%">Item Name</th>
                  <th>Width</th>
                  <th>Description</th>
                  <th>Rate</th>
                  <th>Quantity</th>
                  <th>Unit</th>
                  <th>Total</th>
                  <th ></th>
                </tr>
              </thead>
              <tbody id="PurPOTbleBody">
                <tr>
                  <td width="25%">
                    <select data-plugin-selecttwo class="form-control select2-js" id="productSelect1" onchange="updateUnit(1)" name="details[0][item_id]" required>  <!-- Added name attribute for form submission -->
                      <option value="" selected disabled>Select Item</option>
                      @foreach ($products as $item)
                        <option value="{{ $item->id }}" data-unit="{{ $item->measurement_unit }}">{{$item->sku }} - {{$item->name }}</option> 
                      @endforeach
                    </select>  
                  </td>
                  <td><input type="number" name="details[0][width]"  id="item_width1" step="any" class="form-control" placeholder="Width" required/></td>
                  <td><input type="text" name="details[0][description]"  id="item_description1" class="form-control" placeholder="Description"/></td>
                  <td><input type="number" name="details[0][item_rate]"  id="item_rate1" onchange="rowTotal(1)" step="any" value="0" class="form-control" placeholder="Rate" required/></td>
                  <td>
                    <input type="number" name="details[0][item_qty]" id="item_qty1" onchange="rowTotal(1)" step="any" class="form-control" placeholder="Quantity" required/>
                  </td>
                  <td>
                    <input type="text" id="unitSuffix1" class="form-control" placeholder="unit" disabled/>
                  </td>
                  <td><input type="number" id="item_total1" class="form-control" placeholder="Total" disabled/></td>
                  <td width="7%">
										<button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm" tabindex="1"><i class="fas fa-times"></i></button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addNewRow()" ><i class="fa fa-plus"></i></button></td>
                </tr>
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
              <div class="col-12 pb-sm-3 pb-md-0 text-end">
                <h3 class="font-weight-bold mt-3 mb-0 text-5 text-primary">Net Amount</h3>
                <span>
                  <strong class="text-4 text-primary">PKR <span id="netTotal" class="text-4 text-danger">0.00 </span></strong>
                </span>
              </div>
            </div>
          </footer>
          <footer class="card-footer text-end">
            <a class="btn btn-danger" href="{{ route('pur-pos.index') }}" >Discard</a>
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
        var cell6 = newRow.insertCell(5);
        var cell7 = newRow.insertCell(6);
        var cell8 = newRow.insertCell(7);

        cell1.innerHTML  = '<select data-plugin-selecttwo id="productSelect'+index+'" class="form-control select2-js" onchange="updateUnit('+index+')" name="details['+index+'][item_id]">'+
                            '<option value="" disabled selected>Select Category</option>'+
                            @foreach ($products as $item)
                              '<option value="{{ $item->id }}" data-unit="{{ $item->measurement_unit }}">{{$item->sku }} - {{$item->name }}</option>'+
                            @endforeach
                          '</select>';
        cell2.innerHTML  = '<input type="number" name="details['+index+'][width]" step="any" id="item_width'+index+'" class="form-control" placeholder="Width" required/>';
        cell3.innerHTML  = '<input type="text" name="details['+index+'][description]" id="item_description'+index+'" class="form-control" placeholder="Description"/>';
        cell4.innerHTML  = '<input type="number" name="details['+index+'][item_rate]" step="any" id="item_rate'+index+'" onchange="rowTotal('+index+')" class="form-control" placeholder="Rate" required/>';
        cell5.innerHTML  = '<input type="number" name="details['+index+'][item_qty]" step="any" id="item_qty'+index+'"  onchange="rowTotal('+index+')" class="form-control" placeholder="Quantity" required/>';
        cell6.innerHTML  = '<input type="text" id="unitSuffix'+index+'" class="form-control" placeholder="Quantity" disabled  />';
        cell7.innerHTML  = '<input type="number" id="item_total'+index+'" class="form-control" placeholder="Total" disabled/>';
        cell8.innerHTML  = '<button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm" tabindex="1"><i class="fas fa-times"></i></button> '+
                          '<button type="button" class="btn btn-primary btn-sm" onclick="addNewRow()" ><i class="fa fa-plus"></i></button>';
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
        totalQuantity = totalQuantity + Number(currentRow.cells[4].querySelector('input').value);
        totalAmount = totalAmount + Number(currentRow.cells[6].querySelector('input').value);
      }

      $('#total_qty').val(totalQuantity);
      $('#total_amt').val(totalAmount.toFixed());

      netTotal();
    }

    function netTotal(){
      var netTotal = 0;
      var total = Number($('#total_amt').val());
      netTotal = total.toFixed(0);
      FormattednetTotal = formatNumberWithCommas(netTotal);
      document.getElementById("netTotal").innerHTML = '<span class="text-4 text-danger">'+FormattednetTotal+'</span>';
      $('#net_amount').val(netTotal);
    }
    function formatNumberWithCommas(number) {
      // Convert number to string and add commas
      return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function updateUnit(row) {
      const productSelect = document.getElementById(`productSelect${row}`);
      const selectedOption = productSelect.options[productSelect.selectedIndex];
      const unit = selectedOption.getAttribute('data-unit'); // Get the unit from the selected option's data-unit attribute

      // Set the unit text in the unit field
      const unitField = document.getElementById(`unitSuffix${row}`);
      unitField.value = unit || ''; // Set the unit, or clear it if not available
    }
  </script>
@endsection
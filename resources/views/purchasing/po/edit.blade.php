@extends('layouts.app')

@section('title', 'Purchasing | New PO')

@section('content')
  <div class="page-header d-flex justify-content-end">
    <ul class="breadcrumbs mb-3">
      <li class="nav-home"><a href="#"> <i class="fa fa-home"></i></a></li>
      <li class="separator"> <i class="fa fa-chevron-right"></i></li>
      <li class="nav-item"> <a href="#">Purchasing</a></li>
      <li class="separator"><i class="fa fa-chevron-right"></i></li>
      <li class="nav-item"> <a href="#">Edit PO</a></li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form action="{{ route('purpos.update', $purpo->id) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="card-header">
            <div class="card-title">Edit PO</div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-12 col-md-3 form-group">
                <label>Vendor Name</label>
                <input type="text" name="vendor_name" class="form-control" value="{{ old('vendor_name', $purpo->vendor_name) }}" required />
              </div>
               <div class="col-12 col-md-3 form-group">
                <label>Order Date</label>
                <input type="date" name="order_date" class="form-control" value="{{ old('order_date', \Carbon\Carbon::parse($purpo->order_date)->format('Y-m-d')) }}" required />
            </div>
                <div class="col-12 col-md-3 form-group">
                    <label>Delivery Date</label>
                    <input type="date" name="delivery_date" class="form-control" 
                        value="{{ old('delivery_date', \Carbon\Carbon::parse($purpo->delivery_date)->format('Y-m-d')) }}" required />
                </div>
              <div class="col-12 col-md-3 form-group">
                <label>Payment Term</label>
                <select class="form-control" name="payment_term" required>
                  <option value="" disabled>Select Payment Term</option>
                  <option value="Advance" {{ $purpo->payment_term == 'Advance' ? 'selected' : '' }}>Advance</option>
                  <option value="Partial" {{ $purpo->payment_term == 'Partial' ? 'selected' : '' }}>Partial</option>
                  <option value="On Delivery" {{ $purpo->payment_term == 'On Delivery' ? 'selected' : '' }}>On Delivery</option>
                  <option value="Credit" {{ $purpo->payment_term == 'Credit' ? 'selected' : '' }}>Credit</option>
                </select>
              </div>
            </div>
          </div>
          <div class="card-body" style="max-height:400px; overflow-y:auto">
            <div class="card-title mb-3">Item Details</div>
            <table class="table table-bordered" id="myTable">
              <thead>
                <tr>
                  <th>Item Name</th>
                  <th>Category</th>
                  <th>Rate</th>
                  <th>Quantity</th>
                  <th>Unit</th>
                  <th>Total</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="PurPOTbleBody">
                @foreach ($purpo->details as $index => $detail)
                <tr>
                  <td><input type="text" name="details[{{ $index }}][item_name]" class="form-control" value="{{ old('details.' . $index . '.item_name', $detail->item_name) }}" required/></td>
                  <td>
                    <select class="form-control" name="details[{{ $index }}][category_id]" required>
                      <option value="" disabled>Select Category</option>
                      @foreach ($prodCat as $item)
                        <option value="{{ $item->id }}" {{ $item->id == $detail->category_id ? 'selected' : '' }}>{{ $item->name }}</option>
                      @endforeach
                    </select>
                  </td>
                  <td><input type="number" name="details[{{ $index }}][item_rate]" id="item_rate{{ $index }}" onchange="rowTotal({{ $index }})" step="any" class="form-control" value="{{ old('details.' . $index . '.item_rate', $detail->item_rate) }}" required/></td>
                  <td><input type="number" name="details[{{ $index }}][item_qty]" id="item_qty{{ $index }}" onchange="rowTotal({{ $index }})" step="any" class="form-control" value="{{ old('details.' . $index . '.item_qty', $detail->item_qty) }}" required/></td>
                  <td>
                    <select class="form-control" name="details[{{ $index }}][unit_id]" required>
                      <option value="" disabled>Select Unit</option>
                      @foreach ($produnits as $item)
                        <option value="{{ $item->id }}" {{ $item->id == $detail->unit_id ? 'selected' : '' }}>{{ $item->name }}</option>
                      @endforeach
                    </select>
                  </td>
                  <td><input type="number" id="item_total{{ $index }}" class="form-control" value="{{ old('details.' . $index . '.item_total', $detail->item_rate * $detail->item_qty ) }}" disabled/></td>
                  <td>
                    <button type="button" onclick="removeRow(this)" class="btn btn-danger" tabindex="1"><i class="fas fa-times"></i></button>
                    <button type="button" class="btn btn-primary" onclick="addNewRow()" ><i class="fa fa-plus"></i></button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="card-body">
            <div class="card-title mb-3">Summary</div>
            <div class="row">
              <div class="col-12 col-md-2 form-group">
                <label>Total Quantity</label>
                <input type="number" class="form-control" id="total_qty" placeholder="Total Quantity" disabled/>
              </div>
              <div class="col-12 col-md-2 form-group">
                <label>Total Amount</label>
                <input type="number" class="form-control" id="total_amt" placeholder="Total Amount" disabled />
              </div>
            </div>
          </div>
          <div class="card-action text-end">
            <a class="btn btn-danger" href="{{ route('purpos.index') }}" >Discard</a>
            <button type="submit" class="btn btn-primary">Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script>
    var index = {{ count($purpo->details) }};

    $(document).ready(function() {
      tableTotal();
    });

    function removeRow(button) {
      var tableRows = $("#PurPOTbleBody tr").length;
      if (tableRows > 1) {
        var row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
        index--;
      } 
      tableTotal();
    }

    function addNewRow() {
      var lastRow = $('#PurPOTbleBody tr:last');
      latestValue = lastRow[0].cells[1].querySelector('select').value;

      if (latestValue != "") {
        var table = document.getElementById('myTable').getElementsByTagName('tbody')[0];
        var newRow = table.insertRow(table.rows.length);

        var cell1 = newRow.insertCell(0);
        var cell2 = newRow.insertCell(1);
        var cell3 = newRow.insertCell(2);
        var cell4 = newRow.insertCell(3);
        var cell5 = newRow.insertCell(4);
        var cell6 = newRow.insertCell(5);
        var cell7 = newRow.insertCell(6);

        cell1.innerHTML = '<input type="text" name="details['+index+'][item_name]" class="form-control" placeholder="Item Name" required/>';
        cell2.innerHTML = '<select class="form-control" name="details['+index+'][category_id]"><option value="" disabled selected>Select Category</option>@foreach ($prodCat as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select>';
        cell3.innerHTML = '<input type="number" name="details['+index+'][item_rate]" step="any" id="item_rate'+index+'" onchange="rowTotal('+index+')" class="form-control" placeholder="Rate" required/>';
        cell4.innerHTML = '<input type="number" name="details['+index+'][item_qty]" step="any" id="item_qty'+index+'" onchange="rowTotal('+index+')" class="form-control" placeholder="Quantity" required/>';
        cell5.innerHTML = '<select class="form-control" name="details['+index+'][unit_id]"><option value="" disabled selected>Select Unit</option>@foreach ($produnits as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select>';
        cell6.innerHTML = '<input type="number" id="item_total'+index+'" class="form-control" placeholder="Total" disabled/>';
        cell7.innerHTML = '<button type="button" onclick="removeRow(this)" class="btn btn-danger"><i class="fas fa-times"></i></button>  <button type="button" class="btn btn-primary" onclick="addNewRow()"><i class="fa fa-plus"></i></button>';

        index++;
        tableTotal();
      }
    }

    function rowTotal(index) {
      var item_rate = parseFloat($('#item_rate'+index).val());
      var item_qty = parseFloat($('#item_qty'+index).val());
      var item_total = item_rate * item_qty;

      $('#item_total'+index).val(item_total.toFixed(2));
      tableTotal();
    }

    function tableTotal(){
      var totalQuantity=0;
      var totalAmount=0;
      var tableRows = $("#PurPOTbleBody tr").length;
      var table = document.getElementById('myTable').getElementsByTagName('tbody')[0];

      for (var i = 0; i < tableRows; i++) {
        var currentRow =  table.rows[i];
        totalQuantity = totalQuantity + Number(currentRow.cells[3].querySelector('input').value);
        totalAmount = totalAmount + Number(currentRow.cells[5].querySelector('input').value);
      }

      $('#total_qty').val(totalQuantity);
      $('#total_amt').val(totalAmount.toFixed());
    }
</script>
@endsection
   

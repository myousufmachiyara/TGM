@extends('layouts.app')

@section('title', 'Purchasing | New PO')

@section('content')
  <div class="page-header d-flex justify-content-end">
    <ul class="breadcrumbs mb-3">
      <li class="nav-home"><a href="#"> <i class="fa fa-home"></i></a></li>
      <li class="separator"> <i class="fa fa-chevron-right"></i></li>
      <li class="nav-item"> <a href="#">Purchasing</a></li>
      <li class="separator"><i class="fa fa-chevron-right"></i></li>
      <li class="nav-item"> <a href="#">New PO</a></li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form action="{{ route('purpos.store') }}" method="POST">
          @csrf
          <div class="card-header">
            <div class="card-title">New PO</div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-12 col-md-3 form-group">
                <label>Vendor Name</label>
                <input type="text" name="vendor_name" class="form-control" placeholder="Vendor Name" required/>
                <input type="hidden" id="itemCount" name="items" value="1" class="form-control">
              </div>
              <div class="col-12 col-md-3 form-group">
                <label>Order Date</label>
                <input type="date" name="order_date" class="form-control" placeholder="Order Date" required/>
              </div>
              <div class="col-12 col-md-3 form-group">
                <label>Delivery Date</label>
                <input type="date" name="delivery_date" class="form-control" placeholder="Delivery Date" required/>
              </div>
              <div class="col-12 col-md-3 form-group">
                <label>Payment Term</label>
                <select class="form-control"  name="payment_term" required>
                  <option selected disabled>Select Payment Term</option>
                  <option>Credit</option>
                  <option>Cash</option>
                </select>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="card-title mb-3">Item Details</div>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Item Name</th>
                  <th>Item Category</th>
                  <th>Rate</th>
                  <th>Quantity</th>
                  <th>Total</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="PurPOTbleBody">
                <tr>
                  <td><input type="text" name="item_name[]" class="form-control" placeholder="Item Name" required/></td>
                  <td>
                  <select class="form-control" name="category_id">  <!-- Added name attribute for form submission -->
                    <option selected disabled>Select Category</option>
                    @foreach ($prodCat as $item)
                      <option value="{{ $item->id }}">{{ $item->name }}</option>  <!-- Use category ID as the value and name as the display text -->
                    @endforeach
                  </select>
                  </td>
                  <td><input type="number" name="item_rate[]" class="form-control" placeholder="Rate" required/></td>
                  <td><input type="number" name="item_qty[]" class="form-control" placeholder="Quantity" required/></td>
                  <td><input type="number" name="item_total[]" class="form-control" placeholder="Total" disabled/></td>
                  <td>
										<button type="button" onclick="removeRow(this)" class="btn btn-danger" tabindex="1"><i class="fas fa-times"></i></button>
                    <button type="button" class="btn btn-primary"><i class="fa fa-plus"></i></button></tr>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="card-action text-end">
            <a class="btn btn-danger" href="{{ route('purpos.index') }}" >Discard</a>
            <button type="submit" class="btn btn-primary">Create</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script>

    var index=2;
    var itemCount = Number($('#itemCount').val());

    function removeRow(button) {
      var tableRows = $("#PurPOTbleBody tr").length;
      if(tableRows>1){
        var row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
        index--;	
        itemCount = Number($('#itemCount').val());
        itemCount = itemCount-1;
        $('#itemCount').val(itemCount);
      }   
    }

    document.getElementById('removeRowBtn').addEventListener('click', function() {
      var table = document.getElementById('myTable').getElementsByTagName('tbody')[0];
      if (table.rows.length > 0) {
          table.deleteRow(table.rows.length - 1);
      } else {
          alert("No rows to delete!");
      }
    });

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

        cell1.innerHTML  = '<input type="text" class="form-control" disabled>';
        cell2.innerHTML  = '<select data-plugin-selecttwo class="form-control select2-js"   onclick="addNewRow('+index+')" name ="item_group[]" required>'+
                    '<option value="" disabled selected>Select Group</option>'+
                    @foreach ($prodCat as $key => $row)
                      '<option value="{{ $row->id }}">{{ $row->name }}</option>'
                    @endforeach
                  '</select>';
        cell3.innerHTML  = '<input type="text"   class="form-control" name="item_remarks[]">';
        cell4.innerHTML  = '<input type="number" class="form-control" name="item_stock[]" required value="0" step=".00001">';
        cell5.innerHTML  = '<input type="number" class="form-control" name="weight[]" required value="0" step=".00001">';
        cell16.innerHTML = '<button type="button" onclick="removeRow(this)" class="btn btn-danger" tabindex="1"><i class="fas fa-times"></i></button>';

        index++;
        itemCount = Number($('#itemCount').val());
        itemCount = itemCount+1;
        $('#itemCount').val(itemCount);
      }

    }
  </script>
@endsection
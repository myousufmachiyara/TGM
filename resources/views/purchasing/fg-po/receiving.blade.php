@extends('layouts.app')

@section('title', 'Purchasing | New PO')

@section('content')
  <div class="row">
    <form action="{{ route('pur-fgpos.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @if ($errors->has('error'))
        <strong class="text-danger">{{ $errors->first('error') }}</strong>
      @endif
      <div class="row">
        <div class="col-12 mb-4">
          <section class="card">
            <header class="card-header">
              <div style="display: flex;justify-content: space-between;">
                <h2 class="card-title">PO Challan</h2>
              </div>
            </header>
            <div class="card-body">
              <div class="row mb-4">
                <div class="col-12 col-md-2">
                  <label>GRN #</label>
                  <input type="text" class="form-control" placeholder="GRN #" disabled/>
                </div>
                <div class="col-12 col-md-2 mb-3">
                  <label>Select PO</label>
                  <select data-plugin-selecttwo class="form-control select2-js" name="vendor_name" required>  <!-- Added name attribute for form submission -->
                    <option value="" selected disabled>Select PO</option>
                    @foreach ($purpos as $item)
                      <option value="{{ $item->id }}">PO-{{ $item->id }}</option> 
                    @endforeach
                  </select>
                </div>
                
                <div class="col-12 col-md-2">
                  <label>Receiving Date</label>
                  <input type="date" name="order_date" class="form-control" value="<?php echo date('Y-m-d'); ?>"   placeholder="Order Date" required/>
                </div>
              </div>
            </div>
          </section>
        </div>
        <div class="col-12 mb-4">
          <section class="card">
            <header class="card-header">
              <h2 class="card-title">Fabric Details</h2>
            </header>
            
            <div class="card-body">
              <table class="table table-bordered" id="myTable">
                <thead>
                  <tr>
                    <th>Fabric</th>
                    <th>Description</th>
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
                       
                      </select>  
                    </td>
                    <td><input type="text" name="details[0][for]"  id="item_for1" class="form-control" placeholder="Description" required/></td>
                    <td><input type="number" name="details[0][item_rate]"  id="item_rate1" onchange="rowTotal(1)" step="any" class="form-control" placeholder="Rate" required/></td>
                    <td><input type="number" name="details[0][item_qty]"   id="item_qty1" onchange="rowTotal(1)" step="any" class="form-control" placeholder="Quantity" required/></td>
                    <td><input type="number" id="item_total1" class="form-control" placeholder="Total" disabled/></td>
                    <td>
                      <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-xs" tabindex="1"><i class="fas fa-times"></i></button>
                      <button type="button" class="btn btn-primary btn-xs" onclick="addNewRow()" ><i class="fa fa-plus"></i></button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </div>
        <div class="col-12">
          <section class="card">
            <header class="card-header">
              <h2 class="card-title">Summary</h2>
            </header>
            
            <div class="card-body">
              <div class="row">
                <div class="col-12 col-md-2">
                  <label>Total Fabric Amount</label>
                  <input type="number" class="form-control" id="total_qty" placeholder="Total Quantity" disabled/>
                </div>

                <div class="col-12 col-md-2 mb-3">
                  <label>Billing Terms</label>
                  <select data-plugin-selecttwo class="form-control select2-js" name="vendor_name" required>  <!-- Added name attribute for form submission -->
                    <option value="" selected disabled>Select Billing Terms</option>
                    <option value=""> Bill By Per Piece</option> 
                    <option value=""> Bill By Stitching Rate</option> 
                  </select>
                </div>

                <div class="col-12 col-md-2">
                  <label>Rate</label>
                  <input type="number" class="form-control" id="total_qty" placeholder="Total Quantity" />
                </div>

                <div class="col-12 col-md-2">
                  <label>Bill Amount</label>
                  <input type="number" class="form-control" id="total_qty" placeholder="Total Quantity" disabled/>
                </div>
              </div>
            </div>

            <footer class="card-footer text-end">
              <a class="btn btn-danger" href="{{ route('pur-fgpos.index') }}" >Discard</a>
              <button type="submit" class="btn btn-primary">Create</button>
            </footer>
          </section>
        </div>
      </div>
    </form>
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

        cell1.innerHTML  = '<select data-plugin-selecttwo class="form-control select2-js" name="details['+index+'][item_id]">'+
                            '<option value="" disabled selected>Select Category</option>'+
                           
                          '</select>';
        cell2.innerHTML  = '<input type="text" name="details['+index+'][for]" step="any" id="item_for'+index+'"  class="form-control" placeholder="Description" required/>';
        cell3.innerHTML  = '<input type="number" name="details['+index+'][item_rate]" step="any" id="item_rate'+index+'"  onchange="rowTotal('+index+')" class="form-control" placeholder="Rate" required/>';
        cell4.innerHTML  = '<input type="number" name="details['+index+'][item_qty]" step="any" id="item_qty'+index+'"  onchange="rowTotal('+index+')" class="form-control" placeholder="Quantity" required/>';
        cell5.innerHTML  = '<input type="number" id="item_total'+index+'" class="form-control" placeholder="Total" disabled/>';
        cell6.innerHTML  = '<button type="button" onclick="removeRow(this)" class="btn btn-danger" tabindex="1"><i class="fas fa-times"></i></button> '+
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
      var tableRows = $("#PurPOTbleBody tr").length;
      var table = document.getElementById('myTable').getElementsByTagName('tbody')[0];

      for (var i = 0; i < tableRows; i++) {
        var currentRow =  table.rows[i];
        totalQuantity = totalQuantity + Number(currentRow.cells[2].querySelector('input').value);
      }

      $('#total_qty').val(totalQuantity);
    }
  </script>

@endsection
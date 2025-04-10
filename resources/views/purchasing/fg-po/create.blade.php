@extends('layouts.app')

@section('title', 'Purchasing | New PO')

@section('content')
  <div class="row">
    <form action="{{route('pur-fgpos.store')}}" method="POST" enctype="multipart/form-data">
      @csrf

      @if ($errors->any())
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
     
      <div class="row">
        <div class="col-12 col-md-12 mb-3">
          <section class="card">
            <header class="card-header">
              <div style="display: flex;justify-content: space-between;">
                <h2 class="card-title">New PO</h2>
              </div>
            </header>
            <div class="card-body">
              <div class="row">
                <div class="col-12 col-md-1 mb-3">
                  <label>PO #</label>
                  <input type="number" class="form-control"  placeholder="PO #" disabled/>
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

                <div class="col-12 col-md-2 mb-3">
                  <label>Vendor Name</label>
                  <select data-plugin-selecttwo class="form-control select2-js" name="vendor_id" id="vendor_name" required>  <!-- Added name attribute for form submission -->
                    <option value="" selected disabled>Select Vendor</option>
                    @foreach ($coa as $item)
                      <option value="{{ $item->id }}">{{ $item->name }}</option> 
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-md-2 mb-3">
                  <label>Order Date</label>
                  <input type="date" name="order_date" class="form-control" id="order_date" value="<?php echo date('Y-m-d'); ?>"   placeholder="Order Date" required/>
                </div>

                <div class="col-12 col-md-4 mb-3">
                  <label>Item Name <a href="#" ><i class="fa fa-plus"></i></a></label>
                  <select multiple data-plugin-selecttwo class="form-control select2-js" name="product_id" id="item_name" required>  <!-- Added name attribute for form submission -->
                    <option value=""  disabled>Select Item</option>
                    @foreach ($articles as $item)
                      <option value="{{ $item->id }}">{{ $item->sku }}-{{ $item->name }}</option> 
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-md-1">
                  <label>Item Variations</label>
                  <button type="button" class="d-block btn btn-success" id="generate-variations-btn" >Generate</button>
                </div>
              </div>
              <div class="col-12 col-md-5 mt-3 ">
                <table class="table table-bordered" id="variationsTable">
                  <thead>
                    <tr>
                      <th>S.No</th>
                      <th>Item</th>
                      <th>SKU</th>
                      <th>Quantity</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="variationsTableBody">
                  </tbody>
                </table>
              </div>
            </div>
          </section>
        </div>

        <div class="col-12 col-md-12 mb-3">
          <section class="card">
            <header class="card-header" style="display: flex;justify-content: space-between;">
              <h2 class="card-title">Fabric Details</h2>
            </header>
            <div class="card-body">
              <table class="table table-bordered" id="myTable">
                <thead>
                  <tr>
                    <th>Fabric</th>
                    <th>PO Code</th>
                    <th>Description</th>
                    <th>Rate</th>
                    <th>Qty</th>
                    <th>Width</th>
                    <th>M.Unit</th>
                    <th>Total</th>
                    <th width="5%"></th>
                  </tr>
                </thead>
                <tbody id="PurPOTbleBody">
                  <tr>
                    <td>
                      <select data-plugin-selecttwo class="form-control select2-js" name="voucher_details[0][product_id]" id="productSelect0" onchange="getData(0)" required>  <!-- Added name attribute for form submission -->
                        <option value="" selected disabled>Select Fabric</option>
                        @foreach ($fabrics as $item)
                          <option value="{{ $item->id }}" data-unit="{{ $item->measurement_unit }}" data-width="{{ $item->width }}" >{{$item->sku }} - {{$item->name }}</option> 
                        @endforeach
                      </select>  
                    </td>
                    <td>
                      <select data-plugin-selecttwo class="form-control select2-js" id="poIDSelect0" name="voucher_details[0][po_id]" required>  <!-- Added name attribute for form submission -->
                        <option value="0" selected>Select PO Id</option>
                      </select>  
                    </td>
                    <td><input type="text" name="voucher_details[0][description]" class="form-control" placeholder="Description"/></td>
                    <td><input type="number" name="voucher_details[0][item_rate]" id="item_rate_0" onchange="rowTotal(0)" step="any" value="0" class="form-control" placeholder="Rate" required/></td>
                    <td><input type="number" name="voucher_details[0][qty]" id="item_qty_0" onchange="rowTotal(0)" step="any" value="0" class="form-control" placeholder="Quantity" required/></td>
                    <td><input type="number" id="item_width_0" class="form-control" name="voucher_details[0][width]" placeholder="Width" disabled required/></td>
                    <td><input type="text" id="item_unit_0" class="form-control" name="voucher_details[0][unit]" placeholder="M.Unit" disabled required/></td>
                    <td><input type="number" id="item_total_0" class="form-control" placeholder="Total" disabled/></td>
                    <td width="5%">
                      <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-xs" tabindex="1"><i class="fas fa-times"></i></button>
                      <button type="button" class="btn btn-primary btn-xs"  onclick="addNewRow()" ><i class="fa fa-plus"></i></button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </div>

        <div class="col-12 col-md-5 mb-3">
          <section class="card">
            <header class="card-header" style="display: flex;justify-content: space-between;">
              <h2 class="card-title">Voucher (Challan #)</h2>
              <div>
                <a class="btn btn-danger text-end" aria-expanded="false" onclick="generateVoucher()">Generate Challan</a>
              </div>
            </header>
            <div class="card-body">
              <div class="row pb-4">
                <div class="col-12 mt-3" id="voucher-container">
                  
                </div>
              </div>
            </div>
          </section>
        </div>

        <div class="col-12 col-md-7">
          <section class="card">
            <header class="card-header" style="display: flex;justify-content: space-between;">
              <h2 class="card-title">Summary</h2>
            </header>
            <div class="card-body">
              <div class="row pb-4">
                <div class="col-12 col-md-3">
                  <label>Total Fabric Quantity</label>
                  <input type="number" class="form-control" id="total_fab" placeholder="Total Fabric" disabled/>
                </div>

                <div class="col-12 col-md-3">
                  <label>Total Fabric Amount</label>
                  <input type="number" class="form-control" id="total_fab_amt" placeholder="Total Fabric Amount" disabled />
                </div>

                <div class="col-12 col-md-3">
                  <label>Total Units (Estimated)</label>
                  <input type="number" class="form-control" id="total_units" placeholder="Total Units" disabled/>
                </div>
                
                <div class="col-12 col-md-3">
                  <label>Attachement </label>
                  <input type="file" class="form-control" name="att[]" multiple accept="image/png, image/jpeg, image/jpg, image/webp">
                </div>

                <div class="col-12 pb-sm-3 pb-md-0 text-end">
                  <h3 class="font-weight-bold mb-0 text-5 text-primary">Net Amount</h3>
                  <span>
                    <strong class="text-4 text-primary">PKR <span id="netTotal" class="text-4 text-danger">0.00 </span></strong>
                  </span>
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

    var index=1;

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
        var cell9 = newRow.insertCell(8);

        cell1.innerHTML  = '<select data-plugin-selecttwo id="productSelect'+index+'" class="form-control select2-js" onchange="getData('+index+')" name="voucher_details['+index+'][product_id]">'+
                            '<option value="" disabled selected>Select Fabric</option>'+
                            @foreach ($fabrics as $item)
                              '<option value="{{ $item->id }}" data-unit="{{ $item->measurement_unit }}" data-width="{{ $item->width }}">{{$item->sku }} - {{$item->name }}</option>'+
                            @endforeach
                          '</select>';

        cell2.innerHTML  = '<select data-plugin-selecttwo id="poIDSelect'+index+'" class="form-control select2-js" name="voucher_details['+index+'][po_id]">'+
                            '<option value="0" selected>Select PO Code</option>'+
                          '</select>';
        cell3.innerHTML  = '<input type="text" name="voucher_details['+index+'][description]" class="form-control" placeholder="Description" required/>';
        cell4.innerHTML  = '<input type="number" name="voucher_details['+index+'][item_rate]" step="any" id="item_rate_'+index+'" value="0" onchange="rowTotal('+index+')" class="form-control" placeholder="Rate" required/>';
        cell5.innerHTML  = '<input type="number" name="voucher_details['+index+'][qty]" step="any" id="item_qty_'+index+'" value="0" onchange="rowTotal('+index+')" class="form-control" placeholder="Quantity" required/>';
        cell6.innerHTML  = '<input type="number"  id="item_width_'+index+'" class="form-control" name="voucher_details['+index+'][width]" placeholder="Width" disabled required/>';
        cell7.innerHTML  = '<input type="text" id="item_unit_'+index+'" class="form-control" name="voucher_details['+index+'][unit]" placeholder="M.Unit" disabled required/>';
        cell8.innerHTML  = '<input type="number" id="item_total_'+index+'" class="form-control" placeholder="Total" disabled/>';
        cell9.innerHTML  = '<button type="button" onclick="removeRow(this)" class="btn btn-danger btn-xs" tabindex="1"><i class="fas fa-times"></i></button> '+
                          '<button type="button" class="btn btn-primary btn-xs" onclick="addNewRow()" ><i class="fa fa-plus"></i></button>';
        index++;
        
        tableTotal();
      }
      $('#myTable select[data-plugin-selecttwo]').select2();

    }

    function rowTotal(index){
      var item_rate = parseFloat($('#item_rate_'+index+'').val());
      var item_qty = parseFloat($('#item_qty_'+index+'').val());   
      var item_total = item_rate * item_qty;

      $('#item_total_'+index+'').val(item_total.toFixed());
      
      tableTotal();
    }

    function tableTotal(){
      var totalQuantity=0;
      var totalAmount=0;
      var totalUnits=0;

      var tableRows = $("#PurPOTbleBody tr").length;
      var table = document.getElementById('myTable').getElementsByTagName('tbody')[0];

      var variationTableRows = $("#variationsTableBody tr").length;
      var variationTable = document.getElementById('variationsTable').getElementsByTagName('tbody')[0];

      for (var i = 0; i < tableRows; i++) {
        var currentRow =  table.rows[i];
        totalQuantity = totalQuantity + Number(currentRow.cells[3].querySelector('input').value);
        totalAmount = totalAmount + Number(currentRow.cells[5].querySelector('input').value);
      }

      for (var j = 0; j < variationTableRows; j++) {
        var currtRow =  variationTable.rows[j];
        totalUnits = totalUnits + Number(currtRow.cells[3].querySelector('input').value);
      }

      $('#total_fab').val(totalQuantity);
      $('#total_fab_amt').val(totalAmount.toFixed());
      $('#total_units').val(totalUnits.toFixed());

      netTotal();
    }

    function netTotal(){
      var netTotal = 0;
      var total = Number($('#total_fab_amt').val());

      netTotal = total;
      netTotal = netTotal.toFixed(0);
      FormattednetTotal = formatNumberWithCommas(netTotal);
      document.getElementById("netTotal").innerHTML = '<span class="text-4 text-danger">'+FormattednetTotal+'</span>';
      $('#net_amount').val(netTotal);
    }

    function formatNumberWithCommas(number) {
      // Convert number to string and add commas
      return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function generateVoucher() {
      document.getElementById("voucher-container").innerHTML = "";

      // Sample Data (Replace with dynamic values if needed)
      let vendorName = document.querySelector("#vendor_name option:checked").textContent;
      let date = $('#order_date').val();
      let purchaseOrderNo = "FGPO-";

      let items = [];
      let rows = document.querySelectorAll("#PurPOTbleBody tr");

      rows.forEach((row, key) => {
        let selects = row.querySelectorAll("select"); // Get all selects in the row
        let fabric = selects[0].options[selects[0].selectedIndex].text; // First dropdown (Fabric)

        let unitInput = row.querySelector(`#item_unit_${key}`);
        let unit = unitInput?.value || '';

        // If you also want width:
        let widthInput = row.querySelector(`#item_width_${key}`);
        let width = widthInput?.value || '';

        let description = row.querySelector("input[name='voucher_details["+key+"][description]']").value;
        let rate = row.querySelector("input[name='voucher_details["+key+"][item_rate]']").value;
        let quantity = row.querySelector("input[name='voucher_details["+key+"][qty]']").value;
        let total = row.querySelector("#item_total_" + key).value;

        if (fabric && quantity && rate && unit) {
          items.push({ fabric, description, rate, quantity, unit, width, total });
        }
      });
      
      let totalAmount = items.reduce((sum, item) => sum + parseFloat(item.total || 0), 0);

      // Construct the HTML for the voucher
      let voucherHTML = `
        <div class="border p-3 mt-3">
          <h3 class="text-center text-dark">Payment Voucher</h3>
          <hr>
          
          <!-- Vendor, PO No, Item, and Date in One Line -->
          <div class="d-flex justify-content-between text-dark">
            <p class="text-dark"><strong>Vendor:</strong> ${vendorName}</p>
            <p class="text-dark"><strong>PO No:</strong> ${purchaseOrderNo}</p>
            <p class="text-dark"><strong>Date:</strong> ${date}</p>
          </div>

          <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Fabric Name</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Rate</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                ${items.map(item => `
                    <tr>
                        <td>${item.fabric}</td>
                        <td>${item.description}</td>
                        <td>${item.quantity} ${item.unit}</td>
                        <td>${item.rate}</td>
                        <td>${item.total}</td>
                    </tr>`).join('')}
            </tbody>
          </table>
          
          <h4 class="text-end text-dark"><strong>Total Amount:</strong> ${totalAmount} PKR</h4>
          <input type="hidden" name="voucher_amount" id="" value="${totalAmount}">
          <div class="d-flex justify-content-between mt-4">
            <div>
              <p class="text-dark"><strong>Authorized By:</strong></p>
              <p>________________________</p>
            </div> 
          </div>
        </div>
      `;

      // Insert the voucher into the voucher-container div
      document.getElementById("voucher-container").innerHTML = voucherHTML;
    }

    $("#generate-variations-btn").click(function () {
      let tableBody = $("#variationsTable tbody");
      tableBody.empty();

      let productIds = $("#item_name").val(); // Get selected product IDs (array)

      if (!productIds || productIds.length === 0) {
        alert("Please select at least one item.");
        return;
      }

      $.ajax({
        url: `/productDetails`, // Laravel route (updated)
        type: "POST", // Change to POST for multiple IDs
        data: {
          product_ids: productIds,
          _token: $('meta[name="csrf-token"]').attr("content"), // CSRF Token
        },
        dataType: "json",
        success: function (response) {
          let rowIndex = 0; // Global counter for indexing

          let tableBody = $("#variationsTable tbody");
          tableBody.empty();

          if (response.length > 0) {
            response.forEach((product) => {
              if (product.variations.length > 0) {
                product.variations.forEach((variation, key) => {
                  let row = `<tr>
                    <td>${rowIndex + 1}</td>
                    <td>${product.name}</td>
                    <td>
                      <input type="hidden" name="item_order[${rowIndex}][product_id]" value="${product.id}">
                      <input type="hidden" name="item_order[${rowIndex}][variation_id]" value="${variation.id}">
                      <input type="hidden" name="item_order[${rowIndex}][sku]" value="${variation.sku}">
                      ${variation.sku}
                    </td>
                    <td><input type="number" onchange="tableTotal()" name="item_order[${rowIndex}][qty]" class="form-control " placeholder="Quantity" required /></td>
                    <td><button class="btn btn-danger btn-sm delete-row">Delete</button></td>
                  </tr>`;
                  tableBody.append(row);
                  rowIndex++; // Increment the global counter
                });
              } else {
                tableBody.append(`<tr><td colspan="5" class="text-center">No variations found for ${product.name}.</td></tr>`);
              }
            });
          } else {
            tableBody.append(`<tr><td colspan="5" class="text-center">No variations found.</td></tr>`);
          }
        },
        error: function (error) {
          console.error("Error fetching product details:", error);
          alert("Failed to fetch product details.");
        }
      });
    });

    $(document).on("click", ".delete-row", function () {
      $(this).closest("tr").remove();
    });

    function getData(row){

      const productSelect = document.getElementById(`productSelect${row}`);
      const productId = productSelect?.value;

      if (!productId) {
        alert("Please select a product first.");
        return;
      }

      $.ajax({
        url: '/get-po-codes', // Laravel route
        type: 'POST',
        data: {
          product_id: productId,
          _token: $('meta[name="csrf-token"]').attr('content') // CSRF
        },
        success: function(response) {
          const dropdown = document.getElementById(`poIDSelect${row}`);
          dropdown.innerHTML = '<option value="0">Select PO ID</option>'; // Clear & reset

          if (response.po_ids && response.po_codes) {
            response.po_ids.forEach((id, index) => {
              console.log(id,index);
              const option = document.createElement("option");
              option.value = id;
              option.textContent = response.po_codes[index];
              dropdown.appendChild(option);
            });
          }
        },
        error: function(xhr) {
          console.error('Error fetching PO data:', xhr.responseText);
        }
      });

      updateUnit(row);
    }

    function updateUnit(row) {
      const productSelect = document.getElementById(`productSelect${row}`);
      const selectedOption = productSelect.options[productSelect.selectedIndex];
      const unit = selectedOption.getAttribute('data-unit'); // Get the unit from the selected option's data-unit attribute
      const width = selectedOption.getAttribute('data-width'); // Get the unit from the selected option's data-unit attribute

      // Set the unit text in the unit field
      const unitField = document.getElementById(`item_unit_${row}`);
      const widthField = document.getElementById(`item_width_${row}`);

      unitField.value = unit || ''; // Set the unit, or clear it if not available
      widthField.value = width || ''; // Set the unit, or clear it if not available
    }
  </script>
@endsection
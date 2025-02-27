@extends('layouts.app')

@section('title', 'Purchasing | FGPO Receiving')

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
                <h2 class="card-title">FGPO Receiving</h2>
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
                
                <div class="col-12 col-md-1">
                  <label>PO Item</label>
                  <button type="button" class="d-block btn btn-success" id="btn-get-po-data" >Get Data</button>
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
                    <th>Item</th>
                    <th>Ordered Quantity</th>
                    <th>Received</th>
                    <th>Remaining</th>
                    <th>Receiving</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody id="PurPOTbleBody">
                  <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><input type="number" id="" class="form-control" placeholder="Receiving"/></td>
                    <td>
                      <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-xs" tabindex="1"><i class="fas fa-times"></i></button>
                      <button type="button" class="btn btn-primary btn-xs" onclick="addNewRow()" ><i class="fa fa-plus"></i></button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <footer class="card-footer text-end mt-1">
                <a class="btn btn-danger" href="{{ route('pur-fgpos.index') }}" >Discard</a>
                <button type="submit" class="btn btn-primary">Create</button>
              </footer>
            </div>
          </section>
        </div>
      </div>
    </form>
  </div>
  <script>
    $("#btn-get-po-data").click(function () {
      let tableBody = $("#variationsTable tbody");
      tableBody.empty();

      let fgpoid = $("#item_name").val(); // Get selected product IDs (array)

      if (!fgpoid || fgpoid.length === 0) {
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
  </script>
@endsection
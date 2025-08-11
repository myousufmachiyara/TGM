@extends('layouts.app')

@section('title', 'Purchasing | Edit Job PO') 

@section('content')
<div class="row">
  <form action="{{ route('pur-fgpos.update', $po->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

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
              <h2 class="card-title">Edit PO</h2>
            </div>
          </header>
          <div class="card-body">
            <div class="row">
              <div class="col-12 col-md-1 mb-3">
                <label>PO #</label>
                <input type="text" class="form-control" value="{{ $po->id }}" disabled/>
              </div>

              <div class="col-12 col-md-2">
                <label>Category<span style="color: red;"><strong>*</strong></span></label>
                <select data-plugin-selecttwo class="form-control select2-js" name="category_id" required>
                  <option value="" disabled>Select Category</option>
                  @foreach ($prodCat as $cat)
                    <option value="{{ $cat->id }}" @selected($po->category_id == $cat->id)>{{ $cat->name }}</option> 
                  @endforeach
                </select>
              </div>

              <div class="col-12 col-md-2 mb-3">
                <label>Vendor Name</label>
                <select data-plugin-selecttwo class="form-control select2-js" name="vendor_id" id="vendor_name" required>
                  <option value="" disabled>Select Vendor</option>
                  @foreach ($coa as $item)
                    <option value="{{ $item->id }}" @selected($po->vendor_id == $item->id)>{{ $item->name }}</option> 
                  @endforeach
                </select>
              </div>

              <div class="col-12 col-md-2 mb-3">
                <label>Order Date</label>
                
                <input type="date" name="order_date" class="form-control" id="order_date" value="{{ $po->order_date}}" required/>
              </div>

              <div class="col-12 col-md-4 mb-3">
                <label>Item Name <a href="#"><i class="fa fa-plus"></i></a></label>
                <select multiple data-plugin-selecttwo class="form-control" name="product_id[]" id="item_name" required>
                  <option value="" disabled>Select Item</option>
                  @foreach ($articles as $item)
                    <option value="{{ $item->id }}" 
                      @selected(in_array($item->id, $po->details->pluck('product_id')->toArray()))>
                      {{ $item->sku }}-{{ $item->name }}
                    </option> 
                  @endforeach
                </select>
              </div>
              <div class="col-12 col-md-1">
                <label>Item Variations</label>
                <button type="button" class="d-block btn btn-success" id="generate-variations-btn">Generate</button>
              </div>
            </div>

            <div class="col-12 col-md-6 mt-3">
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
                  @php $varIndex = 0; @endphp
                  @foreach ($po->details->whereNotNull('variation_id') as $detail)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $detail->product->name }}</td>
                      <td>
                        <input type="hidden" name="item_order[{{ $varIndex }}][product_id]" value="{{ $detail->product_id }}">
                        <input type="hidden" name="item_order[{{ $varIndex }}][variation_id]" value="{{ $detail->variation_id }}">
                        <input type="hidden" name="item_order[{{ $varIndex }}][sku]" value="{{ $detail->variation->sku }}">
                        {{ $detail->variation->sku }}
                      </td>
                      <td><input type="number" onchange="tableTotal()" name="item_order[{{ $varIndex }}][qty]" 
                        value="{{ $detail->qty }}" class="form-control" required/></td>
                      <td><button class="btn btn-danger btn-sm delete-row">Delete</button></td>
                    </tr>
                    @php $varIndex++; @endphp
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div>

      {{-- Fabric Details --}}
      <div class="col-12 col-md-12 mb-3">
        <section class="card">
          <header class="card-header">
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
                @php $fabIndex = 0; @endphp
                @foreach ($po->voucherDetails as $fabric)
                  <tr>
                      <td>
                        <select data-plugin-selecttwo class="form-control select2-js"
                            name="voucher_details[{{ $fabIndex }}][product_id]"
                            id="productSelect{{ $fabIndex }}"
                            onchange="getData({{ $fabIndex }})" required>
                            <option value="" disabled>Select Fabric</option>
                            @foreach ($fabrics as $item)
                                <option value="{{ $item->id }}" data-unit="{{ $item->measurement_unit }}"
                                    @selected($fabric->product_id == $item->id)>
                                    {{ $item->sku }} - {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                      </td>
                      <td>
                        <select data-plugin-selecttwo class="form-control select2-js"
                            id="poIDSelect{{ $fabIndex }}"
                            onchange="fetchWidth({{ $fabIndex }})"
                            name="voucher_details[{{ $fabIndex }}][po_id]" required>
                            <option value="0" @selected($fabric->po_id == 0)>Select PO Id</option>
                            @if($fabric->po_id && $fabric->po_code)
                                <option value="{{ $fabric->po_id }}" selected>{{ $fabric->po_code }}</option>
                            @endif
                        </select>
                      </td>
                      <td><input type="text" name="voucher_details[{{ $fabIndex }}][description]" value="{{ $fabric->description }}" class="form-control" /></td>
                      <td><input type="number" name="voucher_details[{{ $fabIndex }}][item_rate]"
                          id="item_rate_{{ $fabIndex }}" onchange="rowTotal({{ $fabIndex }})"
                          step="any" value="{{ $fabric->rate }}" class="form-control" required /></td>
                      <td><input type="number" name="voucher_details[{{ $fabIndex }}][qty]"
                          id="item_qty_{{ $fabIndex }}" onchange="rowTotal({{ $fabIndex }})"
                          step="any" value="{{ $fabric->qty }}" class="form-control" required /></td>
                      <td><input type="number" id="item_width_{{ $fabIndex }}" class="form-control"
                          name="voucher_details[{{ $fabIndex }}][width]" value="{{ $fabric->width }}" required/></td>
                      <td><input type="text" id="item_unit_{{ $fabIndex }}" class="form-control"
                          name="voucher_details[{{ $fabIndex }}][unit]" value="{{ $fabric->product->measurement_unit }}" disabled required/></td>
                      <td><input type="number" id="item_total_{{ $fabIndex }}" class="form-control"
                          value="{{ $fabric->rate * $fabric->qty }}" disabled/></td>
                      <td>
                          <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-xs"><i class="fas fa-times"></i></button>
                          <button type="button" class="btn btn-primary btn-xs" onclick="addNewRow()"><i class="fa fa-plus"></i></button>
                      </td>
                  </tr>
                  @php $fabIndex++; @endphp
                @endforeach

              </tbody>
            </table>
          </div>
        </section>
      </div>

      {{-- Voucher --}}
      <div class="col-12 col-md-5 mb-3">
        <section class="card">
          <header class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title mb-0">Voucher (Challan #)</h2>
            <a class="btn btn-danger" onclick="generateVoucher()">Generate Challan</a>
          </header>
          <div class="card-body">
            <div class="row pb-4">
              <div class="col-12 mt-3" id="voucher-container"></div>
            </div>
          </div>
        </section>
      </div>

      {{-- Summary --}}
      <div class="col-12 col-md-7">
        <section class="card">
          <header class="card-header">
            <h2 class="card-title">Summary</h2>
          </header>
          <div class="card-body">
            <div class="row pb-4">
              <div class="col-12 col-md-3">
                <label>Total Fabric Quantity</label>
                <input type="number" class="form-control" id="total_fab" disabled/>
              </div>
              <div class="col-12 col-md-3">
                <label>Total Fabric Amount</label>
                <input type="number" class="form-control" id="total_fab_amt" disabled/>
              </div>
              <div class="col-12 col-md-3">
                <label>Total Units (Estimated)</label>
                <input type="number" class="form-control" id="total_units" disabled/>
              </div>
              <div class="col-12 col-md-5 mt-3">
                <label>Attachment</label>
                <input type="file" class="form-control" name="att[]" multiple accept="image/png, image/jpeg, image/jpg, image/webp">
              </div>
              <div class="col-12 pb-sm-3 pb-md-0 text-end">
                <h3 class="font-weight-bold mb-0 text-5 text-primary">Net Amount</h3>
                <span>
                  <strong class="text-4 text-primary">PKR <span id="netTotal" class="text-4 text-danger">0.00</span></strong>
                </span>
              </div>
            </div>
          </div>
          <footer class="card-footer text-end">
            <a class="btn btn-danger" href="{{ route('pur-fgpos.index') }}">Discard</a>
            <button type="submit" class="btn btn-primary">Update</button>
          </footer>
        </section>
      </div>
    </div>
  </form>
</div>

<script>
  $(document).ready(function(){
    tableTotal();
    generateVoucher();
  });
  document.addEventListener('DOMContentLoaded', function () {

    let detailIndex = {{ count($po->details) }}; // Continue from existing count

    // Function to recalculate totals
    function recalcTotals() {
        let grandTotal = 0;
        document.querySelectorAll('.detail-row').forEach(row => {
            let qty = parseFloat(row.querySelector('.qty').value) || 0;
            let rate = parseFloat(row.querySelector('.rate').value) || 0;
            let total = qty * rate;
            row.querySelector('.total').value = total.toFixed(2);
            grandTotal += total;
        });
        document.getElementById('grand_total').value = grandTotal.toFixed(2);
    }

    // Function to regenerate challan code
    function regenerateChallan() {
        let challanParts = [];
        document.querySelectorAll('.detail-row').forEach((row, i) => {
            let prod = row.querySelector('.product_id');
            let varSel = row.querySelector('.variation_id');
            if (prod && prod.value) {
                let prodText = prod.options[prod.selectedIndex].text || '';
                let varText = varSel && varSel.value ? '-' + varSel.options[varSel.selectedIndex].text : '';
                challanParts.push(prodText + varText);
            }
        });
        document.getElementById('challan_code').value = challanParts.join(', ');
    }

    // Event: Qty or Rate change
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('qty') || e.target.classList.contains('rate')) {
            recalcTotals();
        }
    });

    // Event: Product/Variation change → regenerate challan
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('product_id') || e.target.classList.contains('variation_id')) {
            regenerateChallan();
        }
    });

    // Add Row
    document.getElementById('addRowBtn').addEventListener('click', function () {
        let tableBody = document.getElementById('detailsTableBody');
        let newRow = document.createElement('tr');
        newRow.classList.add('detail-row');
        newRow.innerHTML = `
            <td>
                <select name="details[${detailIndex}][product_id]" class="form-control product_id">
                    <option value="">Select Product</option>
                    @foreach($articles as $article)
                        <option value="{{ $article->id }}">{{ $article->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="details[${detailIndex}][variation_id]" class="form-control variation_id">
                    <option value="">Select Variation</option>
                    @foreach($attributes as $attr)
                        @foreach($attr->values as $val)
                            <option value="{{ $val->id }}">{{ $val->value }}</option>
                        @endforeach
                    @endforeach
                </select>
            </td>
            <td><input type="number" name="details[${detailIndex}][qty]" class="form-control qty" step="0.01"></td>
            <td><input type="number" name="details[${detailIndex}][rate]" class="form-control rate" step="0.01"></td>
            <td><input type="text" name="details[${detailIndex}][total]" class="form-control total" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRowBtn">X</button></td>
        `;
        tableBody.appendChild(newRow);
        detailIndex++;
    });

    // Remove Row
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('removeRowBtn')) {
            e.target.closest('tr').remove();
            recalcTotals();
            regenerateChallan();
        }
    });

    // Initial calc & challan regen
    recalcTotals();
    regenerateChallan();
});
</script>

@endsection

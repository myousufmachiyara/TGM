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
      {{-- PO header --}}
      <div class="col-12 col-md-12 mb-3">
        <section class="card">
          <header class="card-header d-flex justify-content-between">
            <h2 class="card-title">Edit PO</h2>
          </header>
          <div class="card-body">
            <div class="row">
              <div class="col-12 col-md-1 mb-3">
                <label>PO #</label>
                <input type="text" class="form-control" value="{{ $po->id }}" disabled/>
              </div>

              <div class="col-12 col-md-2">
                <label>Category<span class="text-danger">*</span></label>
                <select class="form-control" name="category_id" required>
                  <option value="" disabled>Select Category</option>
                  @foreach ($prodCat as $cat)
                    <option value="{{ $cat->id }}" @selected($po->category_id == $cat->id)>{{ $cat->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-12 col-md-2 mb-3">
                <label>Vendor Name</label>
                <select class="form-control" name="vendor_id" id="vendor_name" required>
                  <option value="" disabled>Select Vendor</option>
                  @foreach ($coa as $item)
                    <option value="{{ $item->id }}" @selected($po->vendor_id == $item->id)>{{ $item->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-12 col-md-2 mb-3">
                <label>Order Date</label>
                <input type="date" name="order_date" class="form-control" id="order_date" value="{{ $po->order_date }}" required/>
              </div>

              <div class="col-12 col-md-4 mb-3">
                <label>Item Name</label>
                <select multiple class="form-control" name="product_id[]" id="item_name" required>
                  <option value="" disabled>Select Item</option>
                  @foreach ($articles as $item)
                    <option value="{{ $item->id }}" @selected(in_array($item->id, $po->details->pluck('product_id')->toArray()))>
                      {{ $item->sku }}-{{ $item->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-12 col-md-1">
                <label>Variations</label>
                <button type="button" id="generate-variations-btn" class="btn btn-success d-block">Generate</button>
              </div>
            </div>

            {{-- Variations Table --}}
            <div class="col-12 col-md-6 mt-3">
              <table class="table table-bordered" id="variationsTable">
                <thead>
                  <tr>
                    <th>S.No</th>
                    <th>Item</th>
                    <th>Variation</th>
                    <th>Quantity</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody id="variationsTableBody">
                  @php $varIndex = 0; @endphp
                  @foreach ($po->details->whereNotNull('variation_id') as $detail)
                    <tr class="variation-row">
                      <td>{{ $loop->iteration }}</td>
                      <td>
                        <select name="item_order[{{ $varIndex }}][product_id]" class="form-control item-select">
                          <option value="">-- Select Item --</option>
                          @foreach ($articles as $item)
                            <option value="{{ $item->id }}" @selected($detail->product_id == $item->id)>
                              {{ $item->name }}
                            </option>
                          @endforeach
                        </select>
                      </td>
                      <td>
                        <select name="item_order[{{ $varIndex }}][variation_id]"
                                class="form-control variation-select"
                                data-selected="{{ $detail->variation_id }}">
                          <option value="">-- Select Variation --</option>
                          {{-- options loaded by JS --}}
                        </select>
                      </td>
                      <td>
                        <input type="number" onchange="tableTotal()" name="item_order[{{ $varIndex }}][qty]"
                          value="{{ $detail->qty }}" class="form-control variation-qty" step="1" min="0" />
                      </td>
                      <td>
                        <button type="button" class="btn btn-danger btn-sm delete-variation-row">Delete</button>
                      </td>
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
            <table class="table table-bordered">
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
                @foreach ($po->details as $i => $detail)
                  <tr data-selected-po="{{ $detail->po_id }}">
                    <td>
                      <select class="form-control fabric-select"
                              name="voucher_details[{{ $i }}][product_id]">
                        <option value="">Select Fabric</option>
                        @foreach ($fabrics as $fabric)
                          <option value="{{ $fabric->id }}"
                            data-unit="{{ $fabric->measurement_unit }}"
                            @selected($detail->product_id == $fabric->id)>
                            {{ $fabric->sku }} - {{ $fabric->name }}
                          </option>
                        @endforeach
                      </select>
                    </td>

                    <td>
                      <select class="form-control po-code-select"
                              name="voucher_details[{{ $i }}][po_id]">
                        <option value="">-- Select PO Code --</option>
                        @if ($detail->po_id)
                          <option value="{{ $detail->po_id }}" selected>
                            {{ $detail->po->po_code ?? 'Unknown' }}
                          </option>
                        @endif
                      </select>
                    </td>

                    <td><input type="text" name="voucher_details[{{ $i }}][description]"
                              class="form-control" value="{{ $detail->description }}"/></td>

                    <td><input type="number" step="any"
                              name="voucher_details[{{ $i }}][item_rate]"
                              value="{{ $detail->item_rate }}" class="form-control item-rate"/></td>

                    <td><input type="number" step="any"
                              name="voucher_details[{{ $i }}][qty]"
                              value="{{ $detail->qty }}" class="form-control item-qty"/></td>

                    <td><input type="number" name="voucher_details[{{ $i }}][width]"
                              class="form-control item-width" value="{{ $detail->width }}"/></td>

                    <td><input type="text" class="form-control item-unit" value="{{ $detail->unit }}" disabled/></td>

                    <td><input type="number" class="form-control item-total"
                              value="{{ $detail->qty * $detail->item_rate }}" disabled/></td>

                    <td>
                      <button type="button" class="btn btn-danger btn-xs remove-fabric-row">
                        <i class="fas fa-times"></i>
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            <div class="mt-2">
              <button type="button" id="addFabricRowBtn" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Add Fabric Row
              </button>
            </div>
          </div>
        </section>
      </div>

      {{-- Voucher (Challan) --}}
      <div class="col-12 col-md-5 mb-3">
        <section class="card">
          <header class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title mb-0">Voucher (Challan #)</h2>
            <button type="button" class="btn btn-danger" id="regenerateChallanBtn">Generate Challan</button>
            <input type="hidden" name="challan_code" id="challan_code" value="{{ $po->challan_code ?? '' }}">
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
                <input type="number" class="form-control" id="total_fabric_qty" disabled/>
              </div>
              <div class="col-12 col-md-3">
                <label>Total Fabric Amount</label>
                <input type="number" class="form-control" id="total_fabric_amount" disabled/>
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
  document.addEventListener('DOMContentLoaded', function () {
    let jobIndex = {{ $po->details->count() ?? 0 }};  // ✅ Prevents "undefined"
    let varIndex = {{ $po->details->whereNotNull('variation_id')->count() ?? 0 }};

    const fabricList = @json($fabricJs);
    const productList = @json($products);   
    const challanList = @json($po->challans ?? []); // already attached challans

    // --- Table bodies ---
    const jobBody = document.getElementById('jobTableBody');
    const fabricBody = document.getElementById('fabricTableBody');
    const challanBody = document.getElementById('challanTableBody');

    // --- Add new Job Row ---
    document.getElementById('addJobRow').addEventListener('click', function () {
        const row = `
            <tr>
                <td>
                    <select name="details[${jobIndex}][product_id]" class="form-control product-select" data-index="${jobIndex}">
                        <option value="">-- Select Product --</option>
                        ${products.map(p => `<option value="${p.id}">${p.name}</option>`).join('')}
                    </select>
                </td>
                <td>
                    <select name="details[${jobIndex}][variation_id]" class="form-control variation-select"></select>
                </td>
                <td><input type="number" name="details[${jobIndex}][qty]" class="form-control qty" value="0"></td>
                <td><input type="number" name="details[${jobIndex}][rate]" class="form-control rate" value="0"></td>
                <td><input type="number" name="details[${jobIndex}][total]" class="form-control total" readonly></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
            </tr>`;
        jobBody.insertAdjacentHTML('beforeend', row);
        jobIndex++;
    });

    // --- Add new Fabric Row ---
    document.getElementById('addFabricRow').addEventListener('click', function () {
        const row = `
            <tr>
                <td>
                    <select name="voucherDetails[${fabricIndex}][fabric_id]" class="form-control fabric-select" data-index="${fabricIndex}">
                        <option value="">-- Select Fabric --</option>
                        ${fabricList.map(f => `<option value="${f.id}">${f.name}</option>`).join('')}
                    </select>
                </td>
                <td>
                    <select name="voucherDetails[${fabricIndex}][purchase_id]" class="form-control po-code-select">
                        <option value="">-- Select PO Code --</option>
                    </select>
                </td>
                <td><input type="number" name="voucherDetails[${fabricIndex}][qty]" class="form-control qty" value="0"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
            </tr>`;
        fabricBody.insertAdjacentHTML('beforeend', row);
        fabricIndex++;
    });

    // --- Dynamic Event Delegation ---
    document.body.addEventListener('change', function(e) {

        // 1. When Product changes → fetch variations
        if (e.target.classList.contains('product-select')) {
            let productId = e.target.value;
            let row = e.target.closest('tr');
            let variationSelect = row.querySelector('.variation-select');
            variationSelect.innerHTML = `<option value="">Loading...</option>`;
            if (productId) {
                fetch(`/items/${productId}/variations`)
                    .then(res => res.json())
                    .then(data => {
                        variationSelect.innerHTML = `<option value="">-- Select Variation --</option>`;
                        data.forEach(v => {
                            variationSelect.innerHTML += `<option value="${v.id}">${v.name}</option>`;
                        });
                    });
            }
        }

        // 2. When Fabric changes → fetch PO Codes (purchase IDs)
        if (e.target.classList.contains('fabric-select')) {
            let fabricId = e.target.value;
            let row = e.target.closest('tr');
            let poCodeSelect = row.querySelector('.po-code-select');
            poCodeSelect.innerHTML = `<option value="">Loading...</option>`;
            if (fabricId) {
                fetch(`/fabrics/${fabricId}/purchases`)
                    .then(res => res.json())
                    .then(data => {
                        poCodeSelect.innerHTML = `<option value="">-- Select PO Code --</option>`;
                        data.forEach(po => {
                            poCodeSelect.innerHTML += `<option value="${po.id}">${po.code}</option>`;
                        });
                    });
            }
        }

        // 3. Qty / Rate change → recalc total
        if (e.target.classList.contains('qty') || e.target.classList.contains('rate')) {
            let row = e.target.closest('tr');
            let qty = parseFloat(row.querySelector('.qty').value) || 0;
            let rate = parseFloat(row.querySelector('.rate').value) || 0;
            row.querySelector('.total').value = (qty * rate).toFixed(2);
            recalcSummary();
        }
    });

    // --- Remove Row ---
    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
            recalcSummary();
        }
    });

    // --- Summary Recalculation ---
    function recalcSummary() {
        let totalQty = 0, totalAmount = 0;
        document.querySelectorAll('#jobTableBody tr').forEach(row => {
            totalQty += parseFloat(row.querySelector('.qty')?.value || 0);
            totalAmount += parseFloat(row.querySelector('.total')?.value || 0);
        });
        document.getElementById('summaryQty').textContent = totalQty;
        document.getElementById('summaryAmount').textContent = totalAmount.toFixed(2);
    }

    // --- Pre-fill Challan (if editing) ---
    if (challanList.length > 0) {
        challanList.forEach((c, idx) => {
            const row = `
                <tr>
                    <td><input type="text" name="challans[${idx}][no]" value="${c.no}" class="form-control"></td>
                    <td><input type="text" name="challans[${idx}][date]" value="${c.date}" class="form-control"></td>
                    <td><input type="text" name="challans[${idx}][remarks]" value="${c.remarks}" class="form-control"></td>
                </tr>`;
            challanBody.insertAdjacentHTML('beforeend', row);
        });
    }

    // initial summary
    recalcSummary();
  });
</script>
@endsection

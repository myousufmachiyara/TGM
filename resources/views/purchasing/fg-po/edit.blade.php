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
                <input type="date" name="order_date" class="form-control" id="order_date" value="{{ $po->order_date }}" required/>
              </div>

              <div class="col-12 col-md-4 mb-3">
                <label>Item Name</label>
                <select multiple data-plugin-selecttwo class="form-control select2-js" name="product_id[]" id="item_name" required>
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
                    <th>SKU</th>
                    <th>Quantity</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody id="variationsTableBody">
                  @php $varIndex = 0; @endphp
                  @foreach ($po->details->whereNotNull('variation_id') as $detail)
                    <tr class="variation-row">
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $detail->product->name }}</td>
                      <td>
                        <input type="hidden" name="item_order[{{ $varIndex }}][product_id]" value="{{ $detail->product_id }}">
                        <input type="hidden" name="item_order[{{ $varIndex }}][variation_id]" value="{{ $detail->variation_id }}">
                        <input type="hidden" name="item_order[{{ $varIndex }}][sku]" value="{{ $detail->variation->sku }}" class="variation-sku">
                        {{ $detail->variation->sku }}
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

      {{-- Fabric Details (PurPOTbleBody used by voucher/generate functions) --}}
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
                  <tr class="fabric-row" data-index="{{ $fabIndex }}">
                      <td>
                        <select class="form-control select2-js fabric-select" name="voucher_details[{{ $fabIndex }}][product_id]" data-index="{{ $fabIndex }}">
                            <option value="" disabled>Select Fabric</option>
                            @foreach ($fabrics as $item)
                                <option value="{{ $item->id }}" data-unit="{{ $item->measurement_unit }}" @selected($fabric->product_id == $item->id)>
                                    {{ $item->sku }} - {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                      </td>

                      <td>
                        <select class="form-control select2-js po-id-select" name="voucher_details[{{ $fabIndex }}][po_id]" data-index="{{ $fabIndex }}">
                            <option value="0" @selected($fabric->po_id == 0)>Select PO Id</option>
                            @if($fabric->po_id && $fabric->po_code)
                                <option value="{{ $fabric->po_id }}" selected>{{ $fabric->po_code }}</option>
                            @endif
                        </select>
                      </td>

                      <td><input type="text" name="voucher_details[{{ $fabIndex }}][description]" value="{{ $fabric->description }}" class="form-control description-input" /></td>

                      <td>
                        <input type="number" name="voucher_details[{{ $fabIndex }}][item_rate]" class="form-control item-rate" data-index="{{ $fabIndex }}" step="any" value="{{ $fabric->rate }}" required />
                      </td>

                      <td>
                        <input type="number" name="voucher_details[{{ $fabIndex }}][qty]" class="form-control item-qty" data-index="{{ $fabIndex }}" step="any" value="{{ $fabric->qty }}" required />
                      </td>

                      <td><input type="number" id="item_width_{{ $fabIndex }}" name="voucher_details[{{ $fabIndex }}][width]" class="form-control item-width" value="{{ $fabric->width }}" /></td>

                      <td><input type="text" id="item_unit_{{ $fabIndex }}" class="form-control item-unit" value="{{ $fabric->product->measurement_unit }}" disabled /></td>

                      <td><input type="number" id="item_total_{{ $fabIndex }}" name="voucher_details[{{ $fabIndex }}][total]" class="form-control item-total" value="{{ $fabric->rate * $fabric->qty }}" disabled /></td>

                      <td>
                          <button type="button" class="btn btn-danger btn-xs remove-fabric-row"><i class="fas fa-times"></i></button>
                      </td>
                  </tr>
                  @php $fabIndex++; @endphp
                @endforeach

                {{-- If no rows exist, show one blank row --}}
                @if($po->voucherDetails->count() == 0)
                  <tr class="fabric-row" data-index="0">
                      <td>
                        <select class="form-control select2-js fabric-select" name="voucher_details[0][product_id]" data-index="0">
                            <option value="" disabled selected>Select Fabric</option>
                            @foreach ($fabrics as $item)
                                <option value="{{ $item->id }}" data-unit="{{ $item->measurement_unit }}">{{ $item->sku }} - {{ $item->name }}</option>
                            @endforeach
                        </select>
                      </td>
                      <td>
                        <select class="form-control select2-js po-id-select" name="voucher_details[0][po_id]" data-index="0">
                            <option value="0" selected>Select PO Id</option>
                        </select>
                      </td>
                      <td><input type="text" name="voucher_details[0][description]" class="form-control description-input" /></td>
                      <td><input type="number" name="voucher_details[0][item_rate]" class="form-control item-rate" data-index="0" step="any" /></td>
                      <td><input type="number" name="voucher_details[0][qty]" class="form-control item-qty" data-index="0" step="any" /></td>
                      <td><input type="number" name="voucher_details[0][width]" class="form-control item-width" /></td>
                      <td><input type="text" class="form-control item-unit" disabled /></td>
                      <td><input type="number" name="voucher_details[0][total]" class="form-control item-total" disabled /></td>
                      <td><button type="button" class="btn btn-danger btn-xs remove-fabric-row"><i class="fas fa-times"></i></button></td>
                  </tr>
                @endif
              </tbody>
            </table>

            <div class="mt-2">
              <button type="button" id="addFabricRowBtn" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add Fabric Row</button>
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

{{-- Data for JS --}}
@php
  $fabricJs = $fabrics->map(fn($f)=> ['id'=>$f->id,'sku'=>$f->sku,'name'=> ($f->sku . ' - ' . $f->name),'unit'=>$f->measurement_unit]);
  $articleJs = $articles->map(fn($a)=> ['id'=>$a->id,'sku'=>$a->sku,'name'=> ($a->sku . ' - ' . $a->name)]);
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
  // indexes
  let fabIndex = {{ $po->voucherDetails->count() }};
  let varIndex = {{ $po->details->whereNotNull('variation_id')->count() }};

  const fabricList = @json($fabricJs);
  const articleList = @json($articleJs);

  const fabricBody = document.getElementById('PurPOTbleBody');
  const variationsBody = document.getElementById('variationsTableBody');
  const addFabricRowBtn = document.getElementById('addFabricRowBtn');
  const regenerateChallanBtn = document.getElementById('regenerateChallanBtn');
  const challanInput = document.getElementById('challan_code');
  const netTotalSpan = document.getElementById('netTotal');

  // small helper
  const fmt = n => Number(n || 0).toFixed(2);

  // tableTotal: recalc rows and summary
  function tableTotal() {
    let totalFabricQty = 0;
    let totalFabricAmount = 0;
    let totalUnits = 0;

    // fabric rows
    document.querySelectorAll('#PurPOTbleBody tr').forEach(row => {
      const qtyInput = row.querySelector('input[name*="[qty]"]');
      const rateInput = row.querySelector('input[name*="[item_rate]"]');
      if (!qtyInput || !rateInput) return;
      const qty = parseFloat(qtyInput.value) || 0;
      const rate = parseFloat(rateInput.value) || 0;
      totalFabricQty += qty;
      totalFabricAmount += qty * rate;
      const totalInput = row.querySelector('input[name*="[total]"]') || row.querySelector('.item-total');
      if (totalInput) totalInput.value = fmt(qty * rate);
      // ensure unit input exists (from selected option)
      const prodSelect = row.querySelector('select[name*="[product_id]"]');
      if (prodSelect) {
        const unit = prodSelect.selectedOptions?.[0]?.dataset?.unit || '';
        const unitInput = row.querySelector('.item-unit');
        if (unitInput) unitInput.value = unit;
      }
    });

    // variation rows => totalUnits
    document.querySelectorAll('#variationsTableBody tr').forEach(row => {
      const qtyInput = row.querySelector('input[name*="[qty]"]') || row.querySelector('.variation-qty');
      if (!qtyInput) return;
      const units = parseFloat(qtyInput.value) || 0;
      totalUnits += units;
    });

    if (document.getElementById('total_fabric_qty')) document.getElementById('total_fabric_qty').value = fmt(totalFabricQty);
    if (document.getElementById('total_fabric_amount')) document.getElementById('total_fabric_amount').value = fmt(totalFabricAmount);
    if (document.getElementById('total_units')) document.getElementById('total_units').value = fmt(totalUnits);
    if (netTotalSpan) netTotalSpan.textContent = fmt(totalFabricAmount);
  }

  // build fabric select element
  function buildFabricSelect(name, selectedId = null) {
    const sel = document.createElement('select');
    sel.name = name;
    sel.className = 'form-control select2-js';
    const emptyOpt = new Option('Select Fabric','');
    emptyOpt.disabled = true;
    sel.appendChild(emptyOpt);
    fabricList.forEach(f => {
      const opt = new Option(f.name, f.id);
      opt.dataset.unit = f.unit ?? '';
      if (selectedId && selectedId == f.id) opt.selected = true;
      sel.appendChild(opt);
    });
    return sel;
  }

  // add new fabric row (data optional)
  function addFabricRow(data = {}) {
    const idx = fabIndex++;
    const tr = document.createElement('tr');
    tr.classList.add('fabric-row');
    tr.dataset.index = idx;

    // product select
    const tdProd = document.createElement('td');
    const sel = buildFabricSelect(`voucher_details[${idx}][product_id]`, data.product_id ?? null);
    sel.classList.add('fabric-select');
    tdProd.appendChild(sel);

    // PO select
    const tdPo = document.createElement('td');
    const poSel = document.createElement('select');
    poSel.name = `voucher_details[${idx}][po_id]`;
    poSel.className = 'form-control po-id-select';
    poSel.appendChild(new Option('Select PO Id', 0));
    tdPo.appendChild(poSel);

    // description
    const tdDesc = document.createElement('td');
    const desc = document.createElement('input');
    desc.type = 'text'; desc.name = `voucher_details[${idx}][description]`; desc.className = 'form-control description-input';
    desc.value = data.description ?? '';
    tdDesc.appendChild(desc);

    // rate
    const tdRate = document.createElement('td');
    const rate = document.createElement('input');
    rate.type = 'number'; rate.step = 'any'; rate.name = `voucher_details[${idx}][item_rate]`; rate.className = 'form-control item-rate'; rate.dataset.index = idx;
    rate.value = data.item_rate ?? '';
    tdRate.appendChild(rate);

    // qty
    const tdQty = document.createElement('td');
    const qty = document.createElement('input');
    qty.type = 'number'; qty.step = 'any'; qty.name = `voucher_details[${idx}][qty]`; qty.className = 'form-control item-qty'; qty.dataset.index = idx;
    qty.value = data.qty ?? '';
    tdQty.appendChild(qty);

    // width
    const tdWidth = document.createElement('td');
    const width = document.createElement('input');
    width.type = 'number'; width.name = `voucher_details[${idx}][width]`; width.className = 'form-control item-width';
    width.value = data.width ?? '';
    tdWidth.appendChild(width);

    // unit disabled
    const tdUnit = document.createElement('td');
    const unitIn = document.createElement('input');
    unitIn.type = 'text'; unitIn.name = `voucher_details[${idx}][unit]`; unitIn.className = 'form-control item-unit'; unitIn.disabled = true;
    unitIn.value = data.unit ?? '';
    tdUnit.appendChild(unitIn);

    // total
    const tdTotal = document.createElement('td');
    const totalIn = document.createElement('input');
    totalIn.type = 'number'; totalIn.name = `voucher_details[${idx}][total]`; totalIn.className = 'form-control item-total'; totalIn.disabled = true;
    totalIn.value = fmt((data.item_rate ?? 0) * (data.qty ?? 0));
    tdTotal.appendChild(totalIn);

    // action
    const tdAction = document.createElement('td');
    const btnRemove = document.createElement('button');
    btnRemove.type = 'button'; btnRemove.className = 'btn btn-danger btn-xs remove-fabric-row'; btnRemove.innerHTML = '<i class="fas fa-times"></i>';
    tdAction.appendChild(btnRemove);

    tr.appendChild(tdProd); tr.appendChild(tdPo); tr.appendChild(tdDesc); tr.appendChild(tdRate);
    tr.appendChild(tdQty); tr.appendChild(tdWidth); tr.appendChild(tdUnit); tr.appendChild(tdTotal); tr.appendChild(tdAction);

    fabricBody.appendChild(tr);

    // init select2 if available
    if (window.jQuery && typeof window.jQuery.fn.select2 === 'function') {
      try { window.jQuery(sel).select2({ width: '100%' }); } catch(e){}
      try { window.jQuery(poSel).select2({ width: '100%' }); } catch(e){}
    }

    tableTotal();
  }

  // remove fabric row
  function removeFabricRow(button) {
    const row = button.closest('tr');
    if (!row) return;
    row.remove();
    tableTotal();
    regenerateChallan();
  }

  // add variation row
  function addVariationRow(data = {}) {
    const idx = varIndex++;
    const tr = document.createElement('tr');
    tr.classList.add('variation-row');

    const snoTd = document.createElement('td'); snoTd.textContent = variationsBody.querySelectorAll('tr').length + 1;
    const itemTd = document.createElement('td'); itemTd.textContent = data.product_name ?? '';
    const skuTd = document.createElement('td');
    const h1 = document.createElement('input'); h1.type='hidden'; h1.name = `item_order[${idx}][product_id]`; h1.value = data.product_id ?? ''; h1.className='variation-product-id';
    const h2 = document.createElement('input'); h2.type='hidden'; h2.name = `item_order[${idx}][variation_id]`; h2.value = data.variation_id ?? '';
    const h3 = document.createElement('input'); h3.type='hidden'; h3.name = `item_order[${idx}][sku]`; h3.value = data.sku ?? ''; h3.className='variation-sku';
    const span = document.createElement('span'); span.textContent = data.sku ?? '';
    skuTd.appendChild(h1); skuTd.appendChild(h2); skuTd.appendChild(h3); skuTd.appendChild(span);

    const qtyTd = document.createElement('td'); const qtyIn = document.createElement('input'); qtyIn.type='number'; qtyIn.name = `item_order[${idx}][qty]`; qtyIn.className='form-control variation-qty'; qtyIn.step='1'; qtyIn.min='0'; qtyIn.value = data.qty ?? ''; qtyTd.appendChild(qtyIn);

    const actionTd = document.createElement('td'); const delBtn = document.createElement('button'); delBtn.type='button'; delBtn.className='btn btn-danger btn-sm delete-variation-row'; delBtn.textContent='Delete'; actionTd.appendChild(delBtn);

    tr.appendChild(snoTd); tr.appendChild(itemTd); tr.appendChild(skuTd); tr.appendChild(qtyTd); tr.appendChild(actionTd);
    variationsBody.appendChild(tr);
    tableTotal();
  }

  // regenerateChallan: builds challan voucher showing fabric details (same layout as generateVoucher)
  function regenerateChallan() {
    if (!challanInput) return;

    // Collect fabric rows (PurPOTbleBody)
    const rows = Array.from(document.querySelectorAll('#PurPOTbleBody tr'));
    const items = [];

    rows.forEach((row, key) => {
      const prodSel = row.querySelector('select[name*="[product_id]"]');
      const fabricText = prodSel ? (prodSel.selectedOptions[0]?.textContent || '').trim() : '';
      const description = row.querySelector(`input[name='voucher_details[${row.dataset.index ?? key}][description]']`)?.value || row.querySelector('.description-input')?.value || '';
      const rate = parseFloat(row.querySelector(`input[name='voucher_details[${row.dataset.index ?? key}][item_rate]']`)?.value || row.querySelector('.item-rate')?.value || 0) || 0;
      const qty = parseFloat(row.querySelector(`input[name='voucher_details[${row.dataset.index ?? key}][qty]']`)?.value || row.querySelector('.item-qty')?.value || 0) || 0;
      const unit = row.querySelector('.item-unit')?.value || (prodSel ? prodSel.selectedOptions[0]?.dataset?.unit || '' : '');
      const width = row.querySelector('.item-width')?.value || '';
      const total = parseFloat(row.querySelector('.item-total')?.value || (qty * rate) || 0) || 0;

      // push only if meaningful
      if (fabricText || qty || rate) {
        items.push({ fabric: fabricText, description, rate: fmt(rate), quantity: fmt(qty), unit, width, total: fmt(total) });
      }
    });

  
    // build voucher HTML
    const vendorName = document.querySelector("#vendor_name")?.selectedOptions?.[0]?.textContent || '';
    const date = document.querySelector('#order_date')?.value || '';

    const totalAmount = items.reduce((s, it) => s + (parseFloat(it.total) || 0), 0);

    const voucherHTML = `
      <div class="border p-3 mt-3">
        <h3 class="text-center text-dark mb-0">Delivery Challan</h3>
        <hr>
        <div class="d-flex justify-content-between text-dark">
          <p><strong>Vendor:</strong> ${vendorName}</p>
          <p><strong>Date:</strong> ${date}</p>
        </div>
        <table class="table table-bordered mt-3">
          <thead class="table-light">
            <tr>
              <th>Fabric</th>
              <th>Description</th>
              <th>Quantity</th>
              <th>Rate</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            ${items.map(it => `
              <tr>
                <td>${it.fabric}</td>
                <td>${it.description}</td>
                <td>${it.quantity} ${it.unit || ''}</td>
                <td>${it.rate}</td>
                <td>${it.total}</td>
              </tr>`).join('')}
          </tbody>
        </table>
        <h4 class="text-end text-dark"><strong>Total Amount:</strong> ${fmt(totalAmount)} PKR</h4>
        <input type="hidden" name="voucher_amount" value="${fmt(totalAmount)}">
        <div class="d-flex justify-content-between mt-4">
          <div>
            <p class="text-dark"><strong>Authorized By:</strong></p>
            <p>________________________</p>
          </div>
          <div>
            <p class="text-dark"><strong>Received By:</strong></p>
            <p>________________________</p>
          </div>
        </div>
      </div>
    `;

    const voucherContainer = document.getElementById('voucher-container');
    if (voucherContainer) voucherContainer.innerHTML = voucherHTML;
  }

  // generateVoucher (Payment Voucher layout) - kept similar to earlier sample
  function generateVoucher() {
    const voucherContainer = document.getElementById("voucher-container");
    if (!voucherContainer) return;
    voucherContainer.innerHTML = "";

    const vendorName = document.querySelector("#vendor_name")?.selectedOptions?.[0]?.textContent || "";
    const date = document.querySelector('#order_date')?.value || "";
    const purchaseOrderNo = "FGPO-" + new Date().getTime();

    let items = [];
    const rows = Array.from(document.querySelectorAll("#PurPOTbleBody tr"));
    rows.forEach((row, key) => {
        const selects = row.querySelectorAll("select");
        const fabric = selects[0]?.selectedOptions?.[0]?.textContent || "";

        const description = row.querySelector(`input[name='voucher_details[${row.dataset.index ?? key}][description]']`)?.value || '';
        const rate = row.querySelector(`input[name='voucher_details[${row.dataset.index ?? key}][item_rate]']`)?.value || '';
        const quantity = row.querySelector(`input[name='voucher_details[${row.dataset.index ?? key}][qty]']`)?.value || '';
        const unit = row.querySelector('.item-unit')?.value || '';
        const total = row.querySelector(`input[name='voucher_details[${row.dataset.index ?? key}][total]']`)?.value || (parseFloat(rate||0)*parseFloat(quantity||0)) || '';

        if (fabric || quantity || rate) {
            items.push({ fabric, description, rate, quantity, unit, total });
        }
    });

    const totalAmount = items.reduce((sum, item) => sum + parseFloat(item.total || 0), 0);

    const voucherHTML = `
      <div class="border p-3 mt-3">
        <h3 class="text-center text-dark mb-0">Payment Voucher</h3>
        <hr>
        <div class="d-flex justify-content-between text-dark">
          <p><strong>Vendor:</strong> ${vendorName}</p>
          <p><strong>PO No:</strong> ${purchaseOrderNo}</p>
          <p><strong>Date:</strong> ${date}</p>
        </div>
        <table class="table table-bordered mt-3">
          <thead class="table-light">
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
        <h4 class="text-end text-dark"><strong>Total Amount:</strong> ${fmt(totalAmount)} PKR</h4>
        <input type="hidden" name="voucher_amount" value="${fmt(totalAmount)}">
        <div class="d-flex justify-content-between mt-4">
            <div>
                <p class="text-dark"><strong>Authorized By:</strong></p>
                <p>________________________</p>
            </div> 
            <div>
                <p class="text-dark"><strong>Received By:</strong></p>
                <p>________________________</p>
            </div>
        </div>
      </div>
    `;
    voucherContainer.innerHTML = voucherHTML;
  }

  // event bindings --------------------------------------------------------

  // Add Fabric Row button
  if (addFabricRowBtn) {
    addFabricRowBtn.addEventListener('click', () => addFabricRow());
  }

  // Generate variations from item_name select
  const generateBtn = document.getElementById('generate-variations-btn');
  if (generateBtn) {
    generateBtn.addEventListener('click', function () {
      const itemSelect = document.getElementById('item_name');
      if (!itemSelect) return;
      const selected = Array.from(itemSelect.selectedOptions).map(o => o.value);
      selected.forEach(pid => {
        const art = articleList.find(a => String(a.id) === String(pid));
        const product_name = art ? art.name : '';
        const sku = art ? art.sku : '';
        addVariationRow({ product_id: pid, product_name, sku, qty: 0 });
      });
      tableTotal();
      regenerateChallan();
    });
  }

  // Delegated changes: fabric select -> set unit
  fabricBody?.addEventListener('change', function (e) {
    const target = e.target;
    if (target && target.matches('select[name^="voucher_details"]')) {
      const unit = target.selectedOptions?.[0]?.dataset?.unit || '';
      const row = target.closest('tr');
      if (row) {
        const unitInput = row.querySelector('.item-unit');
        if (unitInput) unitInput.value = unit;
      }
    }
  });

  // Delegated input: recalc row total and overall
  fabricBody?.addEventListener('input', function (e) {
    const t = e.target;
    if (!t) return;
    const row = t.closest('tr');
    if (!row) return;
    if (t.classList.contains('item-qty') || t.classList.contains('item-rate') || t.name?.includes('[qty]') || t.name?.includes('[item_rate]')) {
      const qty = parseFloat(row.querySelector('input[name*="[qty]"]')?.value || 0) || 0;
      const rate = parseFloat(row.querySelector('input[name*="[item_rate]"]')?.value || 0) || 0;
      const totalInput = row.querySelector('input[name*="[total]"]') || row.querySelector('.item-total');
      if (totalInput) totalInput.value = fmt(qty * rate);
      tableTotal();
    }
  });

  // Delegated clicks: remove fabric row, delete variation row
  document.addEventListener('click', function (e) {
    const btn = e.target;

    // remove-fabric-row (button may be <button> or inner <i>)
    if (btn.closest && btn.closest('.remove-fabric-row')) {
      removeFabricRow(btn.closest('.remove-fabric-row'));
    }

    // direct remove-fabric-row button
    if (btn.classList && btn.classList.contains('remove-fabric-row')) {
      removeFabricRow(btn);
    }

    // delete-variation-row
    if (btn.closest && btn.closest('.delete-variation-row')) {
      const b = btn.closest('.delete-variation-row');
      const row = b.closest('tr');
      if (row) row.remove();
      tableTotal();
      regenerateChallan();
    }
  });

  // variations quantity change -> recalc
  variationsBody?.addEventListener('input', function (e) {
    if (e.target && (e.target.name?.includes('[qty]') || e.target.classList.contains('variation-qty'))) {
      tableTotal();
      regenerateChallan();
    }
  });

  // regenerateChallan button
  regenerateChallanBtn?.addEventListener('click', function () {
    regenerateChallan();
  });

  // If you want a preview voucher (payment voucher) separately
  // You can call generateVoucher() from a button or on any change

  // Initialize select2 for existing selects if available
  if (window.jQuery && typeof window.jQuery.fn.select2 === 'function') {
    try {
      window.jQuery('.select2-js').select2({ width: '100%' });
    } catch (e) { /* ignore */ }
  }

  // initial calc & preview
  tableTotal();
  regenerateChallan();
});
</script>
@endsection

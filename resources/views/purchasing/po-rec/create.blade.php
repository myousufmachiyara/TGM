@extends('layouts.app')

@section('title', 'Purchasing | PO Receiving')

@section('content')
<div class="row">
  <form action="{{ route('pur-po-rec.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    @if ($errors->has('error'))
      <strong class="text-danger">{{ $errors->first('error') }}</strong>
    @endif

    <div class="row">
      <div class="col-12 mb-4">
        <section class="card">
          <header class="card-header d-flex justify-content-between">
            <h2 class="card-title">PO Receiving</h2>
          </header>

          <div class="card-body">
            <div class="row mb-4">
              <div class="col-md-2">
                <label>GRN #</label>
                <input type="text" class="form-control" placeholder="Auto" disabled />
              </div>
              <div class="col-md-2 mb-3">
                <label>PO #</label>
                <input type="text" class="form-control" value="PO-{{ $purpo->id }}" disabled />
                <input type="hidden" name="po_id" value="{{ $purpo->id }}" />
              </div>
              <div class="col-md-2">
                <label>Receiving Date</label>
                <input type="date" name="rec_date" class="form-control" value="{{ date('Y-m-d') }}" required />
              </div>
            </div>
          </div>
        </section>
      </div>

      <div class="col-12 mb-4">
        <section class="card">
          <header class="card-header">
            <h2 class="card-title">Item Details</h2>
          </header>

          <div class="card-body">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Ordered Qty</th>
                  <th>Received</th>
                  <th>Remaining</th>
                  <th>Receiving Now</th>
                  <th>Rate</th>
                </tr>
              </thead>
              <tbody>
                @foreach($purpo->details as $detail)
                  @php
                    $ordered = number_format($detail->item_qty, 2);
                    $received = number_format($detail->total_received ?? 0, 2);
                    $remaining = number_format($detail->remaining_qty ?? ($detail->item_qty - $detail->total_received), 2);
                  @endphp
                  <tr class="receiving-row">
                    <td>{{ $detail->product_name }}</td>
                    <td>{{ $ordered }}</td>
                    <td>{{ $received }}</td>
                    <td>{{ $remaining }}</td>
                    <td>
                      <input type="number" name="received_qty[{{ $detail->id }}]" class="form-control" placeholder="Qty" step="0.01" min="0">
                    </td>
                    <td>
                      <input type="number" name="received_rate[{{ $detail->id }}]" class="form-control" placeholder="Rate" step="0.01" min="0">
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>

            <footer class="card-footer text-end mt-2">
              <div class="row mt-2 mb-4">
                <div class="col-md-3 offset-md-6">
                  <label><strong>Total Receiving Quantity</strong></label>
                  <input type="number" id="total_receiving_qty" class="form-control" placeholder="0.00" readonly />
                </div>

                <div class="col-md-3">
                  <label><strong>Total Bill</strong></label>
                  <input type="number" id="total_bill" class="form-control" placeholder="0.00" readonly />
                </div>
              </div>
              <a class="btn btn-danger" href="{{ route('pur-pos.index') }}">Discard</a>
              <button type="submit" class="btn btn-primary">Receive</button>
            </footer>
          </div>
        </section>
      </div>
    </div>
  </form>
</div>
<script>
  function updateSummary() {
    let totalQty = 0;
    let totalBill = 0;

    document.querySelectorAll('tr.receiving-row').forEach(row => {
      const qtyInput = row.querySelector('input[name^="received_qty"]');
      const rateInput = row.querySelector('input[name^="received_rate"]');

      const qty = parseFloat(qtyInput?.value) || 0;
      const rate = parseFloat(rateInput?.value) || 0;

      totalQty += qty;
      totalBill += qty * rate;
    });

    document.getElementById('total_receiving_qty').value = totalQty.toFixed(2);
    document.getElementById('total_bill').value = totalBill.toFixed(2);
  }

  function bindInputListeners() {
    document.querySelectorAll('input[name^="received_qty"], input[name^="received_rate"]').forEach(input => {
      input.removeEventListener('input', updateSummary); // avoid multiple bindings
      input.addEventListener('input', updateSummary);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    bindInputListeners();
    updateSummary();
  });

  // Call this after adding any new row dynamically
  function onRowAdded() {
    bindInputListeners();
    updateSummary();
  }
</script>


@endsection

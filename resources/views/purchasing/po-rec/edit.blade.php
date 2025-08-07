@extends('layouts.app')
@section('title','Purchasing | Edit PO Receiving')
@section('content')
<div class="row">
<form action="{{ route('pur-po-rec.update',$rec->id) }}" method="POST" enctype="multipart/form-data">
  @csrf @method('PUT')
  @if($errors->has('error'))
    <strong class="text-danger">{{ $errors->first('error') }}</strong>
  @endif
  <div class="col-12 mb-4"><section class="card">
    <header class="card-header d-flex justify-content-between">
      <h2 class="card-title">Edit PO Receiving</h2>
    </header>
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-md-2">
          <label>GRN #</label><input type="text" class="form-control" value="GRN-{{ $rec->id }}" disabled>
        </div>
        <div class="col-md-2 mb-3">
          <label>PO #</label><input type="text" class="form-control" value="PO-{{ $purpo->id }}" disabled>
          <input type="hidden" name="po_id" value="{{ $purpo->id }}">
        </div>
        <div class="col-md-2">
          <label>Receiving Date</label>
          <input type="date" name="rec_date" class="form-control" value="{{ \Carbon\Carbon::parse($rec->rec_date)->toDateString() }}" required>
        </div>
      </div>
    </div></section></div>

  <div class="col-12 mb-4"><section class="card">
    <header class="card-header"><h2 class="card-title">Item Details</h2></header>
    <div class="card-body">
      <table class="table table-bordered">
        <thead><tr>
          <th>Item</th><th>Ordered Qty</th><th>Received</th><th>Remaining</th>
          <th>Receiving Now</th><th>Rate</th></tr></thead>
        <tbody>
          @foreach($purpo->details as $d)
          <tr class="receiving-row">
            <td>{{ $d->product_name }}</td>
            <td>{{ number_format($d->item_qty,2) }}</td>
            <td>{{ number_format($d->total_received ?? 0,2) }}</td>
            <td>{{ number_format($d->remaining_qty,2) }}</td>
            <td><input type="number" name="received_qty[{{ $d->id }}]"
                       class="form-control" step="0.01" min="0"
                       value="{{ old('received_qty.'.$d->id, $d->received_in_this) }}"></td>
            <td><input type="number" name="received_rate[{{ $d->id }}]"
                       class="form-control" step="0.01" min="0"
                       value="{{ old('received_rate.'.$d->id, $d->rate_in_this) }}"></td>
          </tr>
          @endforeach
        </tbody>
      </table>

      <div class="row mt-2 mb-4">
        <div class="col-md-3 offset-md-6">
          <label><strong>Total Receiving Quantity</strong></label>
          <input type="number" id="total_receiving_qty" class="form-control" readonly>
        </div>
        <div class="col-md-3">
          <label><strong>Total Bill</strong></label>
          <input type="number" id="total_bill" class="form-control" readonly>
        </div>
      </div>

      <footer class="card-footer text-end">
        <a href="{{ route('pur-pos.index') }}" class="btn btn-danger">Discard</a>
        <button type="submit" class="btn btn-primary">Update</button>
      </footer>
    </div>
  </section></div>
</form></div>

<script>
  function updateSummary() {
    let totalQty = 0, totalBill = 0;
    document.querySelectorAll('tr.receiving-row').forEach(r => {
      const q = parseFloat(r.querySelector('input[name^="received_qty"]').value) || 0;
      const rate = parseFloat(r.querySelector('input[name^="received_rate"]').value) || 0;
      totalQty += q;
      totalBill += q * rate;
    });
    document.getElementById('total_receiving_qty').value = totalQty.toFixed(2);
    document.getElementById('total_bill').value = totalBill.toFixed(2);
  }

  function bindInputs() {
    document.querySelectorAll('input[name^="received_qty"], input[name^="received_rate"]').forEach(inp => {
      inp.removeEventListener('input', updateSummary);
      inp.addEventListener('input', updateSummary);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    bindInputs();
    updateSummary();
  });
</script>
@endsection

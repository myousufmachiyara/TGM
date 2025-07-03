@extends('layouts.app')

@section('title', 'Purchasing | PO Receiving')

@section('content')
<div class="row">
  <form action="{{ route('pur-pos.store-rec') }}" method="POST" enctype="multipart/form-data">
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
                  <tr>
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
              <a class="btn btn-danger" href="{{ route('pur-pos.index') }}">Discard</a>
              <button type="submit" class="btn btn-primary">Receive</button>
            </footer>
          </div>
        </section>
      </div>
    </div>
  </form>
</div>
@endsection

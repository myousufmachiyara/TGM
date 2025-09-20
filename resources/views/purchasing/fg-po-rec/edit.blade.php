@extends('layouts.app')

@section('title', 'Edit FGPO Receiving')

@section('content')
<div class="row">
  <form action="{{ route('pur-fgpo-rec.update', $receiving->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="col-12 mb-4">
      <section class="card">
        <header class="card-header">
          <h2 class="card-title">FGPO Receiving</h2>
        </header>
        <div class="card-body">
          <div class="row mb-4">
            <div class="col-12 col-md-2">
              <label>GRN #</label>
              <input type="text" class="form-control" placeholder="GRN #" value="{{ $receiving->id }}" disabled/>
            </div>
            <div class="col-12 col-md-2 mb-3">
              <label>PO #</label>
              <input type="text" class="form-control" value="FGPO-{{$receiving->fgpo_id}}" disabled/>
            </div>
            <div class="col-12 col-md-2">
              <label>Receiving Date</label>
              <input type="date" name="rec_date" class="form-control" value="{{ \Carbon\Carbon::parse($receiving->rec_date)->format('Y-m-d') }}" required/>
            </div>
          </div>
        </div>
      </section>
    </div>

    <div class="col-12 mb-4">
      <section class="card">
        <header class="card-header">
          <h2 class="card-title">Items Details</h2>
        </header>
        <div class="card-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Item</th>
                <th>Variation</th>
                <th>Ordered Quantity</th>
                <th>Previously Received</th>
                <th>Remaining</th>
                <th>Receiving Now</th>
              </tr>
            </thead>
            <tbody>
              @foreach($receiving->fgpo->details as $detail)
                <tr>
                  <td>{{ $detail->product->name ?? 'N/A' }}</td>
                  <td>{{ $detail->sku ?? 'N/A' }}</td>
                  <td>{{ $detail->qty }}</td>
                  <td>{{ $detail->total_received }}</td>
                  <td>{{ $detail->remaining_qty }}</td>
                  <td>
                    <input type="number" name="received_qty[{{ $detail->id }}]" class="form-control" 
                      value="{{ $detail->current_received }}" 
                      min="0" max="{{ $detail->remaining_qty + $detail->current_received }}">
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
          <footer class="card-footer text-end mt-1">
            <a class="btn btn-danger" href="{{ route('pur-fgpo-rec.index') }}" >Discard</a>
            <button type="submit" class="btn btn-primary">Update Receiving</button>
          </footer>
        </div>
      </section>
    </div>
  </form>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Purchasing | PO Receiving')

@section('content')
  <div class="row">
    <form action="{{ route('pur-fgpos.store-rec') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @if ($errors->has('error'))
        <strong class="text-danger">{{ $errors->first('error') }}</strong>
      @endif
      <div class="row">
        <div class="col-12 mb-4">
          <section class="card">
            <header class="card-header">
              <div style="display: flex;justify-content: space-between;">
                <h2 class="card-title">PO Receiving</h2>
              </div>
            </header>
            <div class="card-body">
              <div class="row mb-4">
                <div class="col-12 col-md-2">
                  <label>GRN #</label>
                  <input type="text" class="form-control" placeholder="GRN #" disabled/>
                </div>
                <div class="col-12 col-md-2 mb-3">
                  <label>PO #</label>
                  <input type="text" class="form-control" placeholder="PO #" value="PO-{{$purpo->id}}" disabled/>
                  <input type="hidden" class="form-control" name="fgpo_id" value="{{$purpo->id}}"/>
                </div>
                
                <div class="col-12 col-md-2">
                  <label>Receiving Date</label>
                  <input type="date" name="rec_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required/>
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
              <table class="table table-bordered" id="myTable">
                <thead>
                  <tr>
                    <th>Item</th>
                    <th>Variation</th>
                    <th>Ordered Quantity</th>
                    <th>Received</th>
                    <th>Remaining</th>
                    <th>Receiving Now</th>
                  </tr>
                </thead>
                <tbody id="PurPOTbleBody">
                  @foreach($purpo->details as $detail)
                    <tr>
                      <td>{{ $detail->product->name ?? 'N/A' }}</td>
                      <td>{{ $detail->sku ?? 'N/A' }}</td>
                      <td>{{ $detail->qty }}</td>
                      <td>{{ $detail->total_received }}</td>
                      <td>{{ $detail->qty - $detail->total_received }}</td>
                      <td><input type="number" name="received_qty[{{ $detail->id }}]" class="form-control" placeholder="Receiving Now" max="{{ $detail->remaining_qty }}" min="1"></td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
              <footer class="card-footer text-end mt-1">
                <a class="btn btn-danger" href="{{ route('pur-fgpos.index') }}" >Discard</a>
                <button type="submit" class="btn btn-primary">Received</button>
              </footer>
            </div>
          </section>
        </div>
      </div>
    </form>
  </div>

@endsection
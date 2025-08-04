@extends('layouts.app')

@section('title', 'Purchasing | All PO')

@section('content')
  <div class="row">
    <div class="col">
      <section class="card">
        <header class="card-header" style="display: flex;justify-content: space-between;">
          <h2 class="card-title">All PO</h2>
          <div>
            <a class="btn btn-primary text-end" href="{{ route('pur-pos.create') }}"  aria-expanded="false" > <i class="fa fa-plus"></i> Add PO</a>
          </div>
        </header>
        <div class="card-body">
          <div>
            <form method="GET" action="{{ route('pur-pos.index') }}" class="mb-3 d-flex">
              <div class="col-md-2" style="display:flex;">
                <select name="status" class="form-control" style="margin-right:10px" onchange="this.form.submit()">
                  <option value="">Filter by</option>
                  <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All</option>
                  <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                  <option value="Partially Received" {{ request('status') == 'Partially Received' ? 'selected' : '' }}>Partially Received</option>
                  <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                </select>
              </div>
            <form>
          </div>

          <div class="modal-wrapper table-scroll">
            <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
              <thead>
                <tr>
                  <th width="4%">S.No</th>
                  <th >PO Code</th>
                  <th>Date</th>
                  <th width="10%">Vendor</th>
                  <th width="55%">Items</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($purpos as $key => $row)
                  <tr>
                    <td width="4%"> {{$key+1}}</td>
                    <td > {{$row->po_code}}</td>
                    <td>{{ \Carbon\Carbon::parse($row->order_date)->format('d-m-y') }}</td>
                    <td width="10%">{{ $row->vendor->name ?? 'N/A' }}</td>
                    <td width="55%">{{ $row->details->map(function($d) {
                          return optional($d->product)->name . ' (' . optional($d->product)->id . ')';
                      })->filter()->implode(', ') }}</td>
                    <td>
                      <span class="{{ $row->status_class }}">
                        {{ $row->status_text }}
                      </span>
                    </td>                    
                    <td>
                      <a href="{{ route('pur-pos.print', $row->id) }}" class="text-success">
                        <i class="fa fa-print"></i>
                      </a>
                      <a href="{{ route('pur-po-rec.createForm', $row->id) }}" class="text-primary">
                        <i class="fa fa-arrow-left"></i>
                      </a>
                      <a href="{{ route('pur-pos.edit', $row->id) }}" class="text-warning">
                        <i class="fa fa-edit "></i>
                      </a>
                      <!-- Delete Link (with Confirmation) -->
                      <form action="{{ route('pur-pos.destroy', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this purchase order?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-danger" style="background:none;border:none">
                          <i class="fa fa-trash"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>
  </div>
@endsection
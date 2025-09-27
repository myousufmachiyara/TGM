@extends('layouts.app')

@section('title', 'PO Receiving | All Entries')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      <header class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title">All PO Receivings</h2>
      </header>
      <div class="card-body">
        <div class="modal-wrapper table-scroll">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>GRN No</th>
                <th>Date</th>
                <th>PO #</th>
                <th>Vendor</th>
                <th>Total Qty</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($receivings as $index => $rec)
              <tr>
                <td>{{ $rec->id }}</td>
                <td>{{ \Carbon\Carbon::parse($rec->date)->format('d-m-Y') }}</td>
                <td>PO-{{ $rec->po_id ?? '-' }}</td>
                <td>{{ $rec->po->vendor->name ?? '-' }}</td>
                <td>
                  {{ $rec->details->sum('qty') }}
                </td>
                <td>
                  <a href="{{ route('pur-po-rec.print', $rec->id) }}" class="text-success" target="_blank" rel="noopener noreferrer">
                    <i class="fa fa-print"></i>
                  </a>
                  <a href="{{ route('pur-po-rec.edit', $rec->id) }}" class="text-warning" title="Edit">
                    <i class="fa fa-edit"></i>
                  </a>
                  <form action="{{ route('pur-po-rec.destroy', $rec->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this receiving?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-danger border-0 bg-transparent" title="Delete">
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

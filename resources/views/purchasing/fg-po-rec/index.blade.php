@extends('layouts.app')

@section('title', 'FGPO Receiving | All Entries')

@section('content')
<div class="row">
  <div class="col">
    <section class="card">
      <header class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title">All FGPO Receivings</h2>
      </header>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
            <thead>
              <tr>
                <th>GRN No</th>
                <th>Date</th>
                <th>FGPO #</th>
                <th>Vendor</th>
                <th>Total Qty</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($receivings as $rec)
              <tr>
                <td>{{ $rec->id }}</td>
                <td>{{ \Carbon\Carbon::parse($rec->date)->format('d-m-Y') }}</td>
                <td>FGPO-{{ $rec->fgpo_id ?? '-' }}</td>
                <td>{{ $rec->fgpo->vendor->name ?? '-' }}</td>
                <td>{{ $rec->details->sum('qty') }}</td>
                <td>
                  <a href="{{ route('pur-fgpo-rec.print', $rec->id) }}" class="text-success" title="Print">
                    <i class="fa fa-print"></i>
                  </a>
                  <a href="{{ route('pur-fgpo-rec.edit', $rec->id) }}" class="text-warning" title="Edit">
                    <i class="fa fa-edit"></i>
                  </a>
                  <form action="{{ route('pur-fgpo-rec.destroy', $rec->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this receiving?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-link p-0 m-0 text-danger" title="Delete">
                      <i class="fa fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center">No FGPO receiving records found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>
@endsection

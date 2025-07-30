@extends('layouts.app')

@section('title', 'Party Ledger')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Party Ledger</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.party_ledger') }}" class="mb-4 row g-3">
            <div class="col-md-4">
                <label for="vendor_id">Select Vendor</label>
                <select class="form-control" id="vendor_id" name="vendor_id" required>
                    <option value="">-- Select Vendor --</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ $vendorId == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="from_date">From Date</label>
                <input type="date" class="form-control" name="from_date" value="{{ $from }}" required>
            </div>
            <div class="col-md-3">
                <label for="to_date">To Date</label>
                <input type="date" class="form-control" name="to_date" value="{{ $to }}" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        @if($ledger && count($ledger))
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="text-end">Credit (Payable)</th>
                        <th class="text-end">Debit (Paid)</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php $balance = 0; @endphp
                    @foreach($ledger as $row)
                        @php
                            $balance += ($row->credit - $row->debit);
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->date)->format('d-m-Y') }}</td>
                            <td>{{ $row->type }}</td>
                            <td>{{ $row->description ?? '-' }}</td>
                            <td class="text-end text-danger">{{ $row->credit ? number_format($row->credit, 2) : '-' }}</td>
                            <td class="text-end text-success">{{ $row->debit ? number_format($row->debit, 2) : '-' }}</td>
                            <td class="text-end"><strong>{{ number_format($balance, 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif(request()->has('vendor_id'))
            <div class="alert alert-warning">No records found for the selected vendor and date range.</div>
        @endif
    </div>
</div>
@endsection

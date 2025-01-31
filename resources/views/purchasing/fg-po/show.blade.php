@extends('layouts.app')

@section('title', 'Purchasing | All PO')

@section('content')
  <div class="row">
    <div class="col">
      <section class="card">
        <header class="card-header" style="display: flex;justify-content: space-between;">
            <h2 class="card-title">All PO</h2>
            <a  class="btn btn-primary text-end" href="{{ route('purpos.create') }}"  aria-expanded="false" > <i class="fa fa-plus"></i> Add PO</a>
        </header>
        <div class="card-body">
          <div class="modal-wrapper table-scroll">
            <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
              <thead>
                <tr>
                  <th>S.No</th>
                  <th>Delivery Date</th>
                  <th>Order Date</th>
                  <th>Vendor</th>
                  <th>Payment Term</th>
                  <th>Action</th> 
                </tr>
              </thead>
              <tbody>
                @foreach ($purpos as $key => $row)
                  <tr>
                    <td>{{$key+1}}</td>
                    <td>{{ \Carbon\Carbon::parse($row->delivery_date)->format('d-m-y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->order_date)->format('d-m-y') }}</td>
                    <td>{{$row->vendor_name}}</td>
                    <td>{{$row->payment_term}}</td>
                    <td>
                      <a href="{{ route('purpos.edit', $row->id) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-print"></i>
                      </a>
                      <a href="{{ route('purpos.edit', $row->id) }}" class="btn btn-warning btn-sm">
                        <i class="fa fa-edit"></i>
                      </a>
                      <!-- Delete Link (with Confirmation) -->
                      <form action="{{ route('purpos.destroy', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this purchase order?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
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
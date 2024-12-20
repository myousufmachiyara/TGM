@extends('layouts.app')

@section('title', 'Purchasing | All PO')

@section('content')
  <div class="page-header d-flex justify-content-end">
    <ul class="breadcrumbs mb-3">
      <li class="nav-home">
        <a href="#">
          <i class="fa fa-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="fa fa-chevron-right"></i>
      </li>
      <li class="nav-item">
        <a href="#">Purchasing</a>
      </li>
      <li class="separator">
      <i class="fa fa-chevron-right"></i>
      </li>
      <li class="nav-item">
        <a href="#">PO</a>
      </li>
    </ul>
  </div>
  <div class="col-md-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <h4 class="card-title">All PO</h4>
        <a class="btn btn-primary" href="{{ route('purpos.create') }}"  aria-expanded="false" > <i class="fa fa-plus"></i> Add PO</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="basic-datatables" class="display table table-striped table-hover table-bordered" >
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
    </div>
  </div>
@endsection
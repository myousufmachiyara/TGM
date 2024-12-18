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
                <th>Vendor</th>
                <th>Fabric</th>
                <th>Rate</th>
                <th>Quantity</th>
                <th>Payment Term</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
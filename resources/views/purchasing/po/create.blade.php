@extends('layouts.app')

@section('title', 'Purchasing | New PO')

@section('content')
  <div class="page-header d-flex justify-content-end">
    <ul class="breadcrumbs mb-3">
      <li class="nav-home"><a href="#"> <i class="fa fa-home"></i></a></li>
      <li class="separator"> <i class="fa fa-chevron-right"></i></li>
      <li class="nav-item"> <a href="#">Purchasing</a></li>
      <li class="separator"><i class="fa fa-chevron-right"></i></li>
      <li class="nav-item"> <a href="#">New PO</a></li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form action="{{ route('purpos.store') }}" method="POST">
          @csrf
          <div class="card-header">
            <div class="card-title">New PO</div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-12 col-md-3 form-group">
                <label>Vendor Name</label>
                <input type="text" name="vendor_name" class="form-control" placeholder="Vendor Name" required/>
              </div>
              <div class="col-12 col-md-3 form-group">
                <label>Order Date</label>
                <input type="date" name="order_date" class="form-control" placeholder="Order Date" required/>
              </div>
              <div class="col-12 col-md-3 form-group">
                <label>Delivery Date</label>
                <input type="date" name="delivery_date" class="form-control" placeholder="Delivery Date" required/>
              </div>
              <div class="col-12 col-md-3 form-group">
                <label>Payment Term</label>
                <select class="form-control"  name="payment_term" required>
                  <option selected disabled>Select Payment Term</option>
                  <option>Credit</option>
                  <option>Cash</option>
                </select>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="card-title mb-3">Item Details</div>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Item Name</th>
                  <th>Item Category</th>
                  <th>Rate</th>
                  <th>Quantity</th>
                  <th>Total</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><input type="text" name="item_name[]" class="form-control" placeholder="Item Name" required/></td>
                  <td>
                    <select class="form-control">
                      <option selected disabled>Select Category</option>
                      <option>Category 1</option>
                      <option>Category 2</option>
                    </select>
                  </td>
                  <td><input type="number" name="item_rate[]" class="form-control" placeholder="Rate" required/></td>
                  <td><input type="number" name="item_qty[]" class="form-control" placeholder="Quantity" required/></td>
                  <td><input type="number" name="item_total[]" class="form-control" placeholder="Total" disabled/></td>
                  <td><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i></button></tr>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="card-action text-end">
            <a class="btn btn-danger" href="{{ route('purpos.index') }}" >Discard</a>
            <button type="submit" class="btn btn-primary">Create</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
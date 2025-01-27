@extends('layouts.app')

@section('title', 'Products | All Products')

@section('content')
  <div class="row">
    <div class="col">
      <section class="card">
          <header class="card-header">
              <div style="display: flex;justify-content: space-between;">
                  <h2 class="card-title">All Products</h2>
                  <a href="{{ route('products.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Products </a>
              </div>
              @if ($errors->has('error'))
                  <strong class="text-danger">{{ $errors->first('error') }}</strong>
              @endif
          </header>
          <div class="card-body">
            <div class="modal-wrapper table-scroll">
                <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
                <thead>
                    <tr>
                    <th>S.No</th>
                    <th>Name</th>
                    <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
                </table>
            </div>
          </div>
      </section>
    </div>
  </div>
@endsection

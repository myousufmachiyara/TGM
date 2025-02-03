@extends('layouts.app')

@section('title', 'Products | All Products')

@section('content')
  <div class="row">
    <div class="col">
      <section class="card">
          <header class="card-header">
              <div style="display: flex;justify-content: space-between;">
                <h2 class="card-title">All Products</h2>
                <div>
                  <a href="{{ route('products.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Products </a>
                </div>
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
                      <th>SKU</th>
                      <th>Category</th>
                      <th>Measurement Unit</th>
                      <th>Price</th>
                      <th>sale Price</th>
                    </tr>
                </thead>
                <tbody>
                  @foreach ($products as $item)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td><strong>{{ $item->name }}</strong></td>
                      <td>{{ $item->sku }}</td>
                      <td>{{ $item->category ? $item->category->name : 'No Category' }}</td>
                      <td>{{ $item->measurement_unit }}</td>
                      <td>{{ $item->price }}</td>
                      <td>{{ $item->sale_price }}</td>
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

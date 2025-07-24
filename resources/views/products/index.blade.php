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
                      <th>Image</th>
                      <th>Item Name (ID) </th>
                      <th>SKU</th>
                      <th>Category</th>
                      <th>Price</th>
                      <th>sale Price</th>
                      <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                  @foreach ($products as $item)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>
                        @if ($item->firstAttachment)
                          <img src="{{ asset('storage/' . $item->firstAttachment->image_path) }}" alt="Product Image" width="50">
                        @else
                          <span>No Image</span>
                        @endif
                      </td>
                      <td><strong>{{ $item->name }}({{ $item->id }})</strong></td>
                      <td>{{ $item->sku }}</td>
                      <td>{{ $item->category ? $item->category->name : 'No Category' }}</td>
                      <td>{{ $item->price }}</td>
                      <td>{{ $item->sale_price }}</td>
                      <td>
                      <a class="text-primary" href="{{ route('products.edit', $item->id) }}">
                        <i class="fa fa-edit"></i>
                      </a>
                      <!-- Delete Link (with Confirmation) -->
                      <form action="{{ route('products.destroy', $item->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this purchase order?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-danger bg-transparent" style="border:none">
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

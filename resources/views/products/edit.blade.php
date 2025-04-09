@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
  <div class="row">
    <div class="col">
      <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <section class="card">
          <header class="card-header">
            <h2 class="card-title">Edit Product</h2>
            @if ($errors->has('error'))
              <strong class="text-danger">{{ $errors->first('error') }}</strong>
            @endif
          </header>
          <div class="card-body">
            <!-- Product Fields -->
            <div class="row pb-3">
              <div class="col-12 col-md-2 mb-2">
                <label for="name">Product Name <span style="color: red;"><strong>*</strong></span></label>
                <input type="text" class="form-control" name="name" value="{{ old('name', $product->name) }}" required>
              </div>
              <div class="col-12 col-md-2 mb-2">
                <label for="sku">SKU</label>
                <input type="text" class="form-control" name="sku" value="{{ old('sku', $product->sku) }}">
              </div>
              <!-- Other fields... -->
            </div>

            <!-- Variations Section -->
            <div class="row">
              <div class="col-6">
                <label>Product Has Variations</label>
                <input type="checkbox" id="toggleVariations" name="has_variations" value="1" {{ $product->has_variations ? 'checked' : '' }}>
              </div>

              <div class="col-12" id="variationFields" style="display:{{ $product->has_variations ? 'block' : 'none' }}">
                <!-- Display existing variations -->
                <table class="table table-bordered" id="variationsTable">
                  <thead>
                    <tr>
                      <th>Variation</th>
                      <th>Quantity</th>
                      <th>Price</th>
                      <th>SKU <span style="color: red;"><strong>(system generated)</strong></span></th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($product->variations as $index => $variation)
                      <tr data-value="{{ $variation->value_id }}">
                        <td>
                          <strong>{{ $variation->attribute->name }}:</strong> {{ $variation->value->value }}
                          <input type="hidden" name="variations[{{ $index }}][attribute_id]" value="{{ $variation->attribute_id }}">
                          <input type="hidden" name="variations[{{ $index }}][attribute_value_id]" value="{{ $variation->value_id }}">
                        </td>
                        <td><input type="number" name="variations[{{ $index }}][stock]" value="{{ $variation->stock }}"></td>
                        <td><input type="number" name="variations[{{ $index }}][price]" value="{{ $variation->price }}"></td>
                        <td><input type="text" name="variations[{{ $index }}][sku]" value="{{ $variation->sku }}" disabled></td>
                        <td><button type="button" class="btn btn-danger remove-btn btn-xs"><i class="fa fa-times"></i></button></td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <footer class="card-footer text-end">
            <a class="btn btn-danger" href="{{ route('products.index') }}">Discard</a>
            <button type="submit" class="btn btn-primary">Update</button>
          </footer>
        </section>
      </form>
    </div>
  </div>

  <script>
    // Toggle visibility of variations fields based on checkbox
    document.getElementById('toggleVariations').addEventListener('change', function() {
      const variationFields = document.getElementById('variationFields');
      variationFields.style.display = this.checked ? 'block' : 'none';
    });
  </script>
@endsection

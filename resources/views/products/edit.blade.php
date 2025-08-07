@extends('layouts.app')

@section('title', 'Products | Edit Product')

@section('content')
<div class="row">
  <div class="col">
    <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <section class="card">
        <header class="card-header">
          <div class="d-flex justify-content-between">
            <h2 class="card-title">Edit Product</h2>
          </div>
          @if ($errors->has('error'))
            <strong class="text-danger">{{ $errors->first('error') }}</strong>
          @endif
        </header>
        <div class="card-body">
          <div class="row pb-3">
            <div class="col-12 col-md-2 mb-2">
              <label>Product Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name" value="{{ old('name', $product->name) }}" required>
              @error('name')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-2 mb-2">
              <label>Category <span class="text-danger">*</span></label>
              <select class="form-control select2-js" name="category_id" required>
                <option value="" disabled>Select Category</option>
                @foreach ($prodCat as $item)
                  <option value="{{ $item->id }}" {{ old('category_id', $product->category_id) == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
              @error('category_id')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-2 mb-2">
              <label>SKU (system generated)</label>
              <input type="text" class="form-control" value="{{ $product->sku }}" disabled>
            </div>

            <div class="col-12 col-md-2 mb-2">
              <label>Unit</label>
              <select class="form-control" name="measurement_unit" required>
                <option value="" disabled>Select Unit</option>
                @foreach(['mtr'=>'meter','pcs'=>'pieces','yrd'=>'yards','rd'=>'round'] as $val => $label)
                  <option value="{{ $val }}" {{ old('measurement_unit', $product->measurement_unit) == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
              @error('measurement_unit')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-2 mb-2">
              <label>Item Type</label>
              <select class="form-control" name="item_type" id="item_type" required>
                <option value="" disabled>Item Type</option>
                <option value="fg" {{ old('item_type', $product->item_type) == 'fg' ? 'selected' : '' }}>F.G</option>
                <option value="mfg" {{ old('item_type', $product->item_type) == 'mfg' ? 'selected' : '' }}>Men's FG</option>
                <option value="raw" {{ old('item_type', $product->item_type) == 'raw' ? 'selected' : '' }}>Raw</option>
              </select>
              @error('item_type')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            <div id="div-mfg" class="col" style="display: none;">
              <div class="col-12 mb-2">
                <label>Collar Style</label>
                <select class="form-control select2-js" name="style">
                  <option value="" disabled>Collar Style</option>
                  <option value="shirt" {{ old('style', $product->style) == 'shirt' ? 'selected' : '' }}>Shirt Collar</option>
                  <option value="sherwani" {{ old('style', $product->style) == 'sherwani' ? 'selected' : '' }}>Sherwani Collar</option>
                  <option value="round" {{ old('style', $product->style) == 'round' ? 'selected' : '' }}>Round Collar</option>
                </select>
              </div>

              <div class="col-12 mb-2">
                <label>Material</label>
                <select class="form-control select2-js" name="material">
                  <option value="" disabled>Material</option>
                  <option value="cotton" {{ old('material', $product->material) == 'cotton' ? 'selected' : '' }}>Cotton</option>
                  <option value="ww" {{ old('material', $product->material) == 'ww' ? 'selected' : '' }}>Washing Wear</option>
                  <option value="chicken" {{ old('material', $product->material) == 'chicken' ? 'selected' : '' }}>Chicken</option>
                </select>
              </div>
            </div>

            <div class="col-12 col-md-2 mb-2">
              <label>Opening</label>
              <input type="number" class="form-control" name="opening_stock" value="{{ old('opening_stock', $product->opening_stock) }}" required>
            </div>

            <div class="col-12 col-md-6 mb-2">
              <label>Description</label>
              <textarea class="form-control" name="description">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="col-12 col-md-6 mb-2">
              <label>Purchase Note</label>
              <textarea class="form-control" name="purchase_note">{{ old('purchase_note', $product->purchase_note) }}</textarea>
            </div>

            <div class="col-12 col-md-3 mb-2">
              <label>Images</label>
              <input type="file" class="form-control" name="prod_att[]" multiple accept="image/*">
              <div class="mt-2 d-flex flex-wrap gap-2">
                @foreach ($product->attachments as $img)
                  <img src="{{ asset('public/storage/' . $img->image_path) }}" style="max-width:100px; border:1px solid #ccc; border-radius:5px; padding:4px">
                @endforeach
              </div>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-8">
              <div class="card-title mb-3" style="display:inline-block">
                Product Has Variations
                <input type="hidden" id="hasVariationsHidden" name="has_variations" value="1">
              </div>
              <div id="prodVariationsDiv">
                <div class="row">
                  <div class="col-12 col-md-12 mt-3">
                    <table class="table table-bordered" id="variationsTable">
                      <thead>
                        <tr>
                          <th>Variation</th>
                          <th>Quantity</th>
                          <th>Price</th>
                          <th>SKU (system generated)</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="variationRows">
                        @foreach ($product->variations as $index => $variation)
                            <tr data-value="{{ $variation->attribute_value_id }}">
                                <td>
                                <strong>{{ optional($variation->attribute)->name }}:</strong> {{ optional($variation->attributeValue)->value }}
                                <input type="hidden" name="variations[{{ $index }}][attribute_id]" value="{{ $variation->attribute_id }}">
                                <input type="hidden" name="variations[{{ $index }}][attribute_value_id]" value="{{ $variation->attribute_value_id }}">
                                </td>
                                <td><input type="number" class="form-control" name="variations[{{ $index }}][stock]" value="{{ $variation->stock }}"></td>
                                <td><input type="number" class="form-control" name="variations[{{ $index }}][price]" value="{{ $variation->price }}"></td>
                                <td><input type="text" class="form-control" name="variations[{{ $index }}][sku]" value="{{ $variation->sku }}" readonly></td>
                                <td><button type="button" class="btn btn-danger remove-btn"><i class="fa fa-times"></i></button></td>
                            </tr>
                            @endforeach
                      </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-secondary" id="addVariationBtn">+ Add Variation</button>

                  </div>
                </div>
              </div>
            </div>

            <div class="col-6">
              <div id="previewContainer" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;margin-bottom:20px;"></div>
            </div>
          </div>
        </div>

        <footer class="card-footer text-end">
          <a class="btn btn-danger" href="{{ route('products.index') }}">Cancel</a>
          <button type="submit" class="btn btn-primary">Update</button>
        </footer>
      </section>
    </form>
  </div>
</div>


<script>
  document.addEventListener('DOMContentLoaded', function () {
      const tbody = document.getElementById('variationRows');
      const addBtn = document.getElementById('addVariationBtn');
      let newIndex = 0;

      if (!tbody || !addBtn) {
          console.error("Missing variationRows or addVariationBtn element");
          return;
      }

      addBtn.addEventListener('click', function () {
          const row = document.createElement('tr');
          row.innerHTML = `
              <td>
                  <label><strong>New Variation:</strong></label>
                  <select name="variations[new_${newIndex}][attribute_id]" class="form-control attribute-select" required>
                      <option value="">-- Select Attribute --</option>
                      @foreach ($attributes as $attr)
                          <option value="{{ $attr->id }}">{{ $attr->name }}</option>
                      @endforeach
                  </select>
                  <select name="variations[new_${newIndex}][attribute_value_id]" class="form-control mt-2 value-select" required>
                      <option value="">-- Select Value --</option>
                  </select>
              </td>
              <td><input type="number" class="form-control" name="variations[new_${newIndex}][stock]" required></td>
              <td><input type="number" class="form-control" name="variations[new_${newIndex}][price]" required></td>
              <td><input type="text" class="form-control" name="variations[new_${newIndex}][sku]" placeholder="SKU (optional)"></td>
              <td><button type="button" class="btn btn-danger remove-btn"><i class="fa fa-times"></i></button></td>
          `;
          tbody.appendChild(row);
          newIndex++;
      });

      // Remove variation
      document.getElementById('variationsTable').addEventListener('click', function (e) {
          if (e.target.closest('.remove-btn')) {
              e.target.closest('tr').remove();
          }
      });

      // Fetch values on attribute change
      document.getElementById('variationsTable').addEventListener('change', function (e) {
          if (e.target.classList.contains('attribute-select')) {
              const attributeId = e.target.value;
              const tr = e.target.closest('tr');
              const valueSelect = tr.querySelector('.value-select');

              if (!attributeId) {
                  valueSelect.innerHTML = `<option value="">-- Select Value --</option>`;
                  return;
              }

              valueSelect.innerHTML = `<option value="">Loading...</option>`;
              

              fetch('/attributes/' + attributeId + '/values')
                  .then(res => res.json())
                  .then(data => {
                      if (Array.isArray(data) && data.length > 0) {
                          let options = `<option value="">-- Select Value --</option>`;
                          data.forEach(item => {
                              options += `<option value="${item.id}">${item.value}</option>`;
                          });
                          valueSelect.innerHTML = options;
                      } else {
                          valueSelect.innerHTML = `<option value="">No values found</option>`;
                      }
                  })
                  .catch(error => {
                      console.error("Fetch error:", error);
                      valueSelect.innerHTML = `<option value="">Error loading values</option>`;
                  });
          }
      });
  });
</script>

@endsection

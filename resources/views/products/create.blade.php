@extends('layouts.app')

@section('title', 'Products | New Product')

@section('content')
  <div class="row">
    <div class="col">
      <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <section class="card">
          <header class="card-header">
            <div class="d-flex justify-content-between">
              <h2 class="card-title">New Product</h2>
            </div>
            @if ($errors->has('error'))
              <strong class="text-danger">{{ $errors->first('error') }}</strong>
            @endif
          </header>
          <div class="card-body">
            <div class="row pb-3">
				<div class="col-12 col-md-2 mb-2">
					<label class="text-lg-end mb-0">Product Name <span style="color: red;"><strong>*</strong></span></label>
					<input type="text" class="form-control" placeholder="Product Name" name="name" value="{{ old('name') }}" required>
					@error('name')<div class="text-danger">{{ $message }}</div>@enderror
				</div>

				<div class="col-12 col-md-2 mb-2">
					<label class="text-lg-end mb-0">SKU <span style="color: red;"><strong>*</strong></span></label>
					<input type="text" class="form-control" id="base-sku" placeholder="Product SKU" name="sku" value="{{ old('sku') }}" required />
					@error('sku')<div class="text-danger">{{ $message }}</div>@enderror
				</div>

				<div class="col-12 col-md-2 mb-2">
					<label>Category <span style="color: red;"><strong>*</strong></span></label>
					<select data-plugin-selecttwo class="form-control select2-js" name="category_id" required>
						<option value="" selected disabled>Select Category</option>
						@foreach ($prodCat as $item)
							<option value="{{ $item->id }}" {{ old('category_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
						@endforeach
					</select>
					@error('category_id')<div class="text-danger">{{ $message }}</div>@enderror
				</div>

				<div class="col-12 col-md-2 mb-2">
					<label>Unit</label>
					<select data-plugin-selecttwo class="form-control select2-js" name="measurement_unit">
						<option value="" selected disabled>Select Unit</option>
						<option value="mtr" {{ old('measurement_unit') == 'mtr' ? 'selected' : '' }}>meter</option>
						<option value="pcs" {{ old('measurement_unit') == 'pcs' ? 'selected' : '' }}>pieces</option> 
						<option value="yrd" {{ old('measurement_unit') == 'yrd' ? 'selected' : '' }}>yards</option> 
						<option value="rd" {{ old('measurement_unit') == 'rd' ? 'selected' : '' }}>round</option> 
					</select>
					@error('measurement_unit')<div class="text-danger">{{ $message }}</div>@enderror
				</div>

				<div class="col-12 col-md-1 mb-2">
					<label>Item Type</label>
					<select data-plugin-selecttwo class="form-control select2-js" name="item_type">
						<option value="" selected disabled>Item Type</option>
						<option value="fg" {{ old('item_type') == 'fg' ? 'selected' : '' }}>F.G</option>
						<option value="raw" {{ old('item_type') == 'raw' ? 'selected' : '' }}>Raw</option> 
					</select>
					@error('item_type')<div class="text-danger">{{ $message }}</div>@enderror
				</div>

				<div class="col-12 col-md-1 mb-2">
					<label>Purchase Price</label>
					<input type="number" step=".00" class="form-control" value="{{ old('price', '0.00') }}" name="price" required />
					@error('price')<div class="text-danger">{{ $message }}</div>@enderror
				</div>

				<div class="col-12 col-md-1 mb-2">
					<label>Sale Price</label>
					<input type="number" step=".00" class="form-control" value="{{ old('sale_price', '0.00') }}" name="sale_price" required />
					@error('sale_price')<div class="text-danger">{{ $message }}</div>@enderror
				</div>

				<div class="col-12 col-md-1 mb-2">
					<label>Opening Stock</label>
					<input type="number" step=".00" class="form-control" value="{{ old('opening_stock', '0') }}" name="opening_stock" required />
					@error('opening_stock')<div class="text-danger">{{ $message }}</div>@enderror
				</div>

				<div class="col-12 col-md-6 mb-2">
					<label>Description</label>
					<textarea class="form-control" rows="3" placeholder="Description" name="description">{{ old('description') }}</textarea>
					@error('description')<div class="text-danger">{{ $message }}</div>@enderror
				</div>

				<div class="col-12 col-md-6 mb-2">
					<label>Purchase Note</label>
					<textarea class="form-control" rows="3" placeholder="Purchase Note" name="purchase_note">{{ old('purchase_note') }}</textarea>
					@error('purchase_note')<div class="text-danger">{{ $message }}</div>@enderror
				</div>
				
				<div class="col-12 col-md-3 mb-2">
					<label>Images</label>
					<input type="file" class="form-control" name="prod_att[]" multiple accept="image/png, image/jpeg, image/jpg">
					@error('prod_att')<div class="text-danger">{{ $message }}</div>@enderror
				</div>
            </div>
          </div>

          <!-- Product variation card body here -->
          <div class="card-body">
            <div class="card-title mb-3" style="display:inline-block">
              <input type="checkbox" id="toggleTableSwitch" onchange="toggleVariationFields()">
              Product Variations
            </div>

            <div id="prodVariationsDiv" style="display:none">
              <div class="row">
                <div class="col-12 col-md-3">
                  <label>Variation</label>
                  <select data-plugin-selecttwo class="form-control select2-js" id="attributeSelect" name="variations[0][attribute_id]" required>
                    <option value="" selected disabled>Select Variation</option>
                    @foreach ($attributes as $item)
                      <option value="{{ $item->id }}" {{ old('variations.0.attribute_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                    @endforeach
                  </select>
                  @error('variations.0.attribute_id')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-3 mb-2">
                  <label>Values</label>
                  <select data-plugin-selecttwo multiple class="form-control select2-js" id="valueSelect" name="variations[0][value_id][]" required>
                    <option value="" disabled>Values</option>
                  </select>
                  @error('variations.0.value_id')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-2 d-flex align-items-end">
                  <button type="button" class="btn btn-success" id="generate-variations-btn">Generate</button>
                </div>
              </div>

              <div class="row">
                <div class="col-12 col-md-6 mt-3">
                  <table class="table table-bordered" id="variationsTable">
                    <thead>
                      <tr>
                        <th>Variation</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>SKU</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>
            </div>

            <footer class="card-footer text-end">
              <a class="btn btn-danger" href="{{ route('products.index') }}">Discard</a>
              <button type="submit" class="btn btn-primary">Create</button>
            </footer>
          </div>
        </section>
      </form>
    </div>
  </div>

<script>
  // Toggle variation fields
  function toggleVariationFields() {
    const isChecked = document.getElementById('toggleTableSwitch').checked;
    const variationsDiv = document.getElementById('prodVariationsDiv');
    variationsDiv.style.display = isChecked ? 'block' : 'none';
  }

  $(document).ready(function () {
    // Initialize Select2
    $('.select2-js').select2();

    // Define attribute-values from Laravel Blade
    let attributeValues = @json($attributes);

    // Populate values dropdown when attribute changes
    $("#attributeSelect").change(function () {
      let selectedAttributeId = $(this).val();
      let valuesDropdown = $("#valueSelect");

      valuesDropdown.empty(); // Clear existing options

      if (selectedAttributeId) {
        let selectedAttribute = attributeValues.find(attr => attr.id == selectedAttributeId);
        if (selectedAttribute && selectedAttribute.values.length > 0) {
          selectedAttribute.values.forEach(function (value) {
            valuesDropdown.append('<option value="' + value.id + '">' + value.value + '</option>');
          });
        }
      }
      valuesDropdown.trigger("change"); // Refresh Select2 UI
    });

    // Add selected variations to the table
    $("#generate-variations-btn").click(function () {
      let selectedAttributeText = $("#attributeSelect option:selected").text();
      let selectedValues = $("#valueSelect").val(); // Get selected values
      let tableBody = $("#variationsTable tbody");

      if (!selectedAttributeText || selectedValues.length === 0) {
        alert("Please select an attribute and at least one value.");
        return;
      }

      selectedValues.forEach(valueId => {
        let valueText = $("#valueSelect option[value='" + valueId + "']").text();

        // Prevent duplicate entries
        if ($("#variationsTable tbody tr[data-value='" + valueId + "']").length === 0) {
          let row = `
          <tr data-value="${valueId}">
            <td>${selectedAttributeText}: ${valueText}</td>
            <td><input type="number" class="form-control" name="variations[0][stock][]" required></td>
            <td><input type="number" class="form-control" name="variations[0][price][]" required></td>
            <td><input type="text" class="form-control" name="variations[0][sku][]" required></td>
            <td><button class="btn btn-danger remove-btn btn-xs"><i class="fa fa-times"></i></button></td>
          </tr>`;
          tableBody.append(row);
        }
      });
    });

    // Remove row from table
    $(document).on("click", ".remove-btn", function () {
      $(this).closest("tr").remove();
    });
  });
</script>

@endsection
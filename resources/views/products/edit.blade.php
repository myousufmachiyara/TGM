@extends('layouts.app')

@section('title', 'Products | Edit Product')

@section('content')
    <div class="row">
        <div class="col">
            <form action="{{ route('products.update' , $product->id) }}" method="POST" enctype="multipart/form-data">
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
                                <label class="text-lg-end mb-0">Product Name <span style="color: red;"><strong>*</strong></span></label>
                                <input type="text" class="form-control" placeholder="Product Name" name="name" value="{{ old('name', $product->name) }}" required>
                                @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-2 mb-2">
                                <label>Category <span style="color: red;"><strong>*</strong></span></label>
                                <select data-plugin-selecttwo class="form-control select2-js" name="category_id" required>
                                    <option value="" selected disabled>Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $category->id == $product->category_id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-2 mb-2">
                                <label class="text-lg-end mb-0">SKU</label>
                                <input type="text" class="form-control" id="base-sku" placeholder="Product SKU" value="{{ old('sku', $product->sku) }}"  disabled />
                                @error('sku')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-1 mb-2">
                                <label>Unit</label>
                                <select data-plugin-selecttwo class="form-control select2-js" name="measurement_unit" required>
                                    <option value="" selected disabled>Select Unit</option>
                                    <option value="mtr" {{ old('measurement_unit') == 'mtr' ? 'selected' : '' }}>meter</option>
                                    <option value="pcs" {{ old('measurement_unit') == 'pcs' ? 'selected' : '' }}>pieces</option> 
                                    <option value="yrd" {{ old('measurement_unit') == 'yrd' ? 'selected' : '' }}>yards</option> 
                                    <option value="rd" {{ old('measurement_unit') == 'rd' ? 'selected' : '' }}>round</option> 
                                </select>
                                @error('measurement_unit')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 col-md-1 mb-2">
                                <label>Width</label>
                                <input type="number" step=".00" class="form-control" value="{{ old('width', '0.00') }}" name="width" />
                                @error('width')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 col-md-1 mb-2">
                                <label>Item Type</label>
                                <select data-plugin-selecttwo class="form-control select2-js" name="item_type" required>
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
                                <input type="file" class="form-control" name="prod_att[]" id="imageUpload" multiple accept="image/png, image/jpeg, image/jpg, image/webp">  
                                @error('prod_att')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="card-title mb-3" style="display:inline-block">
                                        Product Has Variations
                                        <input type="hidden" id="hasVariationsHidden" name="has_variations" value="{{ old('has_variations', $product->has_variations) }}">
                                        <input type="checkbox" id="toggleTableSwitch" value="1" onchange="toggleVariationFields()" {{ $product->has_variations ? 'checked' : '' }}>
                                    </div>

                                    <div id="prodVariationsDiv" style="{{ $product->has_variations ? '' : 'display:none' }}">
                                        <div class="row">
                                            <div class="col-12 col-md-4">
                                                <label>Variation</label>
                                                <select data-plugin-selecttwo class="form-control select2-js" id="attributeSelect" name="variations[0][attribute_id]">
                                                    <option value="" selected disabled>Select Variation</option>
                                                    @foreach ($attributes as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-12 col-md-6 mb-2">
                                                <label>Values</label>
                                                <select data-plugin-selecttwo multiple class="form-control select2-js" id="valueSelect">
                                                    <option value="" disabled>Values</option>
                                                </select>
                                            </div>

                                            <div class="col-12 col-md-2 d-flex align-items-center">
                                                <button type="button" class="btn btn-success" id="generate-variations-btn">Generate</button>
                                            </div>

                                            <div class="row">
                                                <div class="col-12 col-md-12 mt-3">
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
                                                            @if(isset($product->variations) && $product->variations->count())
                                                                @foreach($product->variations as $index => $variation)
                                                                    <tr data-value="{{ $variation->attribute_value_id }}">
                                                                        <td><strong>{{ $variation->attribute->name ?? '' }}:</strong> {{ $variation->attributeValue->value ?? '' }}
                                                                            <input type="hidden" name="variations[{{ $index }}][attribute_id]" value="{{ $variation->attribute_id }}">
                                                                            <input type="hidden" name="variations[{{ $index }}][attribute_value_id]" value="{{ $variation->attribute_value_id }}">
                                                                        </td>
                                                                        <td><input type="number" class="form-control" name="variations[{{ $index }}][stock]" value="{{ $variation->stock }}" required></td>
                                                                        <td><input type="number" class="form-control" name="variations[{{ $index }}][price]" value="{{ $variation->price }}" required></td>
                                                                        <td><input type="text" class="form-control" value="{{ $variation->sku }}" disabled></td>
                                                                        <td><button class="btn btn-danger remove-btn btn-xs"><i class="fa fa-times"></i></button></td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
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
                            <a class="btn btn-danger" href="{{ route('products.index') }}">Discard</a>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </footer>
                    </div>
                </section>
            </form>
        </div>
    </div>
    <script>
        function toggleVariationFields() {
            const isChecked = document.getElementById('toggleTableSwitch').checked;
            const variationsDiv = document.getElementById('prodVariationsDiv');
            const hasVariationsHidden = document.getElementById('hasVariationsHidden');

            variationsDiv.style.display = isChecked ? 'block' : 'none';
            hasVariationsHidden.value = isChecked ? "1" : "0";

            const attributeSelect = document.getElementById('attributeSelect');
            const valueSelect = document.getElementById('valueSelect');

            if (isChecked) {
                attributeSelect.setAttribute("required", "required");

                valueSelect.onchange = function () {
                    if (valueSelect.selectedOptions.length === 0) {
                        valueSelect.setCustomValidity("Please select at least one variation value.");
                    } else {
                        valueSelect.setCustomValidity("");
                    }
                };
            } else {
                attributeSelect.removeAttribute("required");
                valueSelect.onchange = null;
                valueSelect.setCustomValidity("");
                $("#variationsTable tbody").empty();
            }
        }

        $(document).ready(function () {
            $('.select2-js').select2();
            let attributeValues = @json($attributes);

            $("#attributeSelect").change(function () {
                let selectedAttributeId = $(this).val();
                let valuesDropdown = $("#valueSelect");
                valuesDropdown.empty();

                if (selectedAttributeId) {
                    let selectedAttribute = attributeValues.find(attr => attr.id == selectedAttributeId);
                    if (selectedAttribute && selectedAttribute.values.length > 0) {
                        selectedAttribute.values.forEach(function (value) {
                            valuesDropdown.append('<option value="' + value.id + '">' + value.value + '</option>');
                        });
                    }
                }
                valuesDropdown.trigger("change");
            });

            $("#generate-variations-btn").click(function () {
                let selectedAttributeText = $("#attributeSelect option:selected").text();
                let selectedAttributeId = $("#attributeSelect option:selected").val();
                let selectedValues = $("#valueSelect").val();
                let tableBody = $("#variationsTable tbody");
                let rowCount = tableBody.find("tr").length;

                if (!selectedAttributeText || selectedValues.length === 0) {
                    alert("Please select an attribute and at least one value.");
                    return;
                }

                selectedValues.forEach((valueId, index) => {
                    let valueText = $("#valueSelect option[value='" + valueId + "']").text();

                    if ($("#variationsTable tbody tr[data-value='" + valueId + "']").length === 0) {
                        let rowIndex = rowCount + index;
                        let row = `
                            <tr data-value="${valueId}"> 
                                <td><strong>${selectedAttributeText}:</strong> ${valueText}
                                <input type="hidden" name="variations[${rowIndex}][attribute_id]" value="${selectedAttributeId}">
                                <input type="hidden" name="variations[${rowIndex}][attribute_value_id]" value="${valueId}">
                                </td>
                                <td><input type="number" class="form-control" name="variations[${rowIndex}][stock]" value="0" required></td>
                                <td><input type="number" class="form-control" name="variations[${rowIndex}][price]" value="0" required></td>
                                <td><input type="text" class="form-control" name="variations[${rowIndex}][sku]" disabled></td>
                                <td><button class="btn btn-danger remove-btn btn-xs"><i class="fa fa-times"></i></button></td>
                            </tr>`;
                        tableBody.append(row);
                    }
                });
            });

            $(document).on("click", ".remove-btn", function () {
                $(this).closest("tr").remove();
            });

            document.getElementById("imageUpload").addEventListener("change", function (event) {
                const files = event.target.files;
                const previewContainer = document.getElementById("previewContainer");
                previewContainer.innerHTML = "";

                for (let file of files) {
                    if (file && file.type.startsWith("image/")) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const img = document.createElement("img");
                            img.src = e.target.result;
                            img.style.maxWidth = "200px";
                            img.style.maxHeight = "200px";
                            img.style.border = "1px solid #ddd";
                            img.style.borderRadius = "5px";
                            img.style.padding = "5px";
                            previewContainer.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });
        });
    </script>
@endsection
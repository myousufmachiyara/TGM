@extends('layouts.app')

@section('title', 'Products | New Product')

@section('content')
  <div class="row">
        <div class="col">
			<form action="{{ route('products.store') }}" method="POST">				
				@csrf
				<section class="card">
					<header class="card-header" style="display: flex;justify-content: space-between;">
						<h2 class="card-title">New Product</h2>
						<div class="card-actions">
						</div>
					</header>
					<div class="card-body">
						<div class="row pb-3">
							<div class="col-12 col-md-2 mb-2">
								<label class="text-lg-end mb-0">Product Name <span style="color: red;"><strong>*</strong></span></label>
								<input type="text" class="form-control" placeholder="Product Name" name="name" required>
							</div>

							<div class="col-12 col-md-2 mb-2">
								<label class="text-lg-end mb-0">SKU <span style="color: red;"><strong>*</strong></span></label>
								<input type="text" class="form-control" placeholder="Product SKU" name="sku" required />
							</div>
							<div class="col-12 col-md-2 mb-2">
								<label>Category<span style="color: red;"><strong>*</strong></span></label>
								<select data-plugin-selecttwo class="form-control select2-js" name="details[0][category_id]">  <!-- Added name attribute for form submission -->
									<option value="" selected disabled>Select Category</option>
									@foreach ($prodCat as $item)
										<option value="{{ $item->id }}">{{ $item->name }}</option> 
									@endforeach
								</select>
							</div>
							<div class="col-12 col-md-2 mb-2">
								<label>Unit<span style="color: red;"><strong>*</strong></span></label>
								<select data-plugin-selecttwo class="form-control select2-js" name="details[0][category_id]">  <!-- Added name attribute for form submission -->
									<option value="" selected disabled>Select Measurement Unit</option>
									<option value="mtr">meter</option>
									<option value="pcs">pieces</option> 
									<option value="yrd">yards</option> 
									<option value="rd">round</option> 
								</select>
							</div>
							<div class="col-12 col-md-2 mb-2">
								<label>Purchase Price<span style="color: red;"><strong>*</strong></span></label>
								<input type="number"  step=".00" class="form-control" name="price" required />
							</div>
							<div class="col-12 col-md-2 mb-2">
								<label>Sale Price<span style="color: red;"><strong>*</strong></span></label>
								<input type="number"  step=".00" class="form-control" name="sale_price" required />
							</div>

							<div class="col-12 col-md-6 mb-2">
								<label>Description<span style="color: red;"><strong>*</strong></span></label>
								<textarea type="text" class="form-control" rows="3" placeholder="Description" name="description"></textarea>
							</div>

							<div class="col-12 col-md-6 mb-2">
								<label>Purchase Note<span style="color: red;"><strong>*</strong></span></label>
								<textarea type="text" class="form-control" rows="3" placeholder="Purchase Note" name="purchase_note"></textarea>
							</div>
							
							<div class="col-12 col-md-3 mb-2">
								<label>Images</label>
								<input type="file" class="form-control" name="prod_att[]" multiple accept="image/png, image/jpeg, image/jpg">
							</div>
						</div>
					</div>
					<div class="card-body" style="max-height:400px; overflow-y:auto">
						<div class="card-title mb-3" style="display:inline-block">
							<input type="checkbox" id="toggleTableSwitch" checked onchange="toggleTable()">
							Product Variations
						</div>
						
						<!-- <table class="table table-bordered" id="prodVariations">
							<thead>
								<tr>
									<th>Variation Type</th>
									<th>Values</th>
									<th>Price</th>
									<th>Stock</th>
								</tr>
							</thead>
							<tbody id="PurPOTbleBody">
								<tr>
									<td>
										<select data-plugin-selectTwo class="form-control select2-js attribute-select" id="attributes" name="attribute_id" required onchange="updateValuesDropdown(this)">
											<option value="" disabled selected>Select Attribute</option>
											@foreach($attributes as $attribute)
												<option value="{{ $attribute->id }}" data-values='@json($attribute->values)'>
													{{ $attribute->name }}
												</option>
											@endforeach
										</select>
									</td>
									<td>
										<select multiple data-plugin-selectTwo class="form-select values-select" id="values" name="value_id[]" multiple required>
											<option value="" disabled>Select Values</option>
										</select>
									</td>
									<td><input type="number" name="details[0][item_qty]"   id="item_qty1" onchange="rowTotal(1)" step="any" class="form-control" placeholder="Price" required/></td>
									<td><input type="number" id="item_total1" class="form-control" placeholder="Quantity"/></td>
									<td>
										<button type="button" onclick="removeRow(this)" class="btn btn-danger" tabindex="1"><i class="fas fa-times"></i></button>
										<button type="button" class="btn btn-primary" onclick="addNewRow()" ><i class="fa fa-plus"></i></button>
									</td>
								</tr>
							</tbody>
						</table> -->

						<table class="table table-bordered" id="attributes-table">
							<thead>
								<tr>
									<th>Attribute</th>
									<th>Values</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<!-- Dynamic Rows Will Be Added Here -->
							</tbody>
						</table>
            			<button type="button" class="btn btn-primary" id="add-row-btn">Add Attribute</button>
					</div>
					<footer class="card-footer text-end">
						<a class="btn btn-danger" href="{{ route('products.index') }}" >Discard</a>
						<button type="submit" class="btn btn-primary">Create</button>
					</footer>
				</section>
			</form>
        </div>
    </div>
	<script>
		var index=2;

		// function toggleTable() {
        //     const table = document.getElementById('prodVariations');
        //     const isVisible = table.style.display !== 'none';
        //     table.style.display = isVisible ? 'none' : 'table';
        // }

		// function removeRow(button) {
		// 	var tableRows = $("#prodVariations tr").length;
		// 	if(tableRows>1){
		// 		var row = button.parentNode.parentNode;
		// 		row.parentNode.removeChild(row);
		// 		index--;	
		// 	} 
		// 	tableTotal();
		// }

		
		// function addNewRow(){
		// 	var lastRow =  $('#prodVariations tr:last');
		// 	latestValue=lastRow[0].cells[0].querySelector('select').value;

		// 	if(latestValue!=""){
		// 		var table = document.getElementById('prodVariations').getElementsByTagName('tbody')[0];
		// 		var newRow = table.insertRow(table.rows.length);

		// 		var cell1 = newRow.insertCell(0);
		// 		var cell2 = newRow.insertCell(1);
		// 		var cell3 = newRow.insertCell(2);
		// 		var cell4 = newRow.insertCell(3);
		// 		var cell5 = newRow.insertCell(4);

		// 		cell1.innerHTML  = '<select data-plugin-selectTwo class="form-control select2-js attribute-select" id="attributes" name="attribute_id" required onchange="updateValuesDropdown(this)">'+
		// 									'<option value="" disabled selected>Select Attribute</option>'+
		// 									@foreach($attributes as $attribute)
		// 										'<option value="{{ $attribute->id }}" data-values="@json($attribute->values)">'{{ $attribute->name }}'</option>'+
		// 									@endforeach
		// 								'</select>';
								
		// 		cell2.innerHTML  = '<select multiple data-plugin-selectTwo class="form-select values-select" id="values" name="value_id[]" required>'+
		// 									'<option value="" disabled>Select Values</option>'+
		// 								'</select>';
		// 		cell3.innerHTML  = '<input type="number" name="details['+index+'][item_qty]" step="any" id="item_qty'+index+'"  onchange="rowTotal('+index+')" class="form-control" placeholder="Quantity" required/>';
		// 		cell4.innerHTML  = '<input type="number" id="item_total'+index+'" class="form-control" placeholder="Total" disabled/>';
		// 		cell5.innerHTML  = '<button type="button" onclick="removeRow(this)" class="btn btn-danger" tabindex="1"><i class="fas fa-times"></i></button> '+
		// 						'<button type="button" class="btn btn-primary" onclick="addNewRow()" ><i class="fa fa-plus"></i></button>';
		// 		index++;
		// 		tableTotal();
		// 	}
		// }

		// function rowTotal(index){
		// 	var item_rate = parseFloat($('#item_rate'+index+'').val());
		// 	var item_qty = parseFloat($('#item_qty'+index+'').val());   
		// 	var item_total = item_rate * item_qty;

		// 	$('#item_total'+index+'').val(item_total.toFixed());
			
		// 	tableTotal();
		// }

		// function tableTotal(){
		// 	var totalQuantity=0;
		// 	var totalAmount=0;
		// 	var tableRows = $("#PurPOTbleBody tr").length;
		// 	var table = document.getElementById('myTable').getElementsByTagName('tbody')[0];

		// 	for (var i = 0; i < tableRows; i++) {
		// 		var currentRow =  table.rows[i];
		// 		totalQuantity = totalQuantity + Number(currentRow.cells[2].querySelector('input').value);
		// 		totalAmount = totalAmount + Number(currentRow.cells[3].querySelector('input').value);
		// 	}

		// 	$('#total_qty').val(totalQuantity);
		// 	$('#total_amt').val(totalAmount.toFixed());
		// }

		const attributes = @json($attributes);

    document.addEventListener("DOMContentLoaded", function () {
        const tableBody = document.querySelector("#attributes-table tbody");
        const addRowBtn = document.getElementById("add-row-btn");

        // Function to add a new row
        function addRow() {
            const rowIndex = tableBody.rows.length;

            const row = document.createElement("tr");

            // Attribute Dropdown
            const attributeCell = document.createElement("td");
            const attributeSelect = document.createElement("select");
            attributeSelect.className = "form-select attribute-select";
            attributeSelect.name = `attributes[${rowIndex}][attribute_id]`;
            attributeSelect.required = true;
            attributeSelect.innerHTML = `<option value="" disabled selected>Select Attribute</option>`;
            attributes.forEach(attribute => {
                attributeSelect.innerHTML += `<option value="${attribute.id}" data-values='${JSON.stringify(attribute.values)}'>${attribute.name}</option>`;
            });
            attributeSelect.addEventListener("change", function () {
                updateValuesDropdown(this, rowIndex);
            });
            attributeCell.appendChild(attributeSelect);
            row.appendChild(attributeCell);

            // Values Dropdown
            const valuesCell = document.createElement("td");
            const valuesSelect = document.createElement("select");
            valuesSelect.className = "form-select values-select";
            valuesSelect.name = `attributes[${rowIndex}][value_ids][]`;
            valuesSelect.multiple = true;
            valuesCell.appendChild(valuesSelect);
            row.appendChild(valuesCell);

            // Action Cell (Remove Button)
            const actionCell = document.createElement("td");
            const removeBtn = document.createElement("button");
            removeBtn.className = "btn btn-danger btn-sm";
            removeBtn.textContent = "Remove";
            removeBtn.type = "button";
            removeBtn.addEventListener("click", function () {
                row.remove();
            });
            actionCell.appendChild(removeBtn);
            row.appendChild(actionCell);

            tableBody.appendChild(row);
        }

        // Function to update the values dropdown
        function updateValuesDropdown(attributeSelect, rowIndex) {
            const selectedOption = attributeSelect.options[attributeSelect.selectedIndex];
            const values = JSON.parse(selectedOption.dataset.values || "[]");
            const valuesSelect = tableBody.rows[rowIndex].querySelector(".values-select");

            // Clear existing options
            valuesSelect.innerHTML = "";

            // Populate new options
            values.forEach(value => {
                const option = document.createElement("option");
                option.value = value.id;
                option.textContent = value.value;
                valuesSelect.appendChild(option);
            });
        }

        // Add initial row on page load
        addRowBtn.addEventListener("click", addRow);
    });
	</script>
@endsection

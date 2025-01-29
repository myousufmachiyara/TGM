@extends('layouts.app')

@section('title', 'Products | New Product')

@section('content')
  <div class="row">
        <div class="col">
			<form action="{{ route('products.store') }}" method="POST">				
				@csrf
				<section class="card">
					<header class="card-header">
						<div style="display: flex;justify-content: space-between;">
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
								<input type="text" class="form-control" placeholder="Product Name" name="name" required>
							</div>

							<div class="col-12 col-md-2 mb-2">
								<label class="text-lg-end mb-0">SKU <span style="color: red;"><strong>*</strong></span></label>
								<input type="text" class="form-control" id="base-sku" placeholder="Product SKU" name="sku" required />
							</div>
							<div class="col-12 col-md-2 mb-2">
								<label>Category <span style="color: red;"><strong>*</strong></span></label>
								<select data-plugin-selecttwo class="form-control select2-js" name="category_id">  <!-- Added name attribute for form submission -->
									<option value="" selected disabled>Select Category</option>
									@foreach ($prodCat as $item)
										<option value="{{ $item->id }}">{{ $item->name }}</option> 
									@endforeach
								</select>
							</div>
							<div class="col-12 col-md-2 mb-2">
								<label>Unit</label>
								<select data-plugin-selecttwo class="form-control select2-js" name="measurement_unit">  <!-- Added name attribute for form submission -->
									<option value="" selected disabled>Select Measurement Unit</option>
									<option value="mtr">meter</option>
									<option value="pcs">pieces</option> 
									<option value="yrd">yards</option> 
									<option value="rd">round</option> 
								</select>
							</div>
							<div class="col-12 col-md-2 mb-2">
								<label>Purchase Price</label>
								<input type="number"  step=".00" class="form-control" name="price" />
							</div>
							<div class="col-12 col-md-2 mb-2">
								<label>Sale Price</label>
								<input type="number"  step=".00" class="form-control" name="sale_price" />
							</div>

							<div class="col-12 col-md-6 mb-2">
								<label>Description</label>
								<textarea type="text" class="form-control" rows="3" placeholder="Description" name="description"></textarea>
							</div>

							<div class="col-12 col-md-6 mb-2">
								<label>Purchase Note</label>
								<textarea type="text" class="form-control" rows="3" placeholder="Purchase Note" name="purchase_note"></textarea>
							</div>
							
							<div class="col-12 col-md-3 mb-2">
								<label>Images</label>
								<input type="file" class="form-control" name="prod_att[]" multiple accept="image/png, image/jpeg, image/jpg">
							</div>
						</div>
					</div>
					
					<!-- product variation card body here -->


					<footer class="card-footer text-end">
						<a class="btn btn-danger" href="{{ route('products.index') }}" >Discard</a>
						<button type="submit" class="btn btn-primary">Create</button>
					</footer>
				</section>
			</form>
        </div>
    </div>
	<!-- <script>
		var index=2;

		function toggleTable() {
            const table = document.getElementById('prodVariations');
            const isVisible = table.style.display !== 'none';
            table.style.display = isVisible ? 'none' : 'table';
        }

		function removeRow(button) {
			var tableRows = $("#prodVariations tr").length;
			if(tableRows>1){
				var row = button.parentNode.parentNode;
				row.parentNode.removeChild(row);
				index--;	
			} 
			tableTotal();
		}

		function addNewRow(){
			var lastRow =  $('#prodVariations tr:last');
			latestValue=lastRow[0].cells[0].querySelector('select').value;

			if(latestValue!=""){
				var table = document.getElementById('prodVariations').getElementsByTagName('tbody')[0];
				var newRow = table.insertRow(table.rows.length);

				var cell1 = newRow.insertCell(0);
				var cell2 = newRow.insertCell(1);
				var cell3 = newRow.insertCell(2);
				var cell4 = newRow.insertCell(3);
				var cell5 = newRow.insertCell(4);

				cell1.innerHTML  = '<select data-plugin-selectTwo class="form-control select2-js attribute-select" id="attributes" name="attribute_id" required onchange="updateValuesDropdown(this)">'+
											'<option value="" disabled selected>Select Attribute</option>'+
											@foreach($attributes as $attribute)
												'<option value="{{ $attribute->id }}" data-values="@json($attribute->values)">'{{ $attribute->name }}'</option>'+
											@endforeach
										'</select>';
								
				cell2.innerHTML  = '<select multiple data-plugin-selectTwo class="form-select values-select" id="values" name="value_id[]" required>'+
											'<option value="" disabled>Select Values</option>'+
										'</select>';
				cell3.innerHTML  = '<input type="number" name="details['+index+'][item_qty]" step="any" id="item_qty'+index+'"  onchange="rowTotal('+index+')" class="form-control" placeholder="Quantity" required/>';
				cell4.innerHTML  = '<input type="number" id="item_total'+index+'" class="form-control" placeholder="Total" disabled/>';
				cell5.innerHTML  = '<button type="button" onclick="removeRow(this)" class="btn btn-danger" tabindex="1"><i class="fas fa-times"></i></button> '+
								'<button type="button" class="btn btn-primary" onclick="addNewRow()" ><i class="fa fa-plus"></i></button>';
				index++;
				tableTotal();
			}
		}

		function rowTotal(index){
			var item_rate = parseFloat($('#item_rate'+index+'').val());
			var item_qty = parseFloat($('#item_qty'+index+'').val());   
			var item_total = item_rate * item_qty;

			$('#item_total'+index+'').val(item_total.toFixed());
			
			tableTotal();
		}

		function tableTotal(){
			var totalQuantity=0;
			var totalAmount=0;
			var tableRows = $("#PurPOTbleBody tr").length;
			var table = document.getElementById('myTable').getElementsByTagName('tbody')[0];

			for (var i = 0; i < tableRows; i++) {
				var currentRow =  table.rows[i];
				totalQuantity = totalQuantity + Number(currentRow.cells[2].querySelector('input').value);
				totalAmount = totalAmount + Number(currentRow.cells[3].querySelector('input').value);
			}

			$('#total_qty').val(totalQuantity);
			$('#total_amt').val(totalAmount.toFixed());
		}

	</script> -->


	
@endsection

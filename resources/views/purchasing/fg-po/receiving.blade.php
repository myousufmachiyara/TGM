@extends('layouts.app')

@section('title', 'Purchasing | FGPO Receiving')

@section('content')
  <div class="row">
    <form action="{{ route('pur-fgpos.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @if ($errors->has('error'))
        <strong class="text-danger">{{ $errors->first('error') }}</strong>
      @endif
      <div class="row">
        <div class="col-12 mb-4">
          <section class="card">
            <header class="card-header">
              <div style="display: flex;justify-content: space-between;">
                <h2 class="card-title">FGPO Receiving</h2>
              </div>
            </header>
            <div class="card-body">
              <div class="row mb-4">
                <div class="col-12 col-md-2">
                  <label>GRN #</label>
                  <input type="text" class="form-control" placeholder="GRN #" disabled/>
                </div>
                <div class="col-12 col-md-2 mb-3">
                  <label>Select PO</label>
                  <select data-plugin-selecttwo class="form-control select2-js" name="vendor_name" required>  <!-- Added name attribute for form submission -->
                    <option value="" selected disabled>Select PO</option>
                    @foreach ($purpos as $item)
                      <option value="{{ $item->id }}">PO-{{ $item->id }}</option> 
                    @endforeach
                  </select>
                </div>
                
                <div class="col-12 col-md-2">
                  <label>Receiving Date</label>
                  <input type="date" name="order_date" class="form-control" value="<?php echo date('Y-m-d'); ?>"   placeholder="Order Date" required/>
                </div>
                
                <div class="col-12 col-md-1">
                  <label>PO Item</label>
                  <button type="button" class="d-block btn btn-success" id="generate-variations-btn" >Get Data</button>
                </div>

              </div>
            </div>
          </section>
        </div>
        <div class="col-12 mb-4">
          <section class="card">
            <header class="card-header">
              <h2 class="card-title">Fabric Details</h2>
            </header>
            
            <div class="card-body">
              <table class="table table-bordered" id="myTable">
                <thead>
                  <tr>
                    <th>Item</th>
                    <th>Ordered Quantity</th>
                    <th>Received</th>
                    <th>Remaining</th>
                    <th>Receiving</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody id="PurPOTbleBody">
                  <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><input type="number" id="" class="form-control" placeholder="Receiving"/></td>
                    <td>
                      <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-xs" tabindex="1"><i class="fas fa-times"></i></button>
                      <button type="button" class="btn btn-primary btn-xs" onclick="addNewRow()" ><i class="fa fa-plus"></i></button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <footer class="card-footer text-end mt-1">
                <a class="btn btn-danger" href="{{ route('pur-fgpos.index') }}" >Discard</a>
                <button type="submit" class="btn btn-primary">Create</button>
              </footer>
            </div>
          </section>
        </div>
      </div>
    </form>
  </div>


@endsection
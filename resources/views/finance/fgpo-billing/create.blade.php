@extends('layouts.app')

@section('title', 'Finance | New Bill')

@section('content')
  <div class="row">
    <form action="{{ route('pur-fgpos.store-rec') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @if ($errors->has('error'))
        <strong class="text-danger">{{ $errors->first('error') }}</strong>
      @endif
      <div class="row">
        <div class="col-12 mb-4">
          <section class="card">
            <header class="card-header">
              <div style="display: flex;justify-content: space-between;">
                <h2 class="card-title">New Bill</h2>
              </div>
            </header>
            <div class="card-body">
              <div class="row mb-4">
                <div class="col-12 col-md-2">
                  <label>Bill No</label>
                  <input type="text" class="form-control" placeholder="Bill #" disabled/>
                </div>
                <div class="col-12 col-md-2 mb-3">
                  <label>Vendor</label>
                  <select data-plugin-selecttwo class="form-control select2-js" required>  <!-- Added name attribute for form submission -->
                    <option value="" selected disabled>Select Vendor</option>
                    @foreach ($coa as $item)
                      <option value="{{ $item->id }}">{{ $item->name }}</option> 
                    @endforeach
                  </select>
                </div>
                
                <div class="col-12 col-md-2">
                  <label>Bill Date</label>
                  <input type="date" name="bill_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required/>
                </div>
                
                <div class="col-12 col-md-2 mb-3">
                  <label>Ref Bill #</label>
                  <input type="number" class="form-control" placeholder="Ref Bill #"/>
                </div>

              </div>
            </div>
          </section>
        </div>
        <div class="col-12 mb-4">
          <section class="card">
            <header class="card-header">
              <h2 class="card-title">Bill Details</h2>
            </header>
            <div class="card-body">
              <div class="row">
                <div class="col-12 col-md-4 mb-3">
                  <label>Search PO No.</label>
                  <select multiple data-plugin-selecttwo class="form-control select2-js" required>  <!-- Added name attribute for form submission -->
                    <option value="" disabled>Select PO</option>
                    @foreach ($fgpo as $item)
                      <option value="{{ $item->id }}">{{ $item->doc_code }} - {{ $item->id }}  </option> 
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-md-1">
                  <label>PO Details</label>
                  <button type="button" class="d-block btn btn-success">Get Details</button>
                </div>
                <div class="col-12 col-md-1">
                  <label>Refresh</label>
                  <button type="button" class="d-block btn btn-danger"><i class="bx bx-refresh"></i></button>
                </div>
              </div>
              <table class="table table-bordered" id="myTable">
                <thead>
                  <tr>
                    <th>PO#</th>
                    <th>Items</th>
                    <th>Total Fabric</th>
                    <th>Qty Ordered</th>
                    <th>Qty Received</th>
                    <th>Consumption</th>
                    <th>Fabric Amount</th>
                    <th>Rate</th>
                    <th>Total</th>
                    <th>Adjustment</th>
                  </tr>
                </thead>
                <tbody id="POBillTbleBody">
               
                </tbody>
              </table>
              <footer class="card-footer text-end mt-1">
                <a class="btn btn-danger" href="{{ route('pur-fgpos.index') }}" >Discard</a>
                <button type="submit" class="btn btn-primary">Add Bill</button>
              </footer>
            </div>
          </section>
        </div>
      </div>
    </form>
  </div>
  <script>
    function getPODetails(){    
      var itemId;
      if(option==1){
        itemId = document.getElementById("item_code"+row_no).value;
      }
      else if(option==2){
        itemId = document.getElementById("item_name"+row_no).value;
      }
      $.ajax({
        type: "GET",
        url: "/items/detail",
        data: {id:itemId},
        success: function(result){
         
        },
        error: function(){
          alert("error");
        }
      });
    }
  </script>
@endsection
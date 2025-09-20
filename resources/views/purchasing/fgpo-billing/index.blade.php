@extends('layouts.app')

@section('title', 'Finance | PO Bills')

@section('content')
  <div class="row">
    <div class="col">
      <section class="card">
        <header class="card-header" style="display: flex;justify-content: space-between;">
          <h2 class="card-title">All Bills</h2>
          <div>
            <a class="btn btn-primary text-end" href="{{ route('fgpo-bills.create') }}"  aria-expanded="false" > <i class="fa fa-plus"></i> New Bill</a>
          </div>
        </header>
        <div class="card-body">
          <div>
            <div class="col-md-5" style="display:flex;">
              <select class="form-control" style="margin-right:10px" id="columnSelect">
                <option selected disabled>Search by</option>
                <option value="1">by Delivery Date</option>
                <option value="2">by Order Date</option>
                <option value="3">by Vendor</option>
              </select>
              <input type="text" class="form-control" id="columnSearch" placeholder="Search By Column"/>

            </div>
          </div>

          <div class="modal-wrapper table-scroll">
            <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
              <thead>
                <tr>
                  <th>S.No</th>
                  <th>Ref Document</th>
                  <th>Debit Account</th>
                  <th>Credit Account</th>
                  <th>Amount</th>
                  <th>Narration</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                            
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>
  </div>
@endsection
@extends('layouts.app')

@section('title', 'Product | Categories')

@section('content')
  <div class="row">
    <div class="col">
      <section class="card">
        <header class="card-header" style="display: flex;justify-content: space-between;">
            <h2 class="card-title">All Categories</h2>
            <a  class="btn btn-primary text-end" aria-expanded="false" > <i class="fa fa-plus"></i> Add New</a>
        </header>
        <div class="card-body">
          <div class="modal-wrapper table-scroll">
            <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
              <thead>
                <tr>
                <th>S.No</th>
                <th>Name</th>
                <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($items as $item)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->name }}</td> <!-- Adjusted based on the entity -->
                    <td>
                      <!-- Edit button with dynamic data attributes -->
                      <button class="btn btn-warning btn-sm edit-entity-button">
                        Edit
                      </button>
                      <!-- Delete form -->
                      <form action="" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                      </form>
                    </td>
                  </tr>
                  @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <div id="deleteModal" class="zoom-anim-dialog modal-block modal-block-danger mfp-hide">
            <form method="post" action="" enctype="multipart/form-data">
                @csrf
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Delete COA Group</h2>
                    </header>
                    <div class="card-body">
                        <div class="modal-wrapper">
                            <div class="modal-icon">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div class="modal-text">
                                <p class="mb-0">Are you sure that you want to delete this COA Group?</p>
                                <input name="acc_group_cod" id="deleteID" hidden>
                            </div>
                        </div>
                    </div>
                    <footer class="card-footer">
                        <div class="row">
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-danger">Delete</button>
                                <button class="btn btn-default modal-dismiss">Cancel</button>
                            </div>
                        </div>
                    </footer>
                </section>
            </form>
        </div>

        <div id="addModal" class="modal-block modal-block-primary mfp-hide">
            <section class="card">
                <form method="post" action="{{ route('product-categories.store') }}" enctype="multipart/form-data" onkeydown="return event.key != 'Enter';">
                    @csrf
                    <header class="card-header">
                        <h2 class="card-title">Add COA Group</h2>
                    </header>
                    <div class="card-body">
                        <div class="form-group mt-2">
                            <input type="number" class="form-control" placeholder="group code" disabled>
                        </div>
                        <div class="form-group mb-3">
                            <label>Account Group Name<span style="color: red;"><strong>*</strong></span></label>
                            <input type="text" class="form-control" placeholder="Name" name="acc_group_name" required>
                        </div>
                    </div>
                    <footer class="card-footer">
                        <div class="row">
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary">Add COA Group</button>
                                <button class="btn btn-default modal-dismiss">Cancel</button>
                            </div>
                        </div>
                    </footer>
                </form>
            </section>
        </div>

        <div id="updateModal" class="modal-block modal-block-primary mfp-hide">
            <section class="card">
                <form method="post" action="" enctype="multipart/form-data" onkeydown="return event.key != 'Enter';">
                    @csrf
                    <header class="card-header">
                        <h2 class="card-title">Update COA Group</h2>
                    </header>
                    <div class="card-body">
                       <div class="form-group">
                            <input type="number" class="form-control" id="update_group_id" required disabled>
                        </div>
                        <div class="form-group">
                            <label>Account Group Name<span style="color: red;"><strong>*</strong></span></label>
                            <input type="text" class="form-control" id="update_group_name" placeholder="Name" name="group_name" required>
                            <input type="hidden" class="form-control" id="group_id" name="group_cod" required>
                        </div>
                    </div>
                    <footer class="card-footer">
                        <div class="row">
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary">Update COA Group</button>
                                <button class="btn btn-default modal-dismiss">Cancel</button>
                            </div>
                        </div>
                    </footer>
                </form>
            </section>
        </div>
    </div>
  </div>



@endsection

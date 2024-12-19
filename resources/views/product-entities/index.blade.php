@extends('layouts.app')

@section('title', ucfirst(str_replace('_', ' ', $entity)) . ' | All Items')

@section('content')
<div class="page-header d-flex justify-content-end">
  <ul class="breadcrumbs mb-3">
    <li class="nav-home">
      <a href="#">
        <i class="fa fa-home"></i>
      </a>
    </li>
    <li class="separator">
      <i class="fa fa-chevron-right"></i>
    </li>
    <li class="nav-item">
      <a href="#">Product</a>
    </li>
    <li class="separator">
      <i class="fa fa-chevron-right"></i>
    </li>
    <li class="nav-item">
      <a href="#">{{ ucfirst(str_replace('_', ' ', $entity)) }}</a>
    </li>
  </ul>
</div>

<div class="col-md-12">
  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <h4 class="card-title">{{ ucfirst(str_replace('_', ' ', $entity)) }}</h4>
      <a class="btn btn-primary" id="create-entity-button" data-entity="{{ $entity }}" aria-expanded="false">
        <i class="fa fa-plus"></i> Add New 
      </a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="basic-datatables" class="display table table-striped table-hover table-bordered">
          <thead>
            <tr>
              <th>S.No</th>
              <th>Name</th> <!-- Adjusted the column to "Name" dynamically -->
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
                <button class="btn btn-warning btn-sm edit-entity-button" data-entity="{{ $entity }}" data-id="{{ $item->id }}" data-name="{{ $item->name }}">
                  Edit
                </button>
                <!-- Delete form -->
                <form action="{{ route('entity.destroy', ['entity' => $entity, 'resource' => $item->id]) }}" method="POST" style="display:inline-block;">
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
  </div>
</div>

<script>
  $(document).ready(function () {
    // Handle "Add New" button click
    $("#create-entity-button").click(function (e) {
      var entity = $(this).data('entity');  // Get the dynamic entity from the button

      // Show SweetAlert popup for entity creation
      swal({
        title: "Add New " + entity.replace('_', ' '),
        content: {
          element: "form",
          attributes: {
            innerHTML: `
              <input type="text" name="name" class="form-control" placeholder="Enter name" required><br>
              <!-- You can add more fields based on the entity here -->
            `,
          },
        },
        buttons: {
          cancel: {
            visible: true,
            className: "btn btn-danger",
          },
          confirm: {
            className: "btn btn-primary",
            text: "Add",
          },
        },
      }).then(function (result) {
        if (result) {
          var formData = new FormData();
          formData.append('name', $("input[name='name']").val()); // Add the name field value (you can add more fields here)
          var csrfToken = $('meta[name="csrf-token"]').attr('content');

          // Send the form data to the appropriate URL using AJAX
          $.ajax({
            url: '/' + entity, // The dynamic URL based on the entity (e.g., /product_categories)
            method: 'POST',
            data: formData,
            processData: false,  // Don't process the data
            contentType: false,
            headers: {
              'X-CSRF-TOKEN': csrfToken  // Include CSRF token in the header
            },
            success: function (response) {
              if (response.success) {
                swal("Success", "New " + entity.replace('_', ' ') + " created successfully!", "success");
                location.reload();  // Reload the page to see the new entity in the table
              } else {
                swal("Error", response.message, "error");
              }
            },
            error: function (xhr, status, error) {
              swal("Error", "There was an error while creating the entity.", "error");
            }
          });
        }
      });
    });

    // Edit entity functionality
    $(".edit-entity-button").click(function (e) {
    var entity = $(this).data('entity'); // Get the dynamic entity from the button
    var id = $(this).data('id');         // Get the entity ID from the button
    var currentName = $(this).data('name'); // Get the current name from the button

    // Show SweetAlert popup for entity update
    swal({
        title: "Update",
        content: {
            element: "form",
            attributes: {
                innerHTML: `
                    <input type="text" name="name" class="form-control" value="${currentName}" required><br>
                `,
            },
        },
        buttons: {
            cancel: {
                visible: true,
                className: "btn btn-danger",
            },
            confirm: {
                className: "btn btn-primary",
                text: "Update",
            },
        },
    }).then(function (result) {
        if (result) {
            var formData = new FormData();
            formData.append('name', $("input[name='name']").val()); // Add the updated name field value

            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Send the form data to the appropriate URL using AJAX
            $.ajax({
                url: '/' + entity + '/' + id, // The dynamic URL for the update (e.g., /product_categories/{id})
                method: 'PUT',
                data: formData,
                processData: false,  // Don't process the data
                contentType: false,  // Don't set content-type
                headers: {
                    'X-CSRF-TOKEN': csrfToken  // Include CSRF token in the header
                },
                success: function(response) {
                    if(response.success) {
                        swal("Success", entity.replace('_', ' ') + " updated successfully!", "success");
                        location.reload();  // Reload the page to see the updated entity in the table
                    } else {
                        swal("Error", response.message, "error");
                    }
                },
                error: function(xhr, status, error) {
                    swal("Error", "There was an error while updating the entity.", "error");
                }
            });
        }
    });
});
  });
</script>

@endsection

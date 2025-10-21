<?php
// Set the page title dynamically
$pageTitle = "A - User Table";

// Include the header
include $_SERVER['DOCUMENT_ROOT'] . '/php_views/asset_for_pages/owner_header.php';

// Example array of user data
$userData = [
  [
    'id'     => 'Saniket',
    'name'   => 'Aniket Saroha',
    'status' => 'Active'
  ],
  [
    'id'     => 'GBale',
    'name'   => 'Gareth Bale',
    'status' => 'Active'
  ],
  [
    'id'     => 'RLouis',
    'name'   => 'Louis Rai',
    'status' => 'Inactive'
  ],
];
?>

<div class="page-inner">
  <div class="page-header">
    <h3 class="fw-bold mb-3">User Table</h3>
    <ul class="breadcrumbs mb-3">
      <li class="nav-home">
        <a href="#">
          <i class="icon-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="icon-arrow-right"></i>
      </li>
      <li class="nav-item">
        <a href="#">Tables</a>
      </li>
      <li class="separator">
        <i class="icon-arrow-right"></i>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <!-- CARD FOR USER TABLE -->
      <div class="card">
        <div class="card-header">
          <div class="d-flex align-items-center">
            <h4 class="card-title">User Table</h4>
            <!-- Button to open the modal for adding a new row -->
            <button
              class="btn btn-primary btn-round ms-auto"
              data-bs-toggle="modal"
              data-bs-target="#addRowModal"
            >
              <i class="fa fa-plus"></i>
              Add Row
            </button>
          </div>
        </div>

        <div class="card-body">
          <!-- MODAL FOR ADDING A NEW ROW -->
          <div
            class="modal fade"
            id="addRowModal"
            tabindex="-1"
            role="dialog"
            aria-hidden="true"
          >
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header border-0">
                  <h5 class="modal-title">
                    <span class="fw-mediumbold">New</span>
                    <span class="fw-light">Row</span>
                  </h5>
                  <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close"
                  >
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <p class="small">
                    Create a new row using this form, make sure you fill them all
                  </p>
                  <form>
                    <div class="row">
                      <div class="col-sm-12">
                        <div class="form-group form-group-default">
                          <label>User ID</label>
                          <input
                            id="sn"
                            type="text"
                            class="form-control"
                            placeholder="This is autogen"
                          />
                        </div>
                      </div>
                      <div class="col-sm-12">
                        <div class="form-group form-group-default">
                          <label>User Name</label>
                          <input
                            id="addName"
                            type="text"
                            class="form-control"
                            placeholder="User Name"
                          />
                        </div>
                      </div>
                      <div class="col-md-6 pe-0">
                        <div class="form-group form-group-default">
                          <label>Status</label>
                          <input
                            id="addAccessCode"
                            type="text"
                            class="form-control"
                            placeholder="Active/Inactive"
                          />
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group form-group-default">
                          <label>Edit/Delete</label>
                          <input
                            id="addStatus"
                            type="text"
                            class="form-control"
                            placeholder="Dropdown or Buttons"
                          />
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
                <div class="modal-footer border-0">
                  <button
                    type="button"
                    id="addRowButton"
                    class="btn btn-primary"
                  >
                    Add
                  </button>
                  <button
                    type="button"
                    class="btn btn-danger"
                    data-dismiss="modal"
                  >
                    Close
                  </button>
                </div>
              </div>
            </div>
          </div>
          <!-- END MODAL -->

          <!-- TOGGLE BUTTON & FILTERS ABOVE THE TABLE -->
          <button id="toggleFilterBtn" class="btn btn-outline-primary mb-3">
            Show Filters
          </button>

          <div id="filterSection" class="row mb-3" style="display: none;">
            <div class="col-md-3">
              <label for="filterUserID" class="form-label">Filter by User ID</label>
              <input
                type="text"
                class="form-control"
                id="filterUserID"
                placeholder="e.g. RLouis"
              />
            </div>
            <div class="col-md-3">
              <label for="filterUserName" class="form-label">Filter by User Name</label>
              <input
                type="text"
                class="form-control"
                id="filterUserName"
                placeholder="e.g. Gareth Bale"
              />
            </div>
            <div class="col-md-3">
              <label for="filterStatus" class="form-label">Filter by Status</label>
              <!-- Using regex to enforce exact matches for Active/Inactive -->
              <select class="form-select" id="filterStatus">
                <option value="">All</option>
                <option value="^Active$">Active</option>
                <option value="^Inactive$">Inactive</option>
              </select>
            </div>
          </div>
          <!-- END FILTERS ABOVE THE TABLE -->

          <div class="table-responsive">
            <table
              id="multi-filter-select"
              class="display table table-striped table-hover"
            >
              <thead>
                <tr>
                  <th>User ID</th>
                  <th>User Name</th>
                  <th>Status</th>
                  <th style="width: 15%">Edit/Delete</th>
                </tr>
              </thead>
              <tfoot>
                <tr>
                  <th>User ID</th>
                  <th>User Name</th>
                  <th>Status</th>
                  <th>Edit/Delete</th>
                </tr>
              </tfoot>
              <tbody>
                <?php foreach ($userData as $user): ?>
                <tr>
                  <td><?= htmlspecialchars($user['id']) ?></td>
                  <td><?= htmlspecialchars($user['name']) ?></td>
                  <td><?= htmlspecialchars($user['status']) ?></td>
                  <td>
                    <div class="form-button-action">
                      <button
                        type="button"
                        data-bs-toggle="tooltip"
                        class="btn btn-link btn-primary btn-lg"
                        data-original-title="Edit Task"
                      >
                        <i class="fa fa-edit"></i>
                      </button>
                      <button
                        type="button"
                        data-bs-toggle="tooltip"
                        class="btn btn-link btn-danger"
                        data-original-title="Remove"
                      >
                        <i class="fa fa-times"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- END CARD FOR USER TABLE -->
    </div>
  </div>
</div>

<!-- REQUIRED SCRIPTS -->
<!-- jQuery, DataTables, and Bootstrap (or your front-end framework) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<!-- If you need Bootstrap JavaScript for modals, etc. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Toggle filter visibility
  document.getElementById('toggleFilterBtn').addEventListener('click', function() {
    const filterSection = document.getElementById('filterSection');
    if (filterSection.style.display === 'none') {
      filterSection.style.display = 'flex';
      this.textContent = 'Hide Filters';
    } else {
      filterSection.style.display = 'none';
      this.textContent = 'Show Filters';
    }
  });

  $(document).ready(function() {
    // Initialize DataTable with regex searching
    const table = $('#multi-filter-select').DataTable({
      search: {
        regex: true,
      },
      // If you want to disable global search box:
      // searching: false,
    });

    // Filter by User ID (Partial Match)
    $('#filterUserID').on('keyup change', function() {
      table.column(0).search(this.value).draw();
    });

    // Filter by User Name (Partial Match)
    $('#filterUserName').on('keyup change', function() {
      table.column(1).search(this.value).draw();
    });

    // Filter by Status (Exact Match via regex)
    $('#filterStatus').on('change', function() {
      // 'true' for regex search, 'false' for smart search
      table.column(2).search(this.value, true, false).draw();
    });
  });

  // Example script for adding row on the fly (client-side only)
  document.getElementById('addRowButton').addEventListener('click', function () {
    const userID = document.getElementById('sn').value.trim();
    const userName = document.getElementById('addName').value.trim();
    const userStatus = document.getElementById('addAccessCode').value.trim();

    // DataTables instance
    const table = $('#multi-filter-select').DataTable();

    // Add row to DataTables
    table.row.add([
      userID,
      userName,
      userStatus,
      `<div class="form-button-action">
        <button type="button" class="btn btn-link btn-primary btn-lg">
          <i class="fa fa-edit"></i>
        </button>
        <button type="button" class="btn btn-link btn-danger">
          <i class="fa fa-times"></i>
        </button>
      </div>`
    ]).draw(false);

    // Clear form fields
    document.getElementById('sn').value = '';
    document.getElementById('addName').value = '';
    document.getElementById('addAccessCode').value = '';
    document.getElementById('addStatus').value = '';

    // Hide the modal
    const modalElement = document.getElementById('addRowModal');
    if (modalElement) {
      const modalInstance = new bootstrap.Modal(modalElement);
      modalInstance.hide();
    }
  });
</script>

<?php
// Include the footer
include('../asset_for_pages/footer.php');
?>
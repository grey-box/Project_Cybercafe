<?php
// Sample user data array
$user_data = [
    ['GBale', 'Gareth Bale', 'Active'],
    ['RLouis', 'Louis Rai', 'Inactive'],
    ['Saniket', 'Aniket Saroha', 'Active'],
    ['JSmith', 'John Smith', 'Inactive'],
    ['ADoe', 'Alice Doe', 'Active'],
    ['TJohnson', 'Tom Johnson', 'Active'],
    ['LWilliams', 'Liam Williams', 'Inactive'],
    ['EBrown', 'Emily Brown', 'Active'],
    ['MBaker', 'Mason Baker', 'Inactive'],
    ['SWhite', 'Sophia White', 'Active']
];

// Set the page title dynamically
$pageTitle = "O - Add User"; 

// Include the header
include $_SERVER['DOCUMENT_ROOT'] . '/php_views/asset_for_pages/owner_header.php';
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
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <h4 class="card-title">User Table</h4>
                                        <a href="add_user.php" class="btn btn-primary btn-round ms-auto">
                                          <i class="fa fa-plus"></i>
                                            Add User
                                          </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Multi-filter dropdowns above table -->
                                    <div class="d-flex mb-3">
                                        <select id="filter-user-id" class="form-select me-2">
                                            <option value="">Filter by User ID</option>
                                        </select>
                                        <select id="filter-user-name" class="form-select me-2">
                                            <option value="">Filter by User Name</option>
                                        </select>
                                        <select id="filter-status" class="form-select">
                                            <option value="">Filter by Status</option>
                                        </select>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="multi-filter-select" class="display table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>User ID</th>
                                                    <th>User Name</th>
                                                    <th>Status</th>
                                                    <th>Delete</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($user_data as $user) : ?>
                                                    <tr>
                                                        <td><?= $user[0] ?></td>
                                                        <td><?= $user[1] ?></td>
                                                        <td><?= $user[2] ?></td>
                                                        <td><button class="btn btn-danger btn-sm delete-btn">Delete</button></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

    


<?php
// Include the footer
include('../asset_for_pages/footer.php');
?>
<script>
      $(document).ready(function () {
        if ($.fn.DataTable.isDataTable("#multi-filter-select")) {
    $("#multi-filter-select").DataTable().destroy();
  }
        var table = $("#multi-filter-select").DataTable({
          pageLength: 5,
          lengthMenu: [5, 10],
        });

        function populateFilter(selectId, columnIdx) {
          var column = table.column(columnIdx);
          var select = $(selectId);
          select.append('<option value="">All</option>');
          column.data().unique().sort().each(function (d, j) {
            select.append('<option value="' + d + '">' + d + '</option>');
          });
          select.on("change", function () {
            var val = $.fn.dataTable.util.escapeRegex($(this).val());
            column.search(val ? "^" + val + "$" : "", true, false).draw();
          });
        }

        populateFilter("#filter-user-id", 0);
        populateFilter("#filter-user-name", 1);
        populateFilter("#filter-status", 2);

        $("#multi-filter-select tbody").on("click", ".delete-btn", function () {
          table.row($(this).parents("tr")).remove().draw();
        });
      });
    </script>
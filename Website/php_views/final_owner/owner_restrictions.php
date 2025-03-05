<?php
// Set the page title dynamically
$pageTitle = "O - Restrictions"; 

// Include the header
include('../asset_for_pages/owner_header.php');

$users = [
    ['username' => 'Admin', 'currentSpeedLane' => 'High'],
    ['username' => 'Regular User', 'currentSpeedLane' => 'Mid'],
    ['username' => 'Guest User', 'currentSpeedLane' => 'Mid'],
];

?>

<style>
  .info-icon {
    font-size: 1.5em;
    color: blue;
    cursor: pointer;
  }
  .tooltip-inner {
    font-size: 1.25em;
  }
</style>

<div class="page-header">
    <ul class="breadcrumbs mb-3">
        <li class="nav-home">
            <a href="#">
                <i class="fas fa-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="fas fa-chevron-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Restrictions</a>
        </li>
        <li class="separator">
            <i class="fas fa-chevron-right"></i>
        </li>
    </ul>
</div>

<!-- Restriction Cards Section -->
<div class="row">

    <!-- Card 1: URL Restrictions -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">URL Block List</div>
                <span class="info-icon" data-toggle="tooltip" title="Restrict specific URLs by adding them here. You can also remove them from the list below.">
                    <i class="fas fa-info-circle"></i>
                </span>
            </div>
            <div class="card-body">
                <input type="text" class="form-control mb-3" placeholder="Add URL" onfocus="showSuggestions('urlSuggestions')" list="urlSuggestions">
                <datalist id="urlSuggestions">
                    <option value="https://example.com">
                    <option value="https://anotherurl.com">
                    <option value="https://website.com">
                </datalist>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Item List</th>
                                <th>Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>https://example.com</td>
                                <td><button class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                            </tr>
                            <tr>
                                <td>https://anotherurl.com</td>
                                <td><button class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Allowed URLs for Guest Users -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">Allowed URLs for Guest User</div>
                <span class="info-icon" data-toggle="tooltip" title="Allow specific URLs for guest users by adding them here. You can also remove them from the list below.">
                    <i class="fas fa-info-circle"></i>
                </span>
            </div>
            <div class="card-body">
                <input type="text" class="form-control mb-3" placeholder="Add URL" onfocus="showSuggestions('guestUrlSuggestions')" list="guestUrlSuggestions">
                <datalist id="guestUrlSuggestions">
                    <option value="https://example.com">
                    <option value="https://anotherurl.com">
                    <option value="https://website.com">
                </datalist>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Item List</th>
                                <th>Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>https://example.com</td>
                                <td><button class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                            </tr>
                            <tr>
                                <td>https://anotherurl.com</td>
                                <td><button class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">Speed Lane Assigning</div>
                    <span class="info-icon" data-toggle="tooltip" title="Assign different bandwidth tiers (Low, Mid, High) to users, controlling their internet speed based on their needs.">
                        <i class="fas fa-info-circle"></i>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Left Side: Speed Lane Input Fields -->
                        <div class="col-md-6">
                            <h5>Speed Lane</h5>
                            <p>Define bandwidth limits for different user categories.</p>
                            <div class="mb-3">
                                <label for="lowSpeed">Low:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="lowSpeed" value="13434">
                                    <span class="input-group-text">Mbps</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="midSpeed">Mid:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="midSpeed" value="234">
                                    <span class="input-group-text">Mbps</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="highSpeed">High:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="highSpeed" value="234">
                                    <span class="input-group-text">Mbps</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side: Assigning Users -->
                        <div class="col-md-6">
                            <h5>Assign Users</h5>
                            <p>Select a speed lane for each user below.</p>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Speed Lane</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user) : ?>
                                        <tr>
                                            <td><?php echo $user['username']; ?></td>
                                            <td>
                                                <select class="form-control">
                                                    <option <?php echo $user['currentSpeedLane'] == 'Low' ? 'selected' : ''; ?>>Low</option>
                                                    <option <?php echo $user['currentSpeedLane'] == 'Mid' ? 'selected' : ''; ?>>Mid</option>
                                                    <option <?php echo $user['currentSpeedLane'] == 'High' ? 'selected' : ''; ?>>High</option>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!-- speed late assigning end -->

</div>

<?php
// Include the footer
include('../asset_for_pages/footer.php');
?>

<script>
// Function to confirm and remove a row
function removeRow(button) {
    const confirmation = confirm("Do you really want to remove this item?");
    if (confirmation) {
        const row = button.closest('tr');
        row.parentNode.removeChild(row);
    }
}

// Enable tooltips
document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

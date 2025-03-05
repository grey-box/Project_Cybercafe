<?php
// Set the page title dynamically
$pageTitle = "O - Feature Toggle"; 

// Include the header
include('../asset_for_pages/header.php');

// Sample data for the features (this would come from a database or another source in a real application)
$features = [
    [
        'title' => 'Payments',
        'description' => 'This toggle enables or disables payment functionality.',
        'info' => 'This feature lets you use/disable the payments feature. When you enable this feature, this switches on the payment options for the regular users, which makes the users pay for the access to this service.',
        'toggleState' => true
    ],
    [
        'title' => 'User Restrictions',
        'description' => 'Toggle user restrictions for specific users based on device usage or data limits.',
        'info' => 'This feature lets you apply restrictions to users based on device usage, data usage, or other parameters.',
        'toggleState' => true
    ],
    [
        'title' => 'URL Restrictions',
        'description' => 'Enable or disable URL-based restrictions for user access.',
        'info' => 'This feature lets you restrict access to certain URLs or websites based on the device or user level.',
        'toggleState' => false
    ]
];

// Simulate getting the Data Sharing Percentage from the database or other source
$dataSharingPercentage = 50; // This can be dynamically fetched from the database

// Simulate user list data for speed lanes (you could dynamically fetch this from the database)
$users = [
    ['username' => 'Admin', 'currentSpeedLane' => 'High'],
    ['username' => 'Regular User', 'currentSpeedLane' => 'Mid'],
];
?>

<!-- Start of the Container -->
<div class="container">
  <div class="page-inner">
    <div class="page-header">
      <h3 class="fw-bold mb-3">Feature List</h3>
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
          <a href="#">Feature Toggle</a>
        </li>
        <li class="separator">
          <i class="icon-arrow-right"></i>
        </li>
      </ul>
    </div>

    <div class="row">
      <!-- Loop through the features array -->
      <?php foreach ($features as $feature) : ?>
        <div class="col-md-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div class="card-title"><?php echo $feature['title']; ?></div>
              <!-- Info Icon -->
              <button class="info-button" aria-label="More Info" onclick="toggleTooltip('<?php echo $feature['title']; ?>')">
                <span>i</span>
              </button>
            </div>
            <div class="card-body">
              <p><?php echo $feature['description']; ?></p>
              <div class="form-check form-switch" style="--bs-form-switch-width:60px;--bs-form-switch-height:24px">
                <input class="form-check-input" type="checkbox" role="switch" id="switch<?php echo $feature['title']; ?>" <?php echo $feature['toggleState'] ? 'checked' : ''; ?> />
              </div>
              <!-- Tooltip content -->
              <div class="info-tooltip" id="tooltip<?php echo $feature['title']; ?>">
                <?php echo $feature['info']; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <!-- New Card for Data Sharing Percentage -->
      <div class="col-md-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">Data Sharing Percentage</div>
            <button class="info-button" aria-label="More Info" onclick="toggleTooltip('dataSharing')">
              <span>i</span>
            </button>
          </div>
          <div class="card-body">
            <p>This toggle enables or disables the data sharing percentage with other users. Adjust the slider to control the data you want to share.</p>

            <!-- Slider and Text Field for Data Sharing Percentage -->
            <div class="form-group d-flex justify-content-between align-items-center">
              <div style="flex: 1; display: flex; align-items: center;">
                <!-- Slider takes 60% width -->
                <input type="range" class="form-control-range" id="dataSharingSlider" min="10" max="100" value="<?php echo $dataSharingPercentage; ?>" oninput="updateDataSharingValue(this.value)" style="width: 60%;"/>
                
                <!-- Text field takes 30% width -->
                <input type="number" class="form-control" id="dataSharingValue" min="10" max="100" value="<?php echo $dataSharingPercentage; ?>" oninput="updateSliderValue(this.value)" style="width: 30%; margin-left: 10px;" />
                
                <!-- Percentage sign takes 10% width -->
                <span style="margin-left: 5px; font-size: 16px;">%</span>
              </div>
            </div>

            <!-- Tooltip content -->
            <div class="info-tooltip" id="tooltipDataSharing">
              This feature allows you to set the percentage of data you want to share with others. Adjust the slider or enter the value manually to control the percentage of data shared.
            </div>
          </div>
        </div>
      </div>

      <!-- New Card for Speed Lane -->
      <div class="col-md-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">Speed Lane</div>
            <button class="info-button" aria-label="More Info" onclick="toggleTooltip('speedLane')">
              <span>i</span>
            </button>
          </div>
          <div class="card-body">
            <p>The Speed Lane feature allows you to assign different bandwidth tiers (Low, Mid, High) to each user. You can control the speed for each user using the dropdown below.</p>

            <!-- Users and Speed Lane Dropdown -->
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
            
            <!-- Tooltip content -->
            <div class="info-tooltip" id="tooltipSpeedLane">
              This feature allows you to set the speed lane (Low, Mid, High) for each user. The speed lane controls the bandwidth allocation for users.
            </div>
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
// Function to toggle the visibility of the tooltip
function toggleTooltip(feature) {
    const tooltip = document.getElementById('tooltip' + capitalizeFirstLetter(feature));
    tooltip.style.display = tooltip.style.display === 'block' ? 'none' : 'block';
}

// Function to capitalize the first letter of a string (for dynamic tooltip IDs)
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// Function to update the value of the text field based on slider
function updateDataSharingValue(value) {
    document.getElementById('dataSharingValue').value = value;
}

// Function to update the slider value based on text field input
function updateSliderValue(value) {
    if (value >= 10 && value <= 100) {
        document.getElementById('dataSharingSlider').value = value;
    }
}
</script>

<style>
/* Styling for info button and tooltip */
.info-button {
    background-color: transparent;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #007bff;
}

.info-tooltip {
    display: none;
    margin-top: 5px;
    padding: 10px;
    background-color: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
    font-size: 14px;
    color: #333;
}

.card-header .info-button-wrapper {
    position: relative;
    display: inline-block;
}

.card-body p {
    margin-bottom: 15px;
}
</style>

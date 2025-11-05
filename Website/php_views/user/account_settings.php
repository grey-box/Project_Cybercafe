<?php
declare(strict_types=1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';
$pageTitle = 'Account Settings';

require_once VIEWS_ROOT . '/asset_for_pages/user_header.php';
?>

<div class="container">
  <div class="page-inner">
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
      <div>
        <h3 class="fw-bold mb-1"><?= htmlspecialchars($pageTitle) ?></h3>
        <div class="text-muted">Manage profile, password, preferences, and sessions.</div>
      </div>
    </div>

    <!-- Profile -->
    <div class="card mb-4" style="border:0; box-shadow:0 8px 20px rgba(0,0,0,.06); border-radius:1rem;">
      <div class="card-body">
        <h5 class="mb-3">Profile</h5>
        <form id="profileForm" onsubmit="event.preventDefault(); fakeSave('Profile saved');">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full name</label>
              <input type="text" class="form-control" name="full_name" value="Aniket User">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email" value="aniket@yahoo.com">
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="tel" class="form-control" name="phone_number" placeholder="+1 480 555 1234">
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="submit"><i class="fa fa-save me-1"></i> Save</button>
              <button class="btn btn-outline-secondary ms-1" type="reset">Reset</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Security -->
    <div class="card mb-4" style="border:0; box-shadow:0 8px 20px rgba(0,0,0,.06); border-radius:1rem;">
      <div class="card-body">
        <h5 class="mb-3">Security</h5>
        <form id="passwordForm" onsubmit="event.preventDefault(); fakeSave('Password updated');">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Current password</label>
              <input type="password" class="form-control" name="current_password" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">New password</label>
              <input id="newPass" type="password" class="form-control" name="new_password" required>
              <div class="form-text">At least 8 characters.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Confirm new password</label>
              <input id="confirmPass" type="password" class="form-control" name="confirm_password" required>
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="submit"><i class="fa fa-key me-1"></i> Change password</button>
            </div>
          </div>
        </form>

        <hr class="my-4">
      </div>
    </div>

    <!-- Preferences -->
    <div class="card mb-4" style="border:0; box-shadow:0 8px 20px rgba(0,0,0,.06); border-radius:1rem;">
      <div class="card-body">
        <h5 class="mb-3">Preferences</h5>
        <form id="prefForm" onsubmit="event.preventDefault(); fakeSave('Preferences saved');">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Language</label>
              <select class="form-select" name="lang">
                <option value="en" selected>English</option>
                <option value="es">Spanish</option>
              </select>
            </div>
            <div class="col-md-8 d-flex align-items-center">
              <div class="form-check me-4">
                <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                <label class="form-check-label" for="emailNotif">Email notifications</label>
              </div>
              <div class="form-check me-4">
                <input class="form-check-input" type="checkbox" id="billingNotif" checked>
                <label class="form-check-label" for="billingNotif">Billing alerts</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="promoNotif">
                <label class="form-check-label" for="promoNotif">Promotions</label>
              </div>
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="submit"><i class="fa fa-save me-1"></i> Save</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Active sessions -->
    <div class="card mb-4" style="border:0; box-shadow:0 8px 20px rgba(0,0,0,.06); border-radius:1rem;">
      <div class="card-body">
        <h5 class="mb-3">Active sessions</h5>
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>Device</th>
                <th>IP</th>
                <th>Login time</th>
                <th>Queue</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="sessionTable">
              <tr>
                <td>Windows Desktop</td>
                <td>10.0.0.42</td>
                <td>2025-11-03 13:12</td>
                <td>slow-lane</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-danger" onclick="revokeSession(this)">Sign out</button>
                </td>
              </tr>
              <tr>
                <td>Android Phone</td>
                <td>10.0.0.58</td>
                <td>2025-11-03 18:45</td>
                <td>fast-lane</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-danger" onclick="revokeSession(this)">Sign out</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="mt-3">
          <button class="btn btn-outline-danger btn-sm" onclick="revokeAll()">Sign out from all devices</button>
        </div>
      </div>
    </div>

    <!-- Danger zone -->
    <div class="card mb-5" style="border:0; box-shadow:0 8px 20px rgba(0,0,0,.06); border-radius:1rem;">
      <div class="card-body">
        <h5 class="mb-2 text-danger">Danger zone</h5>
        <div class="text-muted mb-3 small">
          Request account deactivation. An admin will review the request.
        </div>
        <button class="btn btn-danger" onclick="confirmDeactivate()">Request deactivation</button>
      </div>
    </div>

  </div>
</div>

<script>
  // Simple password confirm check
  document.getElementById('passwordForm')?.addEventListener('submit', function(){
    const a = document.getElementById('newPass').value;
    const b = document.getElementById('confirmPass').value;
    if (a !== b) { alert('Passwords do not match'); throw new Error('no submit'); }
  });

  function fakeSave(msg){

    alert(msg + ' (placeholder. Later call POST to update the database.)');
  }

  function revokeSession(btn){

    const tr = btn.closest('tr');
    tr.parentNode.removeChild(tr);
    alert('Session revoked (placeholder). Later DELETE or UPDATE logout_timestamp in internet_session.');
  }

  function revokeAll(){
    const body = document.getElementById('sessionTable');
    body.innerHTML = '';
    alert('All sessions revoked (placeholder). Later UPDATE logout_timestamp for all active sessions.');
  }

  function confirmDeactivate(){
    if (!confirm('Send deactivation request to admin?')) return;
    alert('Deactivation request submitted (placeholder). Later INSERT into system_event or a tickets table.');
  }
</script>

<?php require_once VIEWS_ROOT . '/asset_for_pages/footer.php'; ?>

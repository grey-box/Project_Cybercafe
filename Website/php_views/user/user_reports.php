<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

$pageTitle = "Reports";

$totalReports = 6;
$sharedReports = 2;
$lastRunType = "Usage Summary";
$lastRunAt = "2025-10-31 21:10";

?>
<?php require_once VIEWS_ROOT . '/asset_for_pages/user_header.php'; ?>

<div class="container">
  <div class="page-inner">
    <!-- Page heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
      <div>
        <h3 class="fw-bold mb-1"><?= htmlspecialchars($pageTitle) ?></h3>
        <div class="text-muted">Generate and view your usage, sessions, and billing reports.</div>
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="<?= WEB_BASE ?>/php_views/user/user_payments.php">
          <i class="fa fa-credit-card me-1"></i>Payments
        </a>
        <a class="btn btn-outline-secondary" href="<?= WEB_BASE ?>/php_views/user/user_invoices.php">
          <i class="fa fa-file-invoice-dollar me-1"></i>Invoices
        </a>
      </div>
    </div>

    <!-- Summary cards -->
    <div class="row g-3">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body">
            <div class="text-muted small">Total reports</div>
            <div class="display-6 fw-bold"><?= (int)$totalReports ?></div>
            <div class="small text-muted">All time</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body">
            <div class="text-muted small">Shared with others</div>
            <div class="display-6 fw-bold"><?= (int)$sharedReports ?></div>
            <div class="small text-muted">Using share flag</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body">
            <div class="text-muted small">Last generated</div>
            <div class="h5 fw-bold mb-0"><?= htmlspecialchars($lastRunType) ?></div>
            <div class="small text-muted"><?= htmlspecialchars($lastRunAt) ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick usage snapshot (placeholders) -->
    <div class="row g-3 mt-1">
      <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-header bg-white">
            <div class="card-title mb-0">Last 30 days snapshot</div>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-sm-4">
                <div class="p-3 border rounded-4">
                  <div class="text-muted small">Total sessions</div>
                  <div class="h3 fw-bold mb-0" id="statSessions">14</div>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="p-3 border rounded-4">
                  <div class="text-muted small">Online time</div>
                  <div class="h3 fw-bold mb-0" id="statTime">18 h 22 m</div>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="p-3 border rounded-4">
                  <div class="text-muted small">Data used</div>
                  <div class="h3 fw-bold mb-0" id="statData">42.7 GB</div>
                </div>
              </div>
            </div>
            <div class="small text-muted mt-2">
              These values are examples; later compute from <code>internet_session_with_length</code> and <code>traffic_data</code>.
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Report generator (placeholders) -->
    <div class="card border-0 shadow-sm rounded-4 mt-3">
      <div class="card-header bg-white">
        <div class="card-title mb-0">Generate a report</div>
      </div>
      <div class="card-body">
        <form class="row g-3" onsubmit="return false;">
          <div class="col-sm-4">
            <label class="form-label">Report type</label>
            <select id="repType" class="form-select">
              <option value="USAGE_SUMMARY" selected>Usage Summary</option>
              <option value="SESSION_DETAIL">Session Detail</option>
              <option value="BILLING_SUMMARY">Billing Summary</option>
              <option value="OVERAGE_DETAIL">Overage Detail</option>
            </select>
          </div>
          <div class="col-sm-3">
            <label class="form-label">From</label>
            <input id="repFrom" type="date" class="form-control">
          </div>
          <div class="col-sm-3">
            <label class="form-label">To</label>
            <input id="repTo" type="date" class="form-control">
          </div>
          <div class="col-sm-2 d-flex align-items-end">
            <button class="btn btn-success w-100" disabled>
              <i class="fa fa-play me-1"></i>Generate
            </button>
          </div>
          <div class="col-12">
            <div class="form-check">
              <input id="shareFlag" class="form-check-input" type="checkbox" disabled>
              <label class="form-check-label" for="shareFlag">
                Shareable link (sets <code>share_flag=1</code>)
              </label>
            </div>
            <div class="small text-muted mt-1">
              Generation is disabled in this demo. Later, insert a row into <code>report_run</code>.
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Reports table -->
    <div class="card border-0 shadow-sm rounded-4 mt-3">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="card-title mb-0">Your reports</div>
        <div class="small text-muted">
          <span class="me-2"><i class="fa fa-filter"></i></span>
          <select id="typeFilter" class="form-select form-select-sm" style="width:auto; display:inline-block;">
            <option value="ALL">All types</option>
            <option value="USAGE_SUMMARY">Usage Summary</option>
            <option value="SESSION_DETAIL">Session Detail</option>
            <option value="BILLING_SUMMARY">Billing Summary</option>
            <option value="OVERAGE_DETAIL">Overage Detail</option>
          </select>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table align-middle" id="reportTable">
            <thead>
              <tr>
                <th>Run ID</th>
                <th>Type</th>
                <th>Parameters</th>
                <th>Generated</th>
                <th>Shared</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody id="reportBody"><!-- populated by JS --></tbody>
          </table>
        </div>
        <div id="emptyReports" class="text-center text-muted py-4 d-none">
          <i class="fa fa-file-alt fa-2x mb-2"></i>
          <div>No reports yet.</div>
        </div>
        <div class="small text-muted">Rows are examples. Later, fetch from <code>report_run</code> scoped to the current user.</div>
      </div>
    </div>

  </div>
</div>

<style>
  .rounded-4{ border-radius:1rem; }
  .badge-soft-success{ background:rgba(25,135,84,.12); color:#198754; }
  .badge-soft-secondary{ background:rgba(108,117,125,.12); color:#6c757d; }
</style>

<script>

const reports = [
  { run_id:'RR-0009Q', report_type:'USAGE_SUMMARY',  parameters:'{"from":"2025-10-01","to":"2025-10-31"}', generated_at:'2025-10-31 21:10', share_flag:1 },
  { run_id:'RR-0008P', report_type:'SESSION_DETAIL', parameters:'{"from":"2025-10-01","to":"2025-10-31"}', generated_at:'2025-10-30 10:05', share_flag:0 },
  { run_id:'RR-0007N', report_type:'BILLING_SUMMARY',parameters:'{"month":"2025-10"}',                generated_at:'2025-10-29 08:12', share_flag:0 },
  { run_id:'RR-0006M', report_type:'OVERAGE_DETAIL', parameters:'{"from":"2025-09-01","to":"2025-09-30"}', generated_at:'2025-10-01 12:02', share_flag:1 },
  { run_id:'RR-0005L', report_type:'USAGE_SUMMARY',  parameters:'{"from":"2025-09-01","to":"2025-09-30"}', generated_at:'2025-09-30 21:02', share_flag:0 },
  { run_id:'RR-0004K', report_type:'SESSION_DETAIL', parameters:'{"from":"2025-08-01","to":"2025-08-31"}', generated_at:'2025-08-31 18:41', share_flag:0 },
];

const badgeShared = (f)=> f ? '<span class="badge badge-soft-success">Yes</span>' : '<span class="badge badge-soft-secondary">No</span>';

function row(r){
  return `
    <tr data-type="${r.report_type}">
      <td class="fw-semibold">${r.run_id}</td>
      <td><span class="badge bg-light text-dark">${r.report_type}</span></td>
      <td><code class="small">${r.parameters.replaceAll('<','&lt;')}</code></td>
      <td>${r.generated_at}</td>
      <td>${badgeShared(!!r.share_flag)}</td>
      <td class="text-end">
        <div class="btn-group">
          <button class="btn btn-sm btn-outline-primary" disabled>View</button>
          <button class="btn btn-sm btn-outline-secondary" disabled>Download</button>
          <button class="btn btn-sm btn-outline-dark" disabled>Share link</button>
        </div>
      </td>
    </tr>
  `;
}

function renderReports(){
  const type = document.getElementById('typeFilter').value;
  const body = document.getElementById('reportBody');
  const filtered = (type==='ALL') ? reports : reports.filter(r=>r.report_type===type);
  body.innerHTML = filtered.map(row).join('');
  document.getElementById('emptyReports').classList.toggle('d-none', filtered.length !== 0);

  body.querySelectorAll('button').forEach(b=>{
    b.addEventListener('click', e=>{
      e.preventDefault();
      alert('Report actions are disabled in this demo.');
    });
  });
}

document.getElementById('typeFilter').addEventListener('change', renderReports);
renderReports();
</script>

<?php require_once VIEWS_ROOT . '/asset_for_pages/footer.php'; ?>

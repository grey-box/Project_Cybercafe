<?php
declare(strict_types=1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

$pageTitle = "Search Results";
require_once VIEWS_ROOT . '/asset_for_pages/user_header.php';

// Inputs
$q = trim((string)($_GET['q'] ?? ''));
$scope = (string)($_GET['scope'] ?? 'all');
$scopes = ['all','payments','invoices','sessions','reports','allowed'];

?>
<div class="page-header">
  <ul class="breadcrumbs mb-3">
    <li class="nav-home"><a href="<?= WEB_BASE ?>/php_views/user/user_profile.php"><i class="icon-home"></i></a></li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="<?= WEB_BASE ?>/php_views/user/search_home.php">Search</a></li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Results</a></li>
  </ul>
</div>

<div class="card">
  <div class="card-body">
    <form class="row gx-2 gy-2 align-items-center" action="" method="get">
      <div class="col-sm-6">
        <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search payments, invoices, sessions…" />
      </div>
      <div class="col-sm-4">
        <select class="form-select" name="scope">
          <?php foreach ($scopes as $s): ?>
            <option value="<?= $s ?>" <?= $s === $scope ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-2">
        <button class="btn btn-primary w-100" type="submit"><i class="fa fa-search me-1"></i>Search</button>
      </div>
    </form>
  </div>
</div>

<?php
// ---------- SAMPLE DATA (replace with PDO later) ----------
$mockPayments = [
  ['id'=>'pay_01','method'=>'card','amount'=>19.99,'invoice'=>'INV-1025','date'=>'2025-10-07 12:12'],
  ['id'=>'pay_02','method'=>'cash','amount'=>9.99,'invoice'=>'INV-1026','date'=>'2025-10-15 14:23'],
];
$mockInvoices = [
  ['invoice'=>'INV-1025','status'=>'PAID','total'=>19.99,'issued'=>'2025-10-07','due'=>'2025-10-07'],
  ['invoice'=>'INV-1026','status'=>'DUE','total'=>9.99,'issued'=>'2025-10-15','due'=>'2025-10-22'],
];
$mockSessions = [
  ['session'=>'S-1001','login'=>'2025-10-20 11:00','logout'=>'2025-10-20 12:10','ip'=>'10.0.0.12','queue'=>'basic'],
  ['session'=>'S-1002','login'=>'2025-10-21 09:05','logout'=>null,'ip'=>'10.0.0.14','queue'=>'basic'],
];
$mockReports = [
  ['run_id'=>'R-2001','type'=>'usage_summary','generated'=>'2025-10-19 18:05'],
  ['run_id'=>'R-2002','type'=>'monthly_statement','generated'=>'2025-10-31 09:00'],
];
$mockAllowed = [
  ['url'=>'https://example.com','blocked'=>0],
  ['url'=>'https://anotherurl.com','blocked'=>0],
];

// Simple filter function
function filterQ(array $rows, string $q): array {
  if ($q === '') return $rows;
  $q = mb_strtolower($q);
  return array_values(array_filter($rows, function($r) use ($q) {
    return mb_strpos(mb_strtolower(json_encode($r)), $q) !== false;
  }));
}

// Apply filters by q and scope
$payments = ($scope === 'all' || $scope === 'payments') ? filterQ($mockPayments, $q) : [];
$invoices = ($scope === 'all' || $scope === 'invoices') ? filterQ($mockInvoices, $q) : [];
$sessions = ($scope === 'all' || $scope === 'sessions') ? filterQ($mockSessions, $q) : [];
$reports  = ($scope === 'all' || $scope === 'reports')  ? filterQ($mockReports,  $q) : [];
$allowed  = ($scope === 'all' || $scope === 'allowed')  ? filterQ($mockAllowed,  $q) : [];
?>

<!-- Tabs -->
<ul class="nav nav-pills my-3" role="tablist">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-pay">Payments (<?= count($payments) ?>)</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-inv">Invoices (<?= count($invoices) ?>)</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-ses">Sessions (<?= count($sessions) ?>)</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-rep">Reports (<?= count($reports) ?>)</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-allow">Allowed Sites (<?= count($allowed) ?>)</button></li>
</ul>

<div class="tab-content">
  <!-- Payments -->
  <div class="tab-pane fade show active" id="tab-pay">
    <div class="card"><div class="card-body">
      <?php if (!$payments): ?>
        <div class="text-muted">No payment results.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead><tr><th>Payment ID</th><th>Invoice #</th><th>Method</th><th>Amount</th><th>Date</th></tr></thead>
            <tbody>
              <?php foreach ($payments as $p): ?>
              <tr>
                <td><?= htmlspecialchars($p['id']) ?></td>
                <td><?= htmlspecialchars($p['invoice'] ?? '') ?></td>
                <td><?= htmlspecialchars($p['method'] ?? '') ?></td>
                <td>$<?= number_format((float)($p['amount'] ?? 0), 2) ?></td>
                <td><?= htmlspecialchars($p['date'] ?? '') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div></div>
  </div>

  <!-- Invoices -->
  <div class="tab-pane fade" id="tab-inv">
    <div class="card"><div class="card-body">
      <?php if (!$invoices): ?>
        <div class="text-muted">No invoice results.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead><tr><th>Invoice</th><th>Status</th><th>Total</th><th>Issued</th><th>Due</th></tr></thead>
            <tbody>
              <?php foreach ($invoices as $inv): ?>
              <tr>
                <td><?= htmlspecialchars($inv['invoice'] ?? ('H-' . ($inv['history_id'] ?? ''))) ?></td>
                <td><span class="badge <?= ($inv['status'] ?? '') === 'PAID' ? 'bg-success' : 'bg-warning' ?>"><?= htmlspecialchars($inv['status'] ?? '') ?></span></td>
                <td>$<?= number_format((float)($inv['total'] ?? $inv['amount_due'] ?? 0), 2) ?></td>
                <td><?= htmlspecialchars($inv['issued'] ?? '') ?></td>
                <td><?= htmlspecialchars($inv['due'] ?? '') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div></div>
  </div>

  <!-- Sessions -->
  <div class="tab-pane fade" id="tab-ses">
    <div class="card"><div class="card-body">
      <?php if (!$sessions): ?>
        <div class="text-muted">No session results.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead><tr><th>Session</th><th>Login</th><th>Logout</th><th>IP</th><th>Queue</th></tr></thead>
            <tbody>
              <?php foreach ($sessions as $s): ?>
              <tr>
                <td><?= htmlspecialchars($s['session']) ?></td>
                <td><?= htmlspecialchars($s['login']) ?></td>
                <td><?= htmlspecialchars($s['logout'] ?? 'Active') ?></td>
                <td><?= htmlspecialchars($s['ip']) ?></td>
                <td><?= htmlspecialchars($s['queue']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div></div>
  </div>

  <!-- Reports -->
  <div class="tab-pane fade" id="tab-rep">
    <div class="card"><div class="card-body">
      <?php if (!$reports): ?>
        <div class="text-muted">No report results.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead><tr><th>Run ID</th><th>Type</th><th>Generated</th><th>Action</th></tr></thead>
            <tbody>
              <?php foreach ($reports as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['run_id']) ?></td>
                <td><?= htmlspecialchars($r['type']) ?></td>
                <td><?= htmlspecialchars($r['generated']) ?></td>
                <td><a class="btn btn-sm btn-outline-secondary" href="<?= WEB_BASE ?>/php_views/user/user_reports.php?run_id=<?= urlencode($r['run_id']) ?>">Open</a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div></div>
  </div>

  <!-- Allowed Sites -->
  <div class="tab-pane fade" id="tab-allow">
    <div class="card"><div class="card-body">
      <?php if (!$allowed): ?>
        <div class="text-muted">No allowed site results.</div>
      <?php else: ?>
        <ul class="list-group list-group-flush">
          <?php foreach ($allowed as $a): ?>
          <li class="list-group-item d-flex align-items-center justify-content-between">
            <span><i class="fa fa-globe me-2 text-muted"></i><?= htmlspecialchars($a['url']) ?></span>
            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($a['url']) ?>" target="_blank" rel="noopener">Visit</a>
          </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div></div>
  </div>
</div>

<script>
  // Save recent search locally (browser only)
  (function(){
    const q = <?= json_encode($q) ?>;
    if(!q) return;
    const key = 'cc_recent_searches';
    const list = JSON.parse(localStorage.getItem(key) || '[]');
    // de-dup, newest first
    const next = [q, ...list.filter(v => v !== q)].slice(0,15);
    localStorage.setItem(key, JSON.stringify(next));
  })();
</script>

<?php require_once VIEWS_ROOT . '/asset_for_pages/footer.php'; ?>
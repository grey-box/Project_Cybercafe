<?php
declare(strict_types=1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

$pageTitle = "Search Results";
require_once VIEWS_ROOT . '/asset_for_pages/user_header.php';

$pdo    = require $_SERVER['DOCUMENT_ROOT'] . '/Website/config/db.php';
$userId = current_user_id();

// Inputs
$q      = trim((string)($_GET['q'] ?? ''));
$scope  = (string)($_GET['scope'] ?? 'all');
$scopes = ['all','payments','invoices','sessions','reports','allowed'];
if (!in_array($scope, $scopes, true)) {
    $scope = 'all';
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Build LIKE clause helper
function addSearchFilter(string $baseSql, string $q, array $cols): array {
    if ($q === '') {
        return [$baseSql, []];
    }
    $pattern = '%' . $q . '%';
    $conditions = [];
    foreach ($cols as $idx => $col) {
        $conditions[] = "$col LIKE :q";
    }
    $baseSql .= ' AND (' . implode(' OR ', $conditions) . ')';
    return [$baseSql, [':q' => $pattern]];
}

// ---------------- PAYMENTS ----------------
$payments = [];
if ($scope === 'all' || $scope === 'payments') {
    $sql = "
      SELECT payment_id, payment_method, amount_charged, invoice_number, payment_datetime
      FROM payment
      WHERE user_id = :u
    ";
    [$sql, $params] = addSearchFilter($sql, $q, [
        'payment_id',
        'invoice_number',
        'transaction_ref_number',
        'payment_method'
    ]);
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge([':u' => $userId], $params));
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $payments[] = [
            'id'      => $row['payment_id'],
            'invoice' => $row['invoice_number'] ?? '',
            'method'  => $row['payment_method'] ?? '',
            'amount'  => (float)$row['amount_charged'],
            'date'    => $row['payment_datetime'],
        ];
    }
}

// ---------------- INVOICES (from payment_history) ----------------
$invoices = [];
if ($scope === 'all' || $scope === 'invoices') {
    $sql = "
      SELECT history_id, amount_due, amount_paid, payment_status, timestamp
      FROM payment_history
      WHERE user_id = :u
    ";
    [$sql, $params] = addSearchFilter($sql, $q, [
        'payment_status'
    ]);
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge([':u' => $userId], $params));
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $total = $row['amount_due'] ?? $row['amount_paid'] ?? 0;
        $invoices[] = [
            'invoice' => 'H-' . $row['history_id'],  // pseudo-invoice number
            'status'  => $row['payment_status'],
            'total'   => (float)$total,
            'issued'  => $row['timestamp'],
            'due'     => '', // no due date field in history; can extend later
        ];
    }
}

// ---------------- SESSIONS ----------------
$sessions = [];
if ($scope === 'all' || $scope === 'sessions') {
    $sql = "
      SELECT session_id, login_timestamp, logout_timestamp, ip_address, speed_queue_id
      FROM internet_session
      WHERE user_id = :u
    ";
    [$sql, $params] = addSearchFilter($sql, $q, [
        'session_id',
        'ip_address',
        'speed_queue_id'
    ]);
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge([':u' => $userId], $params));
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $sessions[] = [
            'session' => $row['session_id'],
            'login'   => $row['login_timestamp'],
            'logout'  => $row['logout_timestamp'],
            'ip'      => $row['ip_address'],
            'queue'   => $row['speed_queue_id'],
        ];
    }
}

// ---------------- REPORTS ----------------
$reports = [];
if ($scope === 'all' || $scope === 'reports') {
    $sql = "
      SELECT run_id, report_type, generated_at
      FROM report_run
      WHERE user_id = :u
    ";
    [$sql, $params] = addSearchFilter($sql, $q, [
        'run_id',
        'report_type'
    ]);
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge([':u' => $userId], $params));
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $reports[] = [
            'run_id'   => $row['run_id'],
            'type'     => $row['report_type'],
            'generated'=> $row['generated_at'],
        ];
    }
}

// ---------------- ALLOWED SITES ----------------
$allowed = [];
if ($scope === 'all' || $scope === 'allowed') {
    $sql = "
      SELECT url, is_blocked
      FROM url_restriction
      WHERE is_blocked = 0
    ";
    [$sql, $params] = addSearchFilter($sql, $q, [
        'url'
    ]);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $allowed[] = [
            'url' => $row['url'],
        ];
    }
}

?>
<div class="page-header">
  <ul class="breadcrumbs mb-3">
    <li class="nav-home">
      <a href="<?= WEB_BASE ?>/php_views/user/user_profile.php"><i class="icon-home"></i></a>
    </li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item">
      <a href="<?= WEB_BASE ?>/php_views/user/search_home.php">Search</a>
    </li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Results</a></li>
  </ul>
</div>

<div class="card">
  <div class="card-body">
    <form class="row gx-2 gy-2 align-items-center" action="" method="get">
      <div class="col-sm-6">
        <input type="text" class="form-control" name="q"
               value="<?= h($q) ?>"
               placeholder="Search payments, invoices, sessions…" />
      </div>
      <div class="col-sm-4">
        <select class="form-select" name="scope">
          <?php foreach ($scopes as $s): ?>
            <option value="<?= h($s) ?>" <?= $s === $scope ? 'selected' : '' ?>>
              <?= ucfirst($s) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-2">
        <button class="btn btn-primary w-100" type="submit">
          <i class="fa fa-search me-1"></i>Search
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Tabs -->
<ul class="nav nav-pills my-3" role="tablist">
  <li class="nav-item">
    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-pay">
      Payments (<?= count($payments) ?>)
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-inv">
      Invoices (<?= count($invoices) ?>)
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-ses">
      Sessions (<?= count($sessions) ?>)
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-rep">
      Reports (<?= count($reports) ?>)
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-allow">
      Allowed Sites (<?= count($allowed) ?>)
    </button>
  </li>
</ul>

<div class="tab-content">
  <!-- Payments -->
  <div class="tab-pane fade show active" id="tab-pay">
    <div class="card">
      <div class="card-body">
        <?php if (!$payments): ?>
          <div class="text-muted">No payment results.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>Payment ID</th>
                  <th>Invoice #</th>
                  <th>Method</th>
                  <th>Amount</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($payments as $p): ?>
                <tr>
                  <td><?= h($p['id']) ?></td>
                  <td><?= h($p['invoice']) ?></td>
                  <td><?= h($p['method']) ?></td>
                  <td>$<?= number_format((float)$p['amount'], 2) ?></td>
                  <td><?= h($p['date']) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Invoices -->
  <div class="tab-pane fade" id="tab-inv">
    <div class="card">
      <div class="card-body">
        <?php if (!$invoices): ?>
          <div class="text-muted">No invoice results.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>Invoice</th>
                  <th>Status</th>
                  <th>Total</th>
                  <th>Issued</th>
                  <th>Due</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($invoices as $inv): ?>
                <tr>
                  <td><?= h($inv['invoice']) ?></td>
                  <td>
                    <span class="badge <?= ($inv['status'] === 'PAID') ? 'bg-success' : 'bg-warning' ?>">
                      <?= h($inv['status']) ?>
                    </span>
                  </td>
                  <td>$<?= number_format((float)$inv['total'], 2) ?></td>
                  <td><?= h($inv['issued']) ?></td>
                  <td><?= h($inv['due']) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Sessions -->
  <div class="tab-pane fade" id="tab-ses">
    <div class="card">
      <div class="card-body">
        <?php if (!$sessions): ?>
          <div class="text-muted">No session results.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>Session</th>
                  <th>Login</th>
                  <th>Logout</th>
                  <th>IP</th>
                  <th>Queue</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($sessions as $s): ?>
                <tr>
                  <td><?= h($s['session']) ?></td>
                  <td><?= h($s['login']) ?></td>
                  <td><?= h($s['logout'] ?? 'Active') ?></td>
                  <td><?= h($s['ip']) ?></td>
                  <td><?= h($s['queue']) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Reports -->
  <div class="tab-pane fade" id="tab-rep">
    <div class="card">
      <div class="card-body">
        <?php if (!$reports): ?>
          <div class="text-muted">No report results.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>Run ID</th>
                  <th>Type</th>
                  <th>Generated</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($reports as $r): ?>
                <tr>
                  <td><?= h($r['run_id']) ?></td>
                  <td><?= h($r['type']) ?></td>
                  <td><?= h($r['generated']) ?></td>
                  <td>
                    <a class="btn btn-sm btn-outline-secondary"
                       href="<?= WEB_BASE ?>/php_views/user/user_reports.php?run_id=<?= urlencode($r['run_id']) ?>">
                      Open
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Allowed Sites -->
  <div class="tab-pane fade" id="tab-allow">
    <div class="card">
      <div class="card-body">
        <?php if (!$allowed): ?>
          <div class="text-muted">No allowed site results.</div>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($allowed as $a): ?>
              <li class="list-group-item d-flex align-items-center justify-content-between">
                <span>
                  <i class="fa fa-globe me-2 text-muted"></i><?= h($a['url']) ?>
                </span>
                <a class="btn btn-sm btn-outline-primary"
                   href="<?= h($a['url']) ?>"
                   target="_blank" rel="noopener">
                  Visit
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
  // Save recent search locally (browser only)
  (function(){
    const q = <?= json_encode($q) ?>;
    if (!q) return;
    const key = 'cc_recent_searches';
    const list = JSON.parse(localStorage.getItem(key) || '[]');
    const next = [q, ...list.filter(v => v !== q)].slice(0, 15);
    localStorage.setItem(key, JSON.stringify(next));
  })();
</script>

<?php require_once VIEWS_ROOT . '/asset_for_pages/footer.php'; ?>
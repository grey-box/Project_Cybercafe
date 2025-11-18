<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

$pdo = require $_SERVER['DOCUMENT_ROOT'] . '/Website/config/db.php';

$userId = current_user_id();

$pageTitle = "Reports";

function guid_like(): string { return 'rr-' . bin2hex(random_bytes(6)); }
function clean_date(?string $s): ?string {
  if (!$s) return null;
  $t = strtotime($s);
  return $t ? date('Y-m-d', $t) : null;
}
function jenc($v): string { return json_encode($v, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); }

$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  if ($action === 'generate') {
    $type = $_POST['type'] ?? 'USAGE_SUMMARY';
    $from = clean_date($_POST['from'] ?? null);
    $to   = clean_date($_POST['to'] ?? null);
    $shareFlag = isset($_POST['share']) ? 1 : 0;

    $params = ['from'=>$from, 'to'=>$to];
    if ($type === 'BILLING_SUMMARY' && !$from && !$to) {
      // convenience: if not provided, use current month
      $params = ['month'=>date('Y-m')];
    }

    try {
      $pdo->beginTransaction();
      $runId = guid_like();

      $stmt = $pdo->prepare("
        INSERT INTO report_run(run_id, user_id, report_type, parameters, share_flag)
        VALUES(:rid, :uid, :rtype, :params, :share)
      ");
      $stmt->execute([
        ':rid'    => $runId,
        ':uid'    => $userId,
        ':rtype'  => $type,
        ':params' => json_encode($params),
        ':share'  => $shareFlag,
      ]);

      $pdo->commit();
      $flash = ['ok'=>true, 'msg'=>"Report generated: $type (Run ID: $runId)"];
    } catch (Throwable $e) {
      $pdo->rollBack();
      $flash = ['ok'=>false, 'msg'=>"Could not generate: ".$e->getMessage()];
    }
  }

  if ($action === 'toggle_share') {
    $rid = $_POST['run_id'] ?? '';
    try {
      $stmt = $pdo->prepare("
        UPDATE report_run
           SET share_flag = CASE WHEN share_flag=1 THEN 0 ELSE 1 END
         WHERE run_id = :rid AND user_id = :uid
      ");
      $stmt->execute([':rid'=>$rid, ':uid'=>$userId]);
      $flash = ['ok'=>true, 'msg'=>"Share flag toggled for $rid."];
    } catch (Throwable $e) {
      $flash = ['ok'=>false, 'msg'=>"Toggle failed: ".$e->getMessage()];
    }
  }
}

if (isset($_GET['download'])) {
  $rid = (string)$_GET['download'];
  $stmt = $pdo->prepare("
    SELECT run_id, report_type, parameters, generated_at, share_flag
      FROM report_run
     WHERE run_id = :rid AND user_id = :uid
     LIMIT 1
  ");
  $stmt->execute([':rid'=>$rid, ':uid'=>$userId]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($row) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="'.basename($rid).'.json"');
    echo json_encode($row, JSON_PRETTY_PRINT);
    exit;
  }

  $flash = ['ok'=>false, 'msg'=>"Report $rid not found or not yours."];
}


$sum = $pdo->prepare("
  SELECT COUNT(*) AS total,
         SUM(CASE WHEN share_flag=1 THEN 1 ELSE 0 END) AS shared
    FROM report_run
   WHERE user_id = :uid
");
$sum->execute([':uid'=>$userId]);
$sumRow = $sum->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'shared'=>0];
$totalReports   = (int)$sumRow['total'];
$sharedReports  = (int)$sumRow['shared'];

$last = $pdo->prepare("
  SELECT report_type, generated_at
    FROM report_run
   WHERE user_id = :uid
ORDER BY generated_at DESC
   LIMIT 1
");
$last->execute([':uid'=>$userId]);
$lastRow = $last->fetch(PDO::FETCH_ASSOC);
$lastRunType = $lastRow['report_type'] ?? '—';
$lastRunAt   = $lastRow['generated_at'] ?? '—';

$snap = $pdo->prepare("
WITH win AS (
  SELECT DATETIME('now','-30 days') AS start_cut
),
base AS (
  SELECT s.session_id,
         s.user_id,
         s.login_timestamp,
         s.logout_timestamp,
         COALESCE(
           (SELECT CAST((JULIANDAY(COALESCE(s.logout_timestamp, DATETIME('now')))
                     - JULIANDAY(s.login_timestamp)) * 86400 AS INTEGER)), 0
         ) AS sec,
         t.received_bytes, t.transmitted_bytes
    FROM internet_session s
    LEFT JOIN traffic_data t ON t.session_id = s.session_id
    JOIN win
   WHERE s.user_id = :uid
     AND s.login_timestamp >= win.start_cut
)
SELECT COUNT(*)                                               AS sessions,
       COALESCE(SUM(sec),0)                                   AS total_seconds,
       COALESCE(SUM(received_bytes + transmitted_bytes),0)    AS total_bytes
  FROM base
");
$snap->execute([':uid'=>$userId]);
$snapRow = $snap->fetch(PDO::FETCH_ASSOC) ?: ['sessions'=>0,'total_seconds'=>0,'total_bytes'=>0];

$statSessions = (int)$snapRow['sessions'];
$statSeconds  = (int)$snapRow['total_seconds'];
$hours = intdiv($statSeconds, 3600);
$mins  = intdiv($statSeconds % 3600, 60);
$statTime = sprintf('%d h %d m', $hours, $mins);
$bytes = (float)$snapRow['total_bytes'];
$statData = $bytes >= (1<<30)
  ? number_format($bytes/(1<<30), 1) . ' GB'
  : ($bytes >= (1<<20) ? number_format($bytes/(1<<20), 1).' MB' : $bytes.' B');

$rep = $pdo->prepare("
  SELECT run_id, report_type, parameters, generated_at, share_flag
    FROM report_run
   WHERE user_id = :uid
ORDER BY generated_at DESC, run_id DESC
");
$rep->execute([':uid'=>$userId]);
$reportRows = $rep->fetchAll(PDO::FETCH_ASSOC);
$reportsJson = jenc($reportRows);

?>
<?php require_once VIEWS_ROOT . '/asset_for_pages/user_header.php'; ?>

<div class="container">
  <div class="page-inner">

    <?php if ($flash): ?>
      <div class="alert <?= $flash['ok'] ? 'alert-success' : 'alert-danger' ?> rounded-4">
        <?= htmlspecialchars($flash['msg']) ?>
      </div>
    <?php endif; ?>

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

    <!-- Quick usage snapshot -->
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
                  <div class="h3 fw-bold mb-0" id="statSessions"><?= $statSessions ?></div>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="p-3 border rounded-4">
                  <div class="text-muted small">Online time</div>
                  <div class="h3 fw-bold mb-0" id="statTime"><?= htmlspecialchars($statTime) ?></div>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="p-3 border rounded-4">
                  <div class="text-muted small">Data used</div>
                  <div class="h3 fw-bold mb-0" id="statData"><?= htmlspecialchars($statData) ?></div>
                </div>
              </div>
            </div>
            <div class="small text-muted mt-2">
              Computed from <code>internet_session_with_length</code> and <code>traffic_data</code> for the past 30 days.
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Report generator -->
    <div class="card border-0 shadow-sm rounded-4 mt-3">
      <div class="card-header bg-white">
        <div class="card-title mb-0">Generate a report</div>
      </div>
      <div class="card-body">
        <form class="row g-3" method="post">
          <input type="hidden" name="action" value="generate">
          <div class="col-sm-4">
            <label class="form-label">Report type</label>
            <select id="repType" class="form-select" name="type" required>
              <option value="USAGE_SUMMARY" selected>Usage Summary</option>
              <option value="SESSION_DETAIL">Session Detail</option>
              <option value="BILLING_SUMMARY">Billing Summary</option>
              <option value="OVERAGE_DETAIL">Overage Detail</option>
            </select>
          </div>
          <div class="col-sm-3">
            <label class="form-label">From</label>
            <input id="repFrom" type="date" class="form-control" name="from">
          </div>
          <div class="col-sm-3">
            <label class="form-label">To</label>
            <input id="repTo" type="date" class="form-control" name="to">
          </div>
          <div class="col-sm-2 d-flex align-items-end">
            <button class="btn btn-success w-100">
              <i class="fa fa-play me-1"></i>Generate
            </button>
          </div>
          <div class="col-12">
            <div class="form-check">
              <input id="shareFlag" class="form-check-input" type="checkbox" name="share" value="1">
              <label class="form-check-label" for="shareFlag">
                Shareable link (sets <code>share_flag=1</code>)
              </label>
            </div>
            <div class="small text-muted mt-1">
              This inserts a row into <code>report_run</code> immediately.
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
const reports = <?= $reportsJson ?: '[]' ?>;

const badgeShared = (f)=> f ? '<span class="badge badge-soft-success">Yes</span>' : '<span class="badge badge-soft-secondary">No</span>';

function esc(s){ return String(s ?? '').replaceAll('<','&lt;'); }

function row(r){
  const paramsPretty = (()=>{ try { return JSON.stringify(JSON.parse(r.parameters||'{}'), null, 0); } catch { return r.parameters || '{}'; } })();
  return `
    <tr data-type="${esc(r.report_type)}">
      <td class="fw-semibold">${esc(r.run_id)}</td>
      <td><span class="badge bg-light text-dark">${esc(r.report_type)}</span></td>
      <td><code class="small">${esc(paramsPretty)}</code></td>
      <td>${esc(r.generated_at)}</td>
      <td>${badgeShared(!!Number(r.share_flag))}</td>
      <td class="text-end">
        <div class="btn-group">
          <a class="btn btn-sm btn-outline-primary" href="?download=${encodeURIComponent(r.run_id)}">Download</a>
          <form method="post" class="d-inline">
            <input type="hidden" name="action" value="toggle_share">
            <input type="hidden" name="run_id" value="${esc(r.run_id)}">
            <button class="btn btn-sm btn-outline-dark">Toggle share</button>
          </form>
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
}
document.getElementById('typeFilter').addEventListener('change', renderReports);
renderReports();
</script>

<?php require_once VIEWS_ROOT . '/asset_for_pages/footer.php'; ?>

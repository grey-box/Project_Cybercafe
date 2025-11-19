<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

$pdo = require $_SERVER['DOCUMENT_ROOT'] . '/Website/config/db.php';
$userId = current_user_id();

$pageTitle = "Calendar";

function jenc($v): string {
  return json_encode($v, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT);
}

$events = [];

/* ========== Billing events from payment_history ========== */
try {
  $stmt = $pdo->prepare("
    SELECT DATE(timestamp) AS d,
           amount_due,
           payment_status
      FROM payment_history
     WHERE user_id = :uid
     ORDER BY timestamp DESC
     LIMIT 200
  ");
  $stmt->execute([':uid' => $userId]);
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $date = $row['d'];
    if (!$date) continue;
    $amt = $row['amount_due'] !== null ? (float)$row['amount_due'] : 0.0;
    $status = $row['payment_status'] ?? '';
    $title = sprintf(
      'Bill %.2f (%s)',
      $amt,
      $status !== '' ? $status : 'status unknown'
    );
    $events[] = [
      'date'  => $date,
      'title' => $title,
      'type'  => 'billing',
    ];
  }
} catch (Throwable $e) {
  // you might log this
}

/* ========== Payment events from payment table ========== */
try {
  $stmt = $pdo->prepare("
    SELECT DATE(payment_datetime) AS d,
           amount_charged,
           invoice_number,
           payment_method
      FROM payment
     WHERE user_id = :uid
     ORDER BY payment_datetime DESC
     LIMIT 200
  ");
  $stmt->execute([':uid' => $userId]);
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $date = $row['d'];
    if (!$date) continue;
    $amt = (float)$row['amount_charged'];
    $invoice = $row['invoice_number'] ?: 'No invoice';
    $method  = $row['payment_method'] ?: 'Method unknown';
    $title = sprintf(
      'Payment %.2f • %s • %s',
      $amt,
      $invoice,
      $method
    );
    $events[] = [
      'date'  => $date,
      'title' => $title,
      'type'  => 'payment',
    ];
  }
} catch (Throwable $e) {
  // log if needed
}

/* ========== Session summary events from internet_session ========== */
try {
  $stmt = $pdo->prepare("
    WITH base AS (
      SELECT DATE(login_timestamp) AS d,
             CASE
               WHEN logout_timestamp IS NOT NULL
                 THEN CAST((JULIANDAY(logout_timestamp) - JULIANDAY(login_timestamp)) * 86400 AS INTEGER)
               ELSE CAST((JULIANDAY(DATETIME('now')) - JULIANDAY(login_timestamp)) * 86400 AS INTEGER)
             END AS sec
        FROM internet_session
       WHERE user_id = :uid
    )
    SELECT d,
           COUNT(*) AS session_count,
           COALESCE(SUM(sec),0) AS total_sec
      FROM base
     WHERE d IS NOT NULL
     GROUP BY d
     ORDER BY d DESC
     LIMIT 180
  ");
  $stmt->execute([':uid' => $userId]);
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $date = $row['d'];
    if (!$date) continue;
    $count = (int)$row['session_count'];
    $sec   = (int)$row['total_sec'];
    $h = intdiv($sec, 3600);
    $m = intdiv($sec % 3600, 60);
    $dur = sprintf('%dh %02dm', $h, $m);
    $title = sprintf('%d session%s total (%s)', $count, $count === 1 ? '' : 's', $dur);

    $events[] = [
      'date'  => $date,
      'title' => $title,
      'type'  => 'session',
    ];
  }
} catch (Throwable $e) {
  // log
}

/* ========== Maintenance or system events from system_event ========== */
try {
  $stmt = $pdo->query("
    SELECT DATE(occurred_at) AS d,
           event_type,
           description
      FROM system_event
     ORDER BY occurred_at DESC
     LIMIT 120
  ");
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $date = $row['d'];
    if (!$date) continue;
    $type = $row['event_type'] ?? 'SYSTEM';
    $desc = $row['description'] ?? '';
    $title = $desc !== '' ? $desc : ('System event: '.$type);

    $events[] = [
      'date'  => $date,
      'title' => $title,
      'type'  => 'maintenance', // treat all as maintenance style for coloring
    ];
  }
} catch (Throwable $e) {
  // log
}

/* ========== Report events from report_run ========== */
try {
  $stmt = $pdo->prepare("
    SELECT DATE(generated_at) AS d,
           report_type,
           COUNT(*) AS cnt
      FROM report_run
     WHERE user_id = :uid
     GROUP BY d, report_type
     ORDER BY d DESC
     LIMIT 120
  ");
  $stmt->execute([':uid' => $userId]);
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $date = $row['d'];
    if (!$date) continue;
    $rtype = $row['report_type'] ?? 'REPORT';
    $cnt   = (int)$row['cnt'];
    $title = sprintf(
      '%d %s report%s generated',
      $cnt,
      $rtype,
      $cnt === 1 ? '' : 's'
    );
    $events[] = [
      'date'  => $date,
      'title' => $title,
      'type'  => 'report',
    ];
  }
} catch (Throwable $e) {
  // log
}

$eventsJson = jenc($events);
?>
<?php require_once VIEWS_ROOT . '/asset_for_pages/user_header.php'; ?>

<div class="container">
  <div class="page-inner">

    <div class="d-sm-flex align-items-center justify-content-between mb-3">
      <div>
        <h3 class="fw-bold mb-1"><?= htmlspecialchars($pageTitle) ?></h3>
        <div class="text-muted">Your billing dates, payments, usage sessions, maintenance, and reports in one place.</div>
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="<?= WEB_BASE ?>/php_views/user/user_payments.php">
          <i class="fa fa-credit-card me-1"></i>Payments
        </a>
        <a class="btn btn-outline-secondary" href="<?= WEB_BASE ?>/php_views/user/user_invoices.php">
          <i class="fa fa-file-invoice-dollar me-1"></i>Invoices
        </a>
        <a class="btn btn-outline-secondary" href="<?= WEB_BASE ?>/php_views/user/user_reports.php">
          <i class="fa fa-file-alt me-1"></i>Reports
        </a>
      </div>
    </div>

    <!-- Controls -->
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body d-flex flex-wrap align-items-end gap-2">
        <div>
          <label class="form-label small mb-1">Month</label>
          <div class="input-group">
            <button id="prevBtn" class="btn btn-outline-secondary"><i class="fa fa-chevron-left"></i></button>
            <input id="monthLabel" class="form-control text-center" value="" readonly style="max-width:220px;">
            <button id="nextBtn" class="btn btn-outline-secondary"><i class="fa fa-chevron-right"></i></button>
          </div>
        </div>

        <div class="ms-auto"></div>

        <div>
          <label class="form-label small mb-1">Filters</label>
          <div class="d-flex flex-wrap gap-2">
            <div class="form-check form-check-inline">
              <input class="form-check-input evt-filter" type="checkbox" id="f-billing" value="billing" checked>
              <label class="form-check-label" for="f-billing"><span class="legend legend-billing"></span> Billing</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input evt-filter" type="checkbox" id="f-payment" value="payment" checked>
              <label class="form-check-label" for="f-payment"><span class="legend legend-payment"></span> Payments</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input evt-filter" type="checkbox" id="f-session" value="session" checked>
              <label class="form-check-label" for="f-session"><span class="legend legend-session"></span> Sessions</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input evt-filter" type="checkbox" id="f-maint" value="maintenance" checked>
              <label class="form-check-label" for="f-maint"><span class="legend legend-maint"></span> Maintenance</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input evt-filter" type="checkbox" id="f-report" value="report" checked>
              <label class="form-check-label" for="f-report"><span class="legend legend-report"></span> Reports</label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Calendar -->
    <div class="card border-0 shadow-sm rounded-4 mt-3">
      <div class="card-body">
        <div id="calendarGrid" class="calendar-grid"></div>
        <div class="small text-muted mt-2">
          Events are populated from your account data: billing history, payments, sessions, system events, and reports.
        </div>
      </div>
    </div>

    <!-- Event Drawer -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="eventDrawer">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title">Day details</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body">
        <div id="drawerDate" class="fw-bold mb-2"></div>
        <div id="drawerList" class="list-group list-group-flush"></div>
        <div class="small text-muted mt-3">Actions are read only for now.</div>
      </div>
    </div>

  </div>
</div>

<style>
  .rounded-4{ border-radius:1rem; }
  .calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(40px, 1fr)); /* min width for mobile */
    gap: 0.25rem;
  }
  @media (max-width: 768px) {
    .calendar-grid {
      grid-template-columns: repeat(7, minmax(30px, 1fr));
      gap: 0.15rem;
    }
  }
  .cal-cell{
    border:1px solid #e9ecef;
    border-radius:.75rem;
    min-height: 80px;
    padding:1.6rem .5rem .5rem .5rem;
    background:#fff;
    position:relative;
    overflow: hidden;
  }
  @media (max-width: 768px) {
    .cal-cell {
      min-height: 70px;
      padding: 0.8rem 0.2rem 0.2rem 0.2rem;
    }
  }
  .cal-cell .day{
    position:absolute;
    top:.35rem;
    right:.6rem;
    font-size:.9rem;
    color:#6c757d;
    z-index:3;
    background:#fff;
    padding:0 .25rem;
    border-radius:.25rem;
    line-height:1.1;
  }
  .evt-pill{
    display:block;
    font-size:.78rem;
    padding:.2rem .5rem;
    border-radius:999px;
    margin-bottom:.25rem;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    cursor:default;
  }
  @media (max-width: 768px) {
    .evt-pill {
      font-size: 0.55rem;
      padding: 0.1rem 0.25rem;
      max-width: 100%;
    }
  }
  @media (max-width: 768px) {
    .card-body.d-flex.flex-wrap.align-items-end.gap-2 {
      flex-direction: column;
      gap: 0.5rem;
    }
    .input-group { flex: 1 1 100%; }
    .evt-filter { font-size: 0.7rem; }
  }

  .legend{ display:inline-block; width:.75rem; height:.75rem; border-radius:999px; margin-right:.25rem; vertical-align:middle; }
  .legend-billing{ background:#ffb547; }
  .legend-payment{ background:#198754; }
  .legend-session{ background:#0d6efd; }
  .legend-maint{ background:#dc3545; }
  .legend-report{ background:#6f42c1; }
  .pill-billing{ background:rgba(255,181,71,.18); color:#a06400;}
  .pill-payment{ background:rgba(25,135,84,.18); color:#0f5132;}
  .pill-session{ background:rgba(13,110,253,.18); color:#0b3c9c;}
  .pill-maint{ background:rgba(220,53,69,.18); color:#842029;}
  .pill-report{ background:rgba(111,66,193,.18); color:#3d256e;}
  .is-today{ box-shadow:0 0 0 2px #13a463 inset; }
</style>

<script>
const allEvents = <?= $eventsJson ?: '[]' ?>;

const monthLabel = document.getElementById('monthLabel');
const grid = document.getElementById('calendarGrid');
const filters = Array.from(document.querySelectorAll('.evt-filter'));

let current = new Date();

function ymd(d){
  return d.toISOString().slice(0,10);
}
function startOfMonth(date){
  return new Date(date.getFullYear(), date.getMonth(), 1);
}
function endOfMonth(date){
  return new Date(date.getFullYear(), date.getMonth()+1, 0);
}
function monthName(year, monthIndex){
  return new Date(year, monthIndex, 1).toLocaleString(undefined, { month:'long', year:'numeric' });
}

function render(){
  monthLabel.value = monthName(current.getFullYear(), current.getMonth());

  const first = startOfMonth(current);
  const last = endOfMonth(current);
  const firstDay = new Date(first);
  firstDay.setDate(first.getDate() - first.getDay());

  const activeTypes = new Set(filters.filter(f => f.checked).map(f=>f.value));
  const cells = [];

  for (let i = 0; i < 42; i++){
    const day = new Date(firstDay);
    day.setDate(firstDay.getDate() + i);
    const iso = ymd(day);
    const inMonth = day.getMonth() === current.getMonth();
    const isToday = iso === ymd(new Date());

    const evts = allEvents.filter(e => e.date === iso && activeTypes.has(e.type));

    const pills = evts.map(e => {
      const pillClass = {
        billing:'pill-billing',
        payment:'pill-payment',
        session:'pill-session',
        maintenance:'pill-maint',
        report:'pill-report'
      }[e.type] || 'pill-report';

      // truncate display text to 25 chars, show full on hover
      const maxLen = 25;
      const displayTitle = e.title.length > maxLen ? e.title.slice(0, maxLen-1) + '…' : e.title;
      const safeTitle = displayTitle.replace(/"/g,'&quot;');
      return `<span class="evt-pill ${pillClass}" title="${e.title.replace(/"/g,'&quot;')}">${safeTitle}</span>`;
    }).join('');


    cells.push(`
      <div class="cal-cell ${isToday?'is-today':''}" data-date="${iso}" data-inmonth="${inMonth?'1':'0'}">
        <div class="day">${day.getDate()}</div>
        <div class="evts">${pills}</div>
      </div>
    `);
  }

  grid.innerHTML = `
    <div class="cal-head small text-muted">Sun</div>
    <div class="cal-head small text-muted">Mon</div>
    <div class="cal-head small text-muted">Tue</div>
    <div class="cal-head small text-muted">Wed</div>
    <div class="cal-head small text-muted">Thu</div>
    <div class="cal-head small text-muted">Fri</div>
    <div class="cal-head small text-muted">Sat</div>
    ${cells.join('')}
  `;

  grid.querySelectorAll('.cal-cell').forEach(cell => {
    cell.addEventListener('click', () => openDrawer(cell.dataset.date));
  });
}

function openDrawer(dateStr){
  const list = document.getElementById('drawerList');
  const label = document.getElementById('drawerDate');
  const activeTypes = new Set(filters.filter(f => f.checked).map(f=>f.value));
  const evts = allEvents.filter(e => e.date === dateStr && activeTypes.has(e.type));

  label.textContent = new Date(dateStr + 'T00:00:00').toLocaleDateString(undefined, {
    weekday:'long', year:'numeric', month:'long', day:'numeric'
  });

  if (evts.length === 0){
    list.innerHTML = `<div class="list-group-item">No events.</div>`;
  } else {
    list.innerHTML = evts.map(e => {
      const icon = {
        billing:'fa-file-invoice-dollar text-warning',
        payment:'fa-credit-card text-success',
        session:'fa-wifi text-primary',
        maintenance:'fa-tools text-danger',
        report:'fa-file-alt text-purple'
      }[e.type] || 'fa-circle';

      return `
        <div class="list-group-item d-flex align-items-start">
          <i class="fa ${icon} me-2 mt-1"></i>
          <div>
            <div class="fw-semibold">${e.title}</div>
            <div class="small text-muted">${e.type}</div>
          </div>
        </div>
      `;
    }).join('');
  }

  // reuse existing Offcanvas instance instead of creating new each time
  const drawerEl = document.getElementById('eventDrawer');
  const drawer = bootstrap.Offcanvas.getInstance(drawerEl) || new bootstrap.Offcanvas(drawerEl);
  drawer.show();
}

document.getElementById('prevBtn').addEventListener('click', () => {
  current.setMonth(current.getMonth()-1);
  render();
});
document.getElementById('nextBtn').addEventListener('click', () => {
  current.setMonth(current.getMonth()+1);
  render();
});
filters.forEach(f => f.addEventListener('change', render));

render();
</script>

<?php require_once VIEWS_ROOT . '/asset_for_pages/footer.php'; ?>

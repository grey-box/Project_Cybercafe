<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

$pageTitle = "Calendar";

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
          Demo data only. Later, populate events from your DB and render here.
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
        <div class="small text-muted mt-3">Actions are disabled in this demo.</div>
      </div>
    </div>

  </div>
</div>

<style>
  .rounded-4{ border-radius:1rem; }
  .calendar-grid{
    display:grid;
    grid-template-columns: repeat(7, 1fr);
    gap:.5rem;
  }
  .cal-cell{
    border:1px solid #e9ecef;
    border-radius:.75rem;
    min-height:110px;
    padding:1.6rem .5rem .5rem .5rem;
    background:#fff;
    position:relative;
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

const demoEvents = [
  // Billing / invoices (e.g., next bill date monthly on 25th)
  { date:'2025-11-25', title:'Monthly bill due ($19.99)', type:'billing' },

  // Payments (from `payment`)
  { date:'2025-10-25', title:'Payment $19.99  •  INV-1025', type:'payment' },
  { date:'2025-09-25', title:'Payment $19.99  •  INV-0925', type:'payment' },

  // Sessions (from `internet_session` – summarized)
  { date:'2025-11-01', title:'3 sessions total (4h 12m)', type:'session' },
  { date:'2025-11-03', title:'1 session (1h 08m)', type:'session' },

  // Maintenance / outages (from `system_event`)
  { date:'2025-11-15', title:'Planned maintenance 11:00–11:30 PM', type:'maintenance' },

  // Reports (from `report_run`)
  { date:'2025-10-31', title:'Report generated: Usage Summary', type:'report' },
];

/* ========= Calendar rendering ========= */
const monthLabel = document.getElementById('monthLabel');
const grid = document.getElementById('calendarGrid');
const filters = Array.from(document.querySelectorAll('.evt-filter'));

let current = new Date(); // today

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
  // header label
  monthLabel.value = monthName(current.getFullYear(), current.getMonth());

  // compute visible range (start on Sunday)
  const first = startOfMonth(current);
  const last = endOfMonth(current);
  const firstDay = new Date(first);
  firstDay.setDate(first.getDate() - first.getDay()); // back to Sunday
  const cells = [];

  const activeTypes = new Set(filters.filter(f => f.checked).map(f=>f.value));

  for (let i = 0; i < 42; i++){ // 6 weeks grid
    const day = new Date(firstDay);
    day.setDate(firstDay.getDate() + i);
    const iso = ymd(day);
    const inMonth = day.getMonth() === current.getMonth();
    const isToday = iso === ymd(new Date());

    // events for this day
    const evts = demoEvents.filter(e => e.date === iso && activeTypes.has(e.type));

    const pills = evts.map(e => {
      const pillClass = {
        billing:'pill-billing',
        payment:'pill-payment',
        session:'pill-session',
        maintenance:'pill-maint',
        report:'pill-report'
      }[e.type] || 'pill-report';
      return `<span class="evt-pill ${pillClass}" title="${e.title.replace(/"/g,'&quot;')}">${e.title}</span>`;
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

  // open day drawer on cell click
  grid.querySelectorAll('.cal-cell').forEach(cell => {
    cell.addEventListener('click', () => openDrawer(cell.dataset.date));
  });
}

function openDrawer(dateStr){
  const list = document.getElementById('drawerList');
  const label = document.getElementById('drawerDate');
  const activeTypes = new Set(filters.filter(f => f.checked).map(f=>f.value));
  const evts = demoEvents.filter(e => e.date === dateStr && activeTypes.has(e.type));

  label.textContent = new Date(dateStr + 'T00:00:00').toLocaleDateString(undefined, { weekday:'long', year:'numeric', month:'long', day:'numeric' });

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
          <div class="ms-auto d-none">
            <button class="btn btn-sm btn-outline-secondary">Details</button>
          </div>
        </div>
      `;
    }).join('');
  }

  // show offcanvas
  const drawer = new bootstrap.Offcanvas('#eventDrawer');
  drawer.show();
}

document.getElementById('prevBtn').addEventListener('click', () => { current.setMonth(current.getMonth()-1); render(); });
document.getElementById('nextBtn').addEventListener('click', () => { current.setMonth(current.getMonth()+1); render(); });
filters.forEach(f => f.addEventListener('change', render));

// initial
render();
</script>

<?php require_once VIEWS_ROOT . '/asset_for_pages/footer.php'; ?>

<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

$pageTitle = "Invoices";

$outstandingTotal = 19.99;     // sum of all open invoices
$openCount        = 1;         // count of pending/unpaid invoices
$lastInvoiceId    = "INV-1025";
$lastInvoiceDate  = "2025-10-25";
$nextBillDate     = "2025-11-30";
?>

<?php require_once VIEWS_ROOT . '/asset_for_pages/user_header.php'; ?>

<div class="container">
  <div class="page-inner">

    <div class="d-sm-flex align-items-center justify-content-between mb-3">
      <div>
        <h3 class="fw-bold mb-1"><?= htmlspecialchars($pageTitle) ?></h3>
        <div class="text-muted">View, search, and download your invoices.</div>
      </div>
      <div class="search-sm mt-3 mt-sm-0" style="min-width:260px;">
        <div class="input-group">
          <span class="input-group-text"><i class="fa fa-search"></i></span>
          <input id="invoiceSearch" type="text" class="form-control" placeholder="Search invoice or note" />
        </div>
      </div>
    </div>

    <!-- Summary -->
    <div class="row g-3">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body">
            <div class="text-muted small">Outstanding</div>
            <div class="display-6 fw-bold <?= $outstandingTotal > 0 ? 'text-danger' : 'text-success' ?>">
              $<?= number_format($outstandingTotal, 2) ?>
              <?= $outstandingTotal > 0 ? '<span class="badge bg-warning text-dark ms-1">Due</span>' : '' ?>
            </div>
            <div class="small text-muted mt-1">Open invoices total</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body">
            <div class="text-muted small">Open invoices</div>
            <div class="h3 fw-bold mb-0"><?= (int)$openCount ?></div>
            <div class="small text-muted">Awaiting payment</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body">
            <div class="text-muted small">Last invoice</div>
            <div class="h5 fw-bold mb-0"><?= htmlspecialchars($lastInvoiceId) ?></div>
            <div class="small text-muted"><?= htmlspecialchars($lastInvoiceDate) ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body">
            <div class="text-muted small">Next bill date</div>
            <div class="h3 fw-bold mb-0"><?= htmlspecialchars($nextBillDate) ?></div>
            <div class="small text-muted">Auto reminders enabled</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mt-3">
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <div class="col-sm-3">
            <label class="form-label">Status</label>
            <select id="statusFilter" class="form-select">
              <option value="all" selected>All</option>
              <option value="pending">Pending</option>
              <option value="overdue">Overdue</option>
              <option value="paid">Paid</option>
              <option value="void">Void</option>
            </select>
          </div>
          <div class="col-sm-3">
            <label class="form-label">From</label>
            <input id="fromDate" type="date" class="form-control" />
          </div>
          <div class="col-sm-3">
            <label class="form-label">To</label>
            <input id="toDate" type="date" class="form-control" />
          </div>
          <div class="col-sm-3">
            <button id="resetBtn" class="btn btn-outline-secondary w-100">
              <i class="fa fa-undo me-1"></i>Reset
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Invoices table -->
    <div class="card border-0 shadow-sm rounded-4 mt-3">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="card-title mb-0">Invoices</div>
        <div class="small text-muted">Sample data for layout</div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table align-middle" id="invoiceTable">
            <thead>
              <tr>
                <th>Invoice</th>
                <th>Date</th>
                <th>Due</th>
                <th>Status</th>
                <th>Amount</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody id="invoiceBody"><!-- rendered by JS --></tbody>
          </table>
        </div>
        <div id="emptyState" class="text-center text-muted py-4 d-none">
          <i class="fa fa-file-invoice-dollar fa-2x mb-2"></i>
          <div>No invoices match the current filters.</div>
        </div>
      </div>
    </div>

    <!-- Mini legend -->
    <div class="small text-muted mt-2">
      Status legend:
      <span class="badge bg-warning text-dark">Pending</span>
      <span class="badge bg-danger">Overdue</span>
      <span class="badge bg-success">Paid</span>
      <span class="badge bg-secondary">Void</span>
    </div>

  </div>
</div>

<style>
  .rounded-4{ border-radius:1rem; }
  .amount-pos{ color:#198754; font-weight:600; }
  .amount-neg{ color:#dc3545; font-weight:600; }
</style>

<script>

const invoices = [
  { id:"INV-1031", date:"2025-10-31", due:"2025-11-05", status:"pending", amount:19.99, note:"Monthly plan" },
  { id:"INV-1025", date:"2025-10-25", due:"2025-10-25", status:"paid",    amount:19.99, note:"Monthly plan" },
  { id:"INV-0925", date:"2025-09-25", due:"2025-09-25", status:"paid",    amount:19.99, note:"Monthly plan" },
  { id:"INV-0820", date:"2025-08-20", due:"2025-08-25", status:"void",    amount:19.99, note:"Reissued" },
  { id:"INV-0725", date:"2025-07-25", due:"2025-07-25", status:"paid",    amount:19.99, note:"Monthly plan" },
  { id:"INV-0610", date:"2025-06-10", due:"2025-06-15", status:"overdue", amount:5.51,  note:"Overage charge" }
];

const badgeFor = (s) => ({
  pending:'<span class="badge bg-warning text-dark">Pending</span>',
  overdue:'<span class="badge bg-danger">Overdue</span>',
  paid   :'<span class="badge bg-success">Paid</span>',
  void   :'<span class="badge bg-secondary">Void</span>',
}[s] || s);

function rowTemplate(inv){
  const amt = `$${inv.amount.toFixed(2)}`;
  const payBtnDisabled = !(inv.status === 'pending' || inv.status === 'overdue');
  return `
    <tr data-id="${inv.id}" data-status="${inv.status}" data-date="${inv.date}">
      <td class="fw-semibold">${inv.id}<div class="small text-muted">${inv.note ?? ''}</div></td>
      <td>${inv.date}</td>
      <td>${inv.due}</td>
      <td>${badgeFor(inv.status)}</td>
      <td class="${inv.status==='paid' ? 'amount-pos' : 'amount-neg'}">${amt}</td>
      <td class="text-end">
        <div class="btn-group">
          <button class="btn btn-sm btn-success" ${payBtnDisabled ? 'disabled' : ''}>Pay</button>
          <button class="btn btn-sm btn-outline-primary" disabled>PDF</button>
          <button class="btn btn-sm btn-outline-secondary" disabled>View</button>
        </div>
      </td>
    </tr>`;
}

function render(){
  const q = document.getElementById('invoiceSearch').value.trim().toLowerCase();
  const status = document.getElementById('statusFilter').value;
  const from = document.getElementById('fromDate').value;
  const to   = document.getElementById('toDate').value;

  let filtered = invoices.filter(v => {
    const matchesQ = !q || v.id.toLowerCase().includes(q) || (v.note||'').toLowerCase().includes(q);
    const matchesStatus = (status === 'all') || v.status === status;
    const afterFrom = !from || v.date >= from;
    const beforeTo  = !to   || v.date <= to;
    return matchesQ && matchesStatus && afterFrom && beforeTo;
  });

  const body = document.getElementById('invoiceBody');
  body.innerHTML = filtered.map(rowTemplate).join('');

  document.getElementById('emptyState').classList.toggle('d-none', filtered.length !== 0);

  // Placeholder clicks
  body.querySelectorAll('button').forEach(btn=>{
    btn.addEventListener('click', e=>{
      e.preventDefault();
      alert('Invoice actions are disabled in this demo.');
    });
  });
}

document.getElementById('invoiceSearch').addEventListener('input', render);
document.getElementById('statusFilter').addEventListener('change', render);
document.getElementById('fromDate').addEventListener('change', render);
document.getElementById('toDate').addEventListener('change', render);
document.getElementById('resetBtn').addEventListener('click', ()=>{
  document.getElementById('invoiceSearch').value = '';
  document.getElementById('statusFilter').value = 'all';
  document.getElementById('fromDate').value = '';
  document.getElementById('toDate').value = '';
  render();
});

render();
</script>

<?php require_once VIEWS_ROOT . '/asset_for_pages/footer.php'; ?>

<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

$pageTitle = "Payments";

$currentBalance = -12.50;  // negative means amount due
$lastPaymentAmt = 25.00;
$lastPaymentTime = "2025-10-27 14:22";
$nextDue = "2025-11-30";
$planName = "Starter Plan";
$planPrice = 19.99;
?>

<?php require_once VIEWS_ROOT . '/asset_for_pages/user_header.php'; ?>

<div class="container">
  <div class="page-inner">
    <!-- Page heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
      <div>
        <h3 class="fw-bold mb-1"><?= htmlspecialchars($pageTitle) ?></h3>
        <div class="text-muted">Review balance, payments, and invoices.</div>
      </div>
      <a class="btn btn-success" href="#" role="button" aria-disabled="true" onclick="return false;">
        <i class="fa fa-credit-card me-2"></i>Make a payment
      </a>
    </div>

    <!-- Summary cards -->
    <div class="row g-3">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body">
            <div class="text-muted small">Current balance</div>
            <div class="display-6 fw-bold <?= $currentBalance < 0 ? 'text-danger' : 'text-success' ?>">
              $<?= number_format(abs($currentBalance), 2) ?>
              <?= $currentBalance < 0 ? '<span class="badge bg-danger-subtle text-danger ms-1">due</span>' : '' ?>
            </div>
            <div class="small text-muted mt-1">Includes credits and charges</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body">
            <div class="text-muted small">Last payment</div>
            <div class="h3 fw-bold mb-0">$<?= number_format($lastPaymentAmt, 2) ?></div>
            <div class="small text-muted"><?= htmlspecialchars($lastPaymentTime) ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body">
            <div class="text-muted small">Next due date</div>
            <div class="h3 fw-bold mb-0"><?= htmlspecialchars($nextDue) ?></div>
            <div class="small text-muted">Auto reminders enabled</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-body">
            <div class="text-muted small">Plan</div>
            <div class="h3 fw-bold mb-0"><?= htmlspecialchars($planName) ?></div>
            <div class="small text-muted">$<?= number_format($planPrice, 2) ?> per month</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment action + saved methods -->
    <div class="row g-3 mt-1">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-header bg-white">
            <div class="card-title mb-0">Make a payment</div>
          </div>
          <div class="card-body">
            <form onsubmit="return false;">
              <div class="mb-3">
                <label for="payAmount" class="form-label">Amount</label>
                <div class="input-group">
                  <span class="input-group-text">$</span>
                  <input id="payAmount" type="number" step="0.01" class="form-control" placeholder="0.00" value="<?= number_format(abs(min(0, $currentBalance)), 2) ?>" disabled>
                </div>
              </div>
              <div class="mb-3">
                <label for="payMethod" class="form-label">Payment method</label>
                <select id="payMethod" class="form-select" disabled>
                  <option selected>Visa ending 4242</option>
                  <option>Mastercard ending 1881</option>
                  <option>New card</option>
                </select>
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-success" disabled>
                  <i class="fa fa-lock me-1"></i> Pay now
                </button>
                <button class="btn btn-outline-secondary" disabled>Schedule</button>
              </div>
              <div class="small text-muted mt-2">
                Payments are processed securely. This is a placeholder only.
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-header bg-white">
            <div class="card-title mb-0">Saved methods</div>
          </div>
          <div class="card-body">
            <div class="list-group list-group-flush">
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <div class="fw-semibold">Visa •••• 4242</div>
                  <div class="small text-muted">Expires 06/28 • Billing: AZ</div>
                </div>
                <div class="d-flex gap-2">
                  <button class="btn btn-sm btn-outline-secondary" disabled>Set default</button>
                  <button class="btn btn-sm btn-outline-danger" disabled>Remove</button>
                </div>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <div class="fw-semibold">Mastercard •••• 1881</div>
                  <div class="small text-muted">Expires 11/27 • Billing: AZ</div>
                </div>
                <div class="d-flex gap-2">
                  <button class="btn btn-sm btn-outline-secondary" disabled>Set default</button>
                  <button class="btn btn-sm btn-outline-danger" disabled>Remove</button>
                </div>
              </div>
            </div>
            <div class="mt-3">
              <button class="btn btn-outline-success" disabled>
                <i class="fa fa-plus me-1"></i> Add new method
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent payments and ledger -->
    <div class="row g-3 mt-1">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div class="card-title mb-0">Recent payments</div>
            <div class="small text-muted">Last 6</div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Receipt</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>2025-10-27 14:22</td>
                    <td>Visa 4242</td>
                    <td class="text-success fw-semibold">$25.00</td>
                    <td><button class="btn btn-sm btn-outline-primary" disabled>View</button></td>
                  </tr>
                  <tr>
                    <td>2025-09-28 09:10</td>
                    <td>Mastercard 1881</td>
                    <td class="text-success fw-semibold">$25.00</td>
                    <td><button class="btn btn-sm btn-outline-primary" disabled>View</button></td>
                  </tr>
                  <tr>
                    <td>2025-08-29 11:05</td>
                    <td>Visa 4242</td>
                    <td class="text-success fw-semibold">$19.99</td>
                    <td><button class="btn btn-sm btn-outline-primary" disabled>View</button></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="small text-muted">These are sample rows for layout only.</div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div class="card-title mb-0">Account ledger</div>
            <div class="small text-muted">Credits and charges</div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th>Time</th>
                    <th>Reason</th>
                    <th>Delta</th>
                    <th>Ref</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>2025-10-27 14:22</td>
                    <td>Card payment</td>
                    <td class="text-success fw-semibold">+25.00</td>
                    <td><span class="badge bg-light text-dark">PAY-9Q2W</span></td>
                  </tr>
                  <tr>
                    <td>2025-10-25 00:01</td>
                    <td>Monthly fee</td>
                    <td class="text-danger fw-semibold">-19.99</td>
                    <td><span class="badge bg-light text-dark">INV-1025</span></td>
                  </tr>
                  <tr>
                    <td>2025-10-20 18:44</td>
                    <td>Overage charge</td>
                    <td class="text-danger fw-semibold">-5.51</td>
                    <td><span class="badge bg-light text-dark">OVG-545</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="small text-muted">Values shown are examples. Hook to monetary_ledger later.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Invoices -->
    <div class="row g-3 mt-1">
      <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div class="card-title mb-0">Invoices</div>
            <div class="small text-muted">Pending and paid</div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>INV-1031</td>
                    <td>2025-10-31</td>
                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                    <td>$19.99</td>
                    <td class="text-end">
                      <button class="btn btn-sm btn-success" disabled>Pay</button>
                      <button class="btn btn-sm btn-outline-primary" disabled>PDF</button>
                    </td>
                  </tr>
                  <tr>
                    <td>INV-1025</td>
                    <td>2025-10-25</td>
                    <td><span class="badge bg-success">Paid</span></td>
                    <td>$19.99</td>
                    <td class="text-end">
                      <button class="btn btn-sm btn-outline-primary" disabled>PDF</button>
                    </td>
                  </tr>
                  <tr>
                    <td>INV-0925</td>
                    <td>2025-09-25</td>
                    <td><span class="badge bg-success">Paid</span></td>
                    <td>$19.99</td>
                    <td class="text-end">
                      <button class="btn btn-sm btn-outline-primary" disabled>PDF</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="small text-muted">Invoice links are placeholders for now.</div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<style>
  .badge.bg-danger-subtle{ background: rgba(220,53,69,.12); }
  .rounded-4{ border-radius:1rem; }
</style>

<script>

document.querySelectorAll('button[disabled], a[aria-disabled="true"]').forEach(el=>{
  el.addEventListener('click', (e)=> {
    e.preventDefault();
    alert('Payments are disabled in this demo.');
  });
});
</script>

<?php require_once VIEWS_ROOT . '/asset_for_pages/footer.php'; ?>

<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

$pdo = require $_SERVER['DOCUMENT_ROOT'] . '/Website/config/db.php';
$userId = current_user_id();

$pageTitle = "Payments";

function uuid_like(): string { return uniqid('id', true); }
function safe_amount(string $s): float {
  $v = (float)preg_replace('/[^\d.\-]/', '', $s);
  return round($v, 2);
}

$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'pay_now') {
    $amount = safe_amount($_POST['amount'] ?? '0');
    $method = trim($_POST['method'] ?? 'CARD');
    $invoiceId = $_POST['invoice_id'] ?? null;

    if ($amount > 0.00) {
        $pdo->beginTransaction();
        try {
            $pid = 'pay-' . uuid_like();
            $invoiceNo = 'INV-' . substr(hash('sha1', $pid), 0, 8);

            // Insert into payment
            $stmt = $pdo->prepare("
                INSERT INTO payment(payment_id, user_id, payment_datetime, payment_method, amount_charged, transaction_ref_number, invoice_number)
                VALUES(:pid, :uid, DATETIME('now'), :method, :amt, :tx, :inv)
            ");
            $stmt->execute([
                ':pid' => $pid,
                ':uid' => $userId,
                ':method' => $method,
                ':amt' => $amount,
                ':tx' => 'TX-' . substr($pid, 4, 12),
                ':inv' => $invoiceNo,
            ]);

            // Update ledger
            $stmt = $pdo->prepare("
                INSERT INTO monetary_ledger(user_id, speed_queue_id, amount_delta, reason, ref_payment_id)
                VALUES(:uid,
                    (SELECT speed_queue_id FROM user_balance WHERE user_id=:uid LIMIT 1),
                    :delta, 'Payment applied', :pid)
            ");
            $stmt->execute([
                ':uid' => $userId,
                ':delta' => $amount,
                ':pid' => $pid,
            ]);

            // Update user_balance
            $updateBal = $pdo->prepare("
                UPDATE user_balance
                SET monetary_balance = monetary_balance + :amt,
                    last_update_timestamp = DATETIME('now')
                WHERE user_id = :uid
            ");
            $updateBal->execute([
                ':amt' => $amount,
                ':uid' => $userId,
            ]);

            // Record in payment_history
            if ($invoiceId) {
                // If paying a pending invoice
                $stmt = $pdo->prepare("
                    UPDATE payment_history
                    SET amount_paid = amount_paid + :amt,
                        payment_status = 'PAID'
                    WHERE history_id = :hid
                ");
                $stmt->execute([
                    ':amt' => $amount,
                    ':hid' => $invoiceId,
                ]);
            } else {
                // General payment (not tied to invoice)
                $stmt = $pdo->prepare("
                    INSERT INTO payment_history(user_id, timestamp, amount_due, amount_paid, payment_status)
                    VALUES(:uid, DATETIME('now'), :due, :paid, 'PAID')
                ");
                $stmt->execute([
                    ':uid' => $userId,
                    ':due' => $amount,
                    ':paid' => $amount,
                ]);
            }

            $pdo->commit();
            $flash = ['ok' => true, 'msg' => "Payment of $" . number_format($amount, 2) . " recorded."];
        } catch (Throwable $e) {
            $pdo->rollBack();
            $flash = ['ok' => false, 'msg' => "Payment failed: " . $e->getMessage()];
        }
    } else {
        $flash = ['ok' => false, 'msg' => "Enter a valid amount."];
    }
}

// Fetch current balance
$balStmt = $pdo->prepare("
    SELECT COALESCE(SUM(monetary_balance), 0.0) AS bal
    FROM user_balance WHERE user_id = :uid
");
$balStmt->execute([':uid' => $userId]);
$currentBalance = (float)$balStmt->fetchColumn();

// Last payment
$lp = $pdo->prepare("
    SELECT amount_charged, payment_datetime, payment_method
    FROM payment WHERE user_id = :uid
    ORDER BY payment_datetime DESC LIMIT 1
");
$lp->execute([':uid' => $userId]);
$last = $lp->fetch(PDO::FETCH_ASSOC);
$lastPaymentAmt  = $last ? (float)$last['amount_charged'] : 0.00;
$lastPaymentTime = $last ? (string)$last['payment_datetime'] : '—';

// Next due date (30 days after last payment)
$nextDue = '—';
if ($last && !empty($last['payment_datetime'])) {
    try {
        $d = new DateTime($last['payment_datetime']);
        $d->modify('+30 days');
        $nextDue = $d->format('Y-m-d');
    } catch (Throwable $e) {}
}

// Fetch plan info
$planRow = $pdo->prepare("
    WITH ub AS (
        SELECT speed_queue_id FROM user_balance WHERE user_id = :uid LIMIT 1
    ), sq AS (
        SELECT s.* FROM speed_queue s
        JOIN ub ON ub.speed_queue_id = s.queue_id
    )
    SELECT sp.plan_name, sp.monthly_price
    FROM service_plan sp, sq
    WHERE sp.upload_speed_limit = sq.upload_speed_limit
       OR sp.bandwidth_quota = sq.bandwidth_quota
    ORDER BY (sp.upload_speed_limit = sq.upload_speed_limit) DESC
    LIMIT 1
");
$planRow->execute([':uid' => $userId]);
$plan = $planRow->fetch(PDO::FETCH_ASSOC);
$planName  = $plan['plan_name'] ?? '—';
$planPrice = isset($plan['monthly_price']) ? (float)$plan['monthly_price'] : 0.00;

// Recent payments
$payStmt = $pdo->prepare("
    SELECT payment_datetime, payment_method, amount_charged, transaction_ref_number
    FROM payment WHERE user_id = :uid
    ORDER BY payment_datetime DESC LIMIT 6
");
$payStmt->execute([':uid' => $userId]);
$recentPayments = $payStmt->fetchAll(PDO::FETCH_ASSOC);

// Ledger (last 12)
$ledStmt = $pdo->prepare("
    SELECT created_at, reason, amount_delta, ref_payment_id
    FROM monetary_ledger WHERE user_id = :uid
    ORDER BY created_at DESC LIMIT 12
");
$ledStmt->execute([':uid' => $userId]);
$ledger = $ledStmt->fetchAll(PDO::FETCH_ASSOC);

// Invoices (paid from payment, open from payment_history)
$invStmt = $pdo->prepare(<<<SQL
WITH payments AS (
  SELECT
    p.invoice_number            AS id,
    DATE(p.payment_datetime)    AS date,
    DATE(p.payment_datetime)    AS due,
    'paid'                      AS status,
    CAST(p.amount_charged AS REAL) AS amount,
    COALESCE('Payment via ' || p.payment_method || ' (TX ' || p.transaction_ref_number || ')','Payment') AS note
  FROM payment p
  WHERE p.user_id = :uid AND p.invoice_number IS NOT NULL
),
hist_unpaid AS (
  SELECT
    'INV-PH-' || h.history_id                       AS id,
    DATE(h.timestamp)                                AS date,
    DATE(h.timestamp, '+5 days')                     AS due,
    CASE WHEN DATE('now') > DATE(h.timestamp, '+5 days') THEN 'overdue' ELSE 'pending' END AS status,
    CAST(ROUND(COALESCE(h.amount_due,0) - COALESCE(h.amount_paid,0), 2) AS REAL) AS amount,
    'Balance due'                                    AS note
  FROM payment_history h
  WHERE h.user_id = :uid
    AND (COALESCE(h.amount_due,0) - COALESCE(h.amount_paid,0)) > 0.009
)
SELECT * FROM payments
UNION ALL
SELECT * FROM hist_unpaid
ORDER BY date DESC, id DESC
SQL);
$invStmt->execute([':uid' => $userId]);
$invoices = $invStmt->fetchAll(PDO::FETCH_ASSOC);
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
        <div class="text-muted">Review balance, payments, and invoices.</div>
      </div>
      <a class="btn btn-success" href="#pay-now">
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
            <div class="small text-muted">
              <?= $planPrice > 0 ? '$' . number_format($planPrice, 2) . ' per month' : '—' ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment action -->
    <div class="row g-3 mt-1" id="pay-now">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-header bg-white">
            <div class="card-title mb-0">Make a payment</div>
          </div>
          <div class="card-body">
            <form method="post" class="d-grid gap-3">
              <input type="hidden" name="action" value="pay_now">
              <div>
                <label for="payAmount" class="form-label">Amount</label>
                <div class="input-group">
                  <span class="input-group-text">$</span>
                  <input id="payAmount" name="amount" type="number" step="0.01" min="0.01" class="form-control"
                    value="<?= htmlspecialchars(number_format(max(0.01, abs(min(0, $currentBalance))), 2, '.', '')) ?>">
                </div>
                <div class="form-text">Positive numbers only. This records a payment immediately.</div>
              </div>
              <div>
                <label for="payMethod" class="form-label">Payment method</label>
                <select id="payMethod" name="method" class="form-select">
                  <option value="CARD">Card (simulated)</option>
                  <option value="CASH">Cash (simulated)</option>
                </select>
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-success">
                  <i class="fa fa-lock me-1"></i> Pay now
                </button>
                <button class="btn btn-outline-secondary" type="reset">Clear</button>
              </div>
              <div class="small text-muted">
                This saves to <code>payment</code> and <code>monetary_ledger</code> instantly (demo, no external gateway).
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Saved methods (none in schema → informative empty state) -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-header bg-white">
            <div class="card-title mb-0">Saved methods</div>
          </div>
          <div class="card-body">
            <div class="text-muted">Your database has no card-on-file table. Add one later (e.g., <code>payment_method</code>) to show real cards.</div>
            <div class="mt-3">
              <button class="btn btn-outline-success" disabled>
                <i class="fa fa-plus me-1"></i> Add new method
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent payments -->
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
                <?php if (!$recentPayments): ?>
                  <tr><td colspan="4" class="text-muted">No payments yet.</td></tr>
                <?php else: foreach ($recentPayments as $p): ?>
                  <tr>
                    <td><?= htmlspecialchars($p['payment_datetime']) ?></td>
                    <td><?= htmlspecialchars($p['payment_method'] ?? '—') ?></td>
                    <td class="text-success fw-semibold">$<?= number_format((float)$p['amount_charged'], 2) ?></td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary" onclick="alert('TX: <?= htmlspecialchars($p['transaction_ref_number']) ?>'); return false;">
                        View
                      </button>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Account ledger -->
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
                <?php if (!$ledger): ?>
                  <tr><td colspan="4" class="text-muted">No ledger entries yet.</td></tr>
                <?php else: foreach ($ledger as $l): ?>
                  <tr>
                    <td><?= htmlspecialchars($l['created_at']) ?></td>
                    <td><?= htmlspecialchars($l['reason'] ?? '—') ?></td>
                    <?php $pos = ((float)$l['amount_delta']) >= 0; ?>
                    <td class="<?= $pos ? 'text-success' : 'text-danger' ?> fw-semibold">
                      <?= $pos ? '+' : '' ?><?= number_format((float)$l['amount_delta'], 2) ?>
                    </td>
                    <td>
                      <?php if (!empty($l['ref_payment_id'])): ?>
                        <span class="badge bg-light text-dark"><?= htmlspecialchars($l['ref_payment_id']) ?></span>
                      <?php else: ?>—<?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
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
                  <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$invoices): ?>
                  <tr><td colspan="5" class="text-muted">No invoices yet.</td></tr>
                <?php else: foreach ($invoices as $inv): ?>
                  <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($inv['id']) ?><div class="small text-muted"><?= htmlspecialchars($inv['note'] ?? '') ?></div></td>
                    <td><?= htmlspecialchars($inv['date'] ?? '—') ?></td>
                    <td>
                      <?php
                        $badge = [
                          'pending' => 'bg-warning text-dark',
                          'overdue' => 'bg-danger',
                          'paid'    => 'bg-success',
                          'void'    => 'bg-secondary',
                        ][$inv['status']] ?? 'bg-light text-dark';
                      ?>
                      <span class="badge <?= $badge ?>"><?= htmlspecialchars($inv['status']) ?></span>
                    </td>
                    <td>$<?= number_format((float)$inv['amount'], 2) ?></td>
                    <td class="text-end">
                      <?php $canPay = in_array($inv['status'], ['pending','overdue'], true); ?>
                      <?php if ($canPay): ?>
                        <form method="post" class="d-inline">
                          <input type="hidden" name="action" value="pay_now">
                          <input type="hidden" name="amount" value="<?= htmlspecialchars((string)$inv['amount']) ?>">
                          <input type="hidden" name="method" value="CARD">
                          <button class="btn btn-sm btn-success">Pay</button>
                        </form>
                      <?php endif; ?>
                      <button class="btn btn-sm btn-outline-primary" onclick="window.print()">PDF</button>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
            <div class="small text-muted">Pay buttons here post to this page and record into <code>payment</code>/<code>monetary_ledger</code>.</div>
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

<?php require_once VIEWS_ROOT . '/asset_for_pages/footer.php'; ?>

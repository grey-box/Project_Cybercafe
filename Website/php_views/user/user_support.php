<?php
declare(strict_types=1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';
// Set the page title dynamically
$pageTitle = "Support Page";

$pdo = require $_SERVER['DOCUMENT_ROOT'] . '/Website/config/db.php';

$userId = current_user_id();

function guid_like_ticket(): string {
    return 'st-' . bin2hex(random_bytes(6));
}

$flash = null;

// Handle new ticket submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'new_ticket') {
    $title   = trim($_POST['queryTitle'] ?? '');
    $details = trim($_POST['queryDetails'] ?? '');

    if ($title === '' || $details === '') {
        $flash = ['ok' => false, 'msg' => 'Please fill in both the title and details.'];
    } else {
        try {
            $pdo->beginTransaction();
            $ticketId = guid_like_ticket();

            // Insert ticket
            $stmt = $pdo->prepare("
                INSERT INTO support_ticket(ticket_id, user_id, title, description, status)
                VALUES(:tid, :uid, :title, :desc, 'OPEN')
            ");
            $stmt->execute([
                ':tid'   => $ticketId,
                ':uid'   => $userId,
                ':title' => $title,
                ':desc'  => $details,
            ]);

            // Insert first message as user
            $msg = $pdo->prepare("
                INSERT INTO support_message(ticket_id, sender_role, sender_user_id, body)
                VALUES(:tid, 'user', :uid, :body)
            ");
            $msg->execute([
                ':tid'  => $ticketId,
                ':uid'  => $userId,
                ':body' => $details,
            ]);

            $pdo->commit();
            $flash = ['ok' => true, 'msg' => 'Your query has been submitted.'];
        } catch (Throwable $e) {
            $pdo->rollBack();
            $flash = ['ok' => false, 'msg' => 'Could not submit query: '.$e->getMessage()];
        }
    }
}

// Load tickets for this user (dynamic)
$stmt = $pdo->prepare("
    SELECT ticket_id, title, status, created_at
      FROM support_ticket
     WHERE user_id = :uid
  ORDER BY created_at DESC
");
$stmt->execute([':uid' => $userId]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include the header
require_once VIEWS_ROOT . '/asset_for_pages/user_header.php';
?>

<div class="page-inner mt-4">
    <div class="container">
        <h2 class="mb-4">My CyberCafe Queries</h2>

        <?php if ($flash): ?>
            <div class="alert <?= $flash['ok'] ? 'alert-success' : 'alert-danger' ?> rounded-4">
                <?= htmlspecialchars($flash['msg']) ?>
            </div>
        <?php endif; ?>

        <!-- Post New Query -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Ask a Question</h5>
                <form method="post">
                    <input type="hidden" name="action" value="new_ticket">
                    <div class="mb-3">
                        <label for="queryTitle" class="form-label">Query Title</label>
                        <input type="text" class="form-control" id="queryTitle" name="queryTitle"
                               placeholder="Enter your query title">
                    </div>
                    <div class="mb-3">
                        <label for="queryDetails" class="form-label">Details</label>
                        <textarea class="form-control" id="queryDetails" name="queryDetails" rows="3"
                                  placeholder="Describe your issue..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Post Query</button>
                </form>
            </div>
        </div>

        <!-- Previous Queries -->
        <h4 class="mb-3">Previous Conversations</h4>

        <div class="list-group">
            <?php if (!$tickets): ?>
                <div class="text-muted">You have not opened any support tickets yet.</div>
            <?php else: ?>
                <?php foreach ($tickets as $t): ?>
                    <?php
                        $status = $t['status'] ?? 'OPEN';
                        $badgeClass = $status === 'RESOLVED' ? 'bg-success' :
                                      ($status === 'OPEN' ? 'bg-warning' : 'bg-secondary');
                        $dateString = $t['created_at'] ? date('M d, Y', strtotime($t['created_at'])) : '—';
                    ?>
                    <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start support-item"
                         data-id="<?= htmlspecialchars($t['ticket_id']) ?>"
                         onclick="window.location.href='chat.php?ticket=<?= urlencode($t['ticket_id']) ?>'">

                        <div class="flex-grow-1">
                            <h5 class="mb-1"><?= htmlspecialchars($t['title']) ?></h5>
                            <small class="text-muted">Posted on <?= htmlspecialchars($dateString) ?></small>
                        </div>
                        <div class="d-flex flex-column flex-md-row align-items-end align-items-md-center">
                            <span class="badge <?= $badgeClass ?> status-label">
                                <?= htmlspecialchars($status) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .list-group-item {
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        transition: background-color 0.3s ease-in-out, transform 0.2s ease-in-out;
        cursor: pointer;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
        transform: scale(1.02);
    }

    .badge {
        font-size: 0.9rem;
        padding: 7px 12px;
        border-radius: 12px;
    }

    @media (max-width: 768px) {
        .list-group-item {
            padding: 12px;
        }

        .status-label {
            display: block;
            width: fit-content;
        }
    }
</style>

<?php require_once VIEWS_ROOT . '/asset_for_pages/footer.php' ?>

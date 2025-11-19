<?php
declare(strict_types=1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

$pdo = require $_SERVER['DOCUMENT_ROOT'] . '/Website/config/db.php';

$userId = current_user_id();
$pageTitle = "Chat Page";

// Get ticket ID from query string
$ticketId = isset($_GET['ticket']) ? (string)$_GET['ticket'] : '';
$flash = null;

if ($ticketId === '') {
    http_response_code(400);
    die('Missing ticket parameter.');
}

// Load ticket, ensure it belongs to current user
$stmt = $pdo->prepare("
    SELECT ticket_id, user_id, title, description, status, created_at, updated_at
      FROM support_ticket
     WHERE ticket_id = :tid AND user_id = :uid
     LIMIT 1
");
$stmt->execute([':tid' => $ticketId, ':uid' => $userId]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    http_response_code(404);
    die('Ticket not found or not yours.');
}

// Handle new comment (user reply)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'new_message') {
    $body = trim($_POST['body'] ?? '');
    if ($body === '') {
        $flash = ['ok' => false, 'msg' => 'Comment cannot be empty.'];
    } else {
        try {
            $pdo->beginTransaction();

            $msg = $pdo->prepare("
                INSERT INTO support_message(ticket_id, sender_role, sender_user_id, body)
                VALUES(:tid, 'user', :uid, :body)
            ");
            $msg->execute([
                ':tid'  => $ticketId,
                ':uid'  => $userId,
                ':body' => $body,
            ]);

            // Bump ticket updated_at
            $upd = $pdo->prepare("
                UPDATE support_ticket
                   SET updated_at = CURRENT_TIMESTAMP
                 WHERE ticket_id = :tid
            ");
            $upd->execute([':tid' => $ticketId]);

            $pdo->commit();
            // Simple PRG pattern: redirect to avoid re-post on refresh
            header("Location: user_chat.php?ticket=".urlencode($ticketId));
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $flash = ['ok' => false, 'msg' => 'Could not post comment: '.$e->getMessage()];
        }
    }
}

// Load all messages for this ticket
$msgs = $pdo->prepare("
    SELECT message_id, sender_role, sender_user_id, body, posted_at
      FROM support_message
     WHERE ticket_id = :tid
  ORDER BY posted_at ASC, message_id ASC
");
$msgs->execute([':tid' => $ticketId]);
$messages = $msgs->fetchAll(PDO::FETCH_ASSOC);

// Include the header
require_once VIEWS_ROOT . '/asset_for_pages/user_header.php';
?>

<div class="page-inner mt-4">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert <?= $flash['ok'] ? 'alert-success' : 'alert-danger' ?> rounded-4">
                <?= htmlspecialchars($flash['msg']) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <h3 class="card-title"><?= htmlspecialchars($ticket['title']) ?></h3>
                <?php if (!empty($ticket['description'])): ?>
                    <p class="card-text"><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
                <?php endif; ?>
                <button class="btn btn-sm btn-primary" onclick="toggleCommentBox()">Add Comment</button>
            </div>
        </div>

        <div id="comment-section" class="mt-3">
            <!-- New comment form -->
            <div id="comment-box" class="d-none mt-3">
                <form method="post" class="row g-2">
                    <input type="hidden" name="action" value="new_message">
                    <div class="col-12">
                        <input type="text" class="form-control" id="new-comment" name="body"
                               placeholder="Write your comment...">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-sm btn-success mt-2" type="submit">Post as User</button>
                    </div>
                </form>
            </div>

            <div id="comments" class="mt-3">
                <?php if (!$messages): ?>
                    <div class="text-muted">No messages yet. Be the first to reply.</div>
                <?php else: ?>
                    <?php foreach ($messages as $m): ?>
                        <?php
                            $isAdmin  = ($m['sender_role'] === 'admin');
                            $cssClass = $isAdmin ? 'admin-comment' : 'user-comment';
                            $label    = $isAdmin ? 'Admin' : 'You';
                            $stamp    = $m['posted_at'] ? date('M d, Y H:i', strtotime($m['posted_at'])) : '';
                        ?>
                        <div class="card comment-box <?= $cssClass ?> mt-2">
                            <div class="card-body">
                                <p class="mb-1">
                                    <strong><?= htmlspecialchars($label) ?>:</strong>
                                    <?= nl2br(htmlspecialchars($m['body'])) ?>
                                </p>
                                <?php if ($stamp): ?>
                                    <div class="small text-muted"><?= htmlspecialchars($stamp) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleCommentBox() {
        document.getElementById("comment-box").classList.toggle("d-none");
        const input = document.getElementById("new-comment");
        if (!input.classList.contains('d-none')) {
            input.focus();
        }
    }
</script>

<style>
    .comment-box {
        margin-top: 10px;
    }
    .user-comment {
        background-color: #f8f9fa;
    }
    .admin-comment {
        background-color: #e3f2fd;
    }
</style>

<?php require_once VIEWS_ROOT . '/asset_for_pages/footer.php' ?>

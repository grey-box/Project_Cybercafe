<?php
declare(strict_types=1);
// Set the page title dynamically
$pageTitle = "Support Page";

require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

// Include the header
require_once VIEWS_ROOT . '/asset_for_pages/user_header.php';

// Simulated queries data (Replace this with a database query)
$queries = [
    ["id" => 1, "title" => "How to fix slow internet in the cafe?", "status" => "Resolved", "date" => "Feb 25, 2025"],
    ["id" => 2, "title" => "Which antivirus is best for CyberCafe PCs?", "status" => "Open", "date" => "Feb 20, 2025"],
    ["id" => 3, "title" => "Best settings for gaming in CyberCafe?", "status" => "Resolved", "date" => "Feb 18, 2025"],
];

?>

<div class="page-inner mt-4">
    <div class="container">
        <h2 class="mb-4">My CyberCafe Queries</h2>

        <!-- Post New Query -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Ask a Question</h5>
                <form>
                    <div class="mb-3">
                        <label for="queryTitle" class="form-label">Query Title</label>
                        <input type="text" class="form-control" id="queryTitle" placeholder="Enter your query title">
                    </div>
                    <div class="mb-3">
                        <label for="queryDetails" class="form-label">Details</label>
                        <textarea class="form-control" id="queryDetails" rows="3" placeholder="Describe your issue..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Post Query</button>
                </form>
            </div>
        </div>

        <!-- Previous Queries -->
        <h4 class="mb-3">Previous Conversations</h4>

        <div class="list-group">
            <?php foreach ($queries as $query) : ?>
                <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start support-item"
                     data-id="<?= $query['id'] ?>"
                     onclick="window.location.href='chat.php?id=<?= $query['id'] ?>&title=<?= urlencode($query['title']) ?>'">
                     
                    <div class="flex-grow-1">
                        <h5 class="mb-1"><?= $query['title'] ?></h5>
                        <small class="text-muted">Posted on <?= $query['date'] ?></small>
                    </div>
                    <div class="d-flex flex-column flex-md-row align-items-end align-items-md-center">
                        <span class="badge <?= $query['status'] == 'Resolved' ? 'bg-success' : 'bg-warning' ?> status-label">
                            <?= $query['status'] ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    // Submit Query (Simulated)
    document.querySelector("form").addEventListener("submit", function(event) {
        event.preventDefault();
        let title = document.getElementById("queryTitle").value.trim();
        let details = document.getElementById("queryDetails").value.trim();
        if (title && details) {
            alert("Your query has been submitted!");
            document.getElementById("queryTitle").value = "";
            document.getElementById("queryDetails").value = "";
        } else {
            alert("Please fill in both fields.");
        }
    });
</script>

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

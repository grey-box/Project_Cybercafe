<?php
// Set the page title dynamically
$pageTitle = "Owner - CyberCafe Support";

// Include the header
include $_SERVER['DOCUMENT_ROOT'] . '/php_views/asset_for_pages/owner_header.php';

// Simulated queries data (Replace this with a database query)
$queries = [
    ["id" => 1, "title" => "How to fix slow internet in the cafe?", "posted_by" => "User1", "status" => "Open", "date" => "Feb 25, 2025"],
    ["id" => 2, "title" => "Which antivirus is best for CyberCafe PCs?", "posted_by" => "User2", "status" => "Open", "date" => "Feb 20, 2025"],
    ["id" => 3, "title" => "Best settings for gaming in CyberCafe?", "posted_by" => "User3", "status" => "Resolved", "date" => "Feb 18, 2025"],
];

?>

<div class="page-inner mt-4">
    <div class="container">
        <h2 class="mb-4">CyberCafe Support (Owner)</h2>

        <!-- Search Bar -->
        <div class="input-group mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Search topics..." aria-label="Search topics">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
        </div>

        <!-- Questions List -->
        <div class="list-group">
            <?php foreach ($queries as $query) : ?>
                <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start support-item"
                     data-id="<?= $query['id'] ?>"
                     onclick="window.location.href='chat.php?id=<?= $query['id'] ?>&title=<?= urlencode($query['title']) ?>'">
                     
                    <div class="flex-grow-1">
                        <h5 class="mb-1"><?= $query['title'] ?></h5>
                        <p class="mb-1 text-muted">Posted by <strong><?= $query['posted_by'] ?></strong> • <?= $query['date'] ?></p>
                    </div>
                    <div class="d-flex flex-column flex-md-row align-items-end align-items-md-center">
                        <span class="badge <?= $query['status'] == 'Resolved' ? 'bg-success' : 'bg-warning' ?> status-label">
                            <?= $query['status'] ?>
                        </span>
                        <?php if ($query['status'] == "Open") : ?>
                            <button class="btn btn-sm btn-success resolve-btn ms-md-3 mt-2 mt-md-0">Mark Resolved</button>
                        <?php else : ?>
                            <button class="btn btn-sm btn-secondary ms-md-3 mt-2 mt-md-0" disabled>Resolved</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll(".resolve-btn").forEach(button => {
        button.addEventListener("click", function (event) {
            event.stopPropagation(); // Prevent triggering chat page redirect

            let parent = this.closest(".support-item");
            let statusLabel = parent.querySelector(".status-label");

            let queryId = parent.getAttribute("data-id");
            fetch("resolve_query.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `query_id=${queryId}`
            })
            .then(response => response.text())
            .then(data => {
                if (data === "success") {
                    statusLabel.classList.remove("bg-warning");
                    statusLabel.classList.add("bg-success");
                    statusLabel.textContent = "Resolved";
                    this.classList.remove("btn-success");
                    this.classList.add("btn-secondary");
                    this.textContent = "Resolved";
                    this.disabled = true;
                }
            })
            .catch(error => console.error("Error:", error));
        });
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

    .resolve-btn {
        margin-left: 10px;
    }

    @media (max-width: 768px) {
        .list-group-item {
            padding: 12px;
        }

        .resolve-btn {
            width: 100%;
            margin-left: 0;
        }

        .status-label {
            display: block;
            width: fit-content;
        }
    }
</style>

<?php include('../asset_for_pages/footer.php'); ?>

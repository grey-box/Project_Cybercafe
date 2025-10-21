<?php
// Set the page title dynamically
$pageTitle = "O - Dashboard"; 

// Include the header
include $_SERVER['DOCUMENT_ROOT'] . '/php_views/asset_for_pages/owner_header.php';
?>

<div class="page-inner mt-4">
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">How do I improve my website speed?</h3>
                <p class="card-text">I have been experiencing slow loading times on my website. Any tips to improve speed?</p>
                <button class="btn btn-sm btn-primary" onclick="toggleCommentBox()">Add Comment</button>
            </div>
        </div>

        <div id="comment-section" class="mt-3">
            <div id="comment-box" class="d-none mt-3">
                <input type="text" class="form-control" id="new-comment" placeholder="Write your comment...">
                <button class="btn btn-sm btn-info mt-2" onclick="addComment('admin')">Post comment</button>
            </div>

            <div id="comments" class="mt-3">
                <!-- Sample Conversation -->
                <div class="card comment-box user-comment">
                    <div class="card-body">
                        <p><strong>User1:</strong> I tried optimizing images, but my website is still slow.</p>
                    </div>
                </div>

                <div class="card comment-box admin-comment">
                    <div class="card-body">
                        <p><strong>Admin:</strong> Have you checked your hosting provider? Sometimes shared hosting affects speed.</p>
                    </div>
                </div>

                <div class="card comment-box user-comment">
                    <div class="card-body">
                        <p><strong>User1:</strong> Good point! I will check with my provider.</p>
                    </div>
                </div>

                <div class="card comment-box admin-comment">
                    <div class="card-body">
                        <p><strong>Admin:</strong> Also, try using caching plugins if you are using WordPress.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleCommentBox() {
        document.getElementById("comment-box").classList.toggle("d-none");
    }

    function addComment(role) {
        let commentText = document.getElementById("new-comment").value.trim();
        if (commentText === "") return;

        let commentClass = role === "admin" ? "admin-comment" : "user-comment";
        let userRole = role === "admin" ? "Admin" : "User1";

        let commentHtml = `
            <div class="card comment-box ${commentClass} mt-2">
                <div class="card-body">
                    <p><strong>${userRole}:</strong> ${commentText}</p>
                </div>
            </div>
        `;
        document.getElementById("comments").innerHTML += commentHtml;
        document.getElementById("new-comment").value = "";
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

<?php include $_SERVER['DOCUMENT_ROOT'] .'/php_views/asset_for_pages/footer.php'; ?>

<?php
declare(strict_types=1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';
$pageTitle = "Search";
require_once VIEWS_ROOT . '/asset_for_pages/user_header.php';
?>

<div class="page-header">
  <ul class="breadcrumbs mb-3">
    <li class="nav-home">
      <a href="<?= WEB_BASE ?>/php_views/user/user_profile.php"><i class="icon-home"></i></a>
    </li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Search</a></li>
  </ul>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-3">Search your account</h4>
        <form class="input-group" action="<?= WEB_BASE ?>/php_views/user/search_results.php" method="get">
          <input type="text" class="form-control" name="q" placeholder="Try: invoice 102, payment, session, plan, allowed site…" />
          <button class="btn btn-primary" type="submit"><i class="fa fa-search me-1"></i>Search</button>
        </form>

        <div class="mt-4">
          <div class="text-muted mb-2">Quick scopes</div>
          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-light border" href="<?= WEB_BASE ?>/php_views/user/search_results.php?scope=payments">Payments</a>
            <a class="btn btn-light border" href="<?= WEB_BASE ?>/php_views/user/search_results.php?scope=invoices">Invoices</a>
            <a class="btn btn-light border" href="<?= WEB_BASE ?>/php_views/user/search_results.php?scope=sessions">Sessions</a>
            <a class="btn btn-light border" href="<?= WEB_BASE ?>/php_views/user/search_results.php?scope=reports">Reports</a>
            <a class="btn btn-light border" href="<?= WEB_BASE ?>/php_views/user/search_results.php?scope=allowed">Allowed Sites</a>
          </div>
        </div>

        <hr class="my-4" />
        <div class="text-muted small">Tip: results are limited to your own data (payments, invoices, sessions, reports, allowed sites you can visit).</div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-3">Recent searches</h6>
        <ul class="list-group list-group-flush" id="recentSearchList">
          <!-- Filled by JS (localStorage) -->
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
  // Simple recent search viewer (localStorage, per-browser)
  const key = 'cc_recent_searches';
  function renderRecent(){
    const ul = document.getElementById('recentSearchList');
    const list = JSON.parse(localStorage.getItem(key) || '[]');
    ul.innerHTML = list.slice(0,8).map(q => `
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <a href="<?= WEB_BASE ?>/php_views/user/search_results.php?q=${encodeURIComponent(q)}">${q}</a>
        <span class="badge bg-light text-muted">${new Date().toLocaleDateString()}</span>
      </li>
    `).join('') || '<li class="list-group-item text-muted">No recent searches</li>';
  }
  renderRecent();
</script>

<?php require_once VIEWS_ROOT . '/asset_for_pages/footer.php'; ?>

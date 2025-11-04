<?php
declare(strict_types=1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';
$pageTitle = "Notifications";
?>

<?php require_once VIEWS_ROOT . '/asset_for_pages/user_header.php'; ?>

<div class="container">
  <div class="page-inner">
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
      <div>
        <h3 class="fw-bold mb-1"><?= htmlspecialchars($pageTitle) ?></h3>
        <div class="text-muted">View and manage your recent activity.</div>
      </div>
      <div class="search-sm mt-3 mt-sm-0" style="min-width:260px;">
        <div class="input-group">
          <span class="input-group-text"><i class="fa fa-search"></i></span>
          <input id="notifSearch" type="text" class="form-control" placeholder="Search notifications..." />
        </div>
      </div>
    </div>

    <div class="card notif-card">
      <div class="card-body">
        <!-- Tabs -->
        <ul class="nav nav-pills mb-3" id="notifTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-all" data-bs-toggle="pill" data-bs-target="#pane-all" type="button" role="tab">All</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-unread" data-bs-toggle="pill" data-bs-target="#pane-unread" type="button" role="tab">
              Unread <span class="badge bg-primary ms-1" id="unreadCountBadge">0</span>
            </button>
          </li>
        </ul>

        <div class="d-flex gap-2 mb-3">
          <button id="markAllBtn" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-check-circle me-1"></i> Mark all as read
          </button>
          <button id="clearReadBtn" class="btn btn-sm btn-outline-danger">
            <i class="fa fa-trash-alt me-1"></i> Clear read
          </button>
        </div>

        <!-- Lists -->
        <div class="tab-content">
          <!-- ALL -->
          <div class="tab-pane fade show active" id="pane-all" role="tabpanel">
            <div id="listAll" class="list-group list-group-flush"></div>
            <div id="emptyAll" class="notif-empty d-none">
              <i class="fa fa-bell-slash fa-2x mb-2"></i>
              <div>No notifications yet.</div>
            </div>
          </div>
          <!-- UNREAD -->
          <div class="tab-pane fade" id="pane-unread" role="tabpanel">
            <div id="listUnread" class="list-group list-group-flush"></div>
            <div id="emptyUnread" class="notif-empty d-none">
              <i class="fa fa-inbox fa-2x mb-2"></i>
              <div>All caught up!</div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<style>
  .notif-card{ border:0; box-shadow:0 8px 20px rgba(0,0,0,.06); border-radius:1rem; }
  .notif-item{ transition: background .2s ease; }
  .notif-item.unread{ background: #f7fbff; }
  .notif-item:hover{ background:#f1f5f9; }
  .notif-dot{ width:.55rem; height:.55rem; border-radius:50%; background:#2c7be5; display:inline-block; margin-right:.5rem; }
  .notif-icon-wrap{ width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; margin-right:12px; }
  .notif-primary{ background:#2c7be5; }
  .notif-success{ background:#00bf9a; }
  .notif-warning{ background:#ffb547; }
  .notif-danger{ background:#e63757; }
  .notif-time{ font-size:.85rem; color:#6c757d; white-space:nowrap; }
  .notif-title{ font-weight:600; }
  .notif-text{ color:#4f5d73; margin:2px 0 0; }
  .notif-empty{ text-align:center; padding:3rem 1rem; color:#6c757d; }
  .search-sm .form-control{ border-radius:.5rem; }
  .badge-soft{ background:rgba(19,164,99,.1); color:#13a463; }
  @media (max-width: 576px){ .notif-time{ display:none; } }
</style>

<script>
  const notifications = [
    {
      id: 1,
      title: "New user registered",
      text: "Your account has been created.",
      time: "5 minutes ago",
      iconClass: "notif-primary fa fa-user-plus",
      href: "#",
      unread: true
    },
    {
      id: 2,
      title: "Broadcast from Chinmay",
      text: "Chinmay posted a broadcast message.",
      time: "12 minutes ago",
      iconClass: "notif-success fa fa-comment",
      href: "#",
      unread: true
    },
    {
      id: 3,
      title: "Broadcast from Aniket",
      text: "Aniket posted a broadcast message.",
      time: "12 minutes ago",
      iconClass: "notif-success fa fa-comment",
      href: "#",
      unread: false
    },
    {
      id: 4,
      title: "Maintenance window",
      text: "Planned maintenance tonight at 11pm.",
      time: "2 hours ago",
      iconClass: "notif-warning fa fa-tools",
      href: "#",
      unread: false
    }
  ];

  function notifItemTemplate(n){
    const unreadClass = n.unread ? ' unread' : '';
    return `
      <a href="${n.href}" class="list-group-item list-group-item-action notif-item d-flex align-items-start${unreadClass}" data-id="${n.id}">
        <div class="notif-icon-wrap ${n.iconClass.split(' ')[0]} me-2">
          <i class="${n.iconClass.split(' ').slice(1).join(' ')}"></i>
        </div>
        <div class="flex-grow-1">
          <div class="d-flex align-items-center justify-content-between">
            <div class="notif-title">
              ${n.unread ? '<span class="notif-dot"></span>' : ''}
              ${n.title}
            </div>
            <div class="notif-time ms-2">${n.time}</div>
          </div>
          <p class="notif-text mb-1">${n.text}</p>
          ${n.unread ? '<span class="badge badge-soft mark-read" role="button">Mark read</span>' : ''}
        </div>
      </a>`;
  }

  function renderLists(filterText=''){
    const listAll = document.getElementById('listAll');
    const listUnread = document.getElementById('listUnread');
    const q = filterText.trim().toLowerCase();

    const filtered = notifications.filter(n =>
      !q || n.title.toLowerCase().includes(q) || n.text.toLowerCase().includes(q)
    );
    const unread = filtered.filter(n => n.unread);

    listAll.innerHTML = filtered.map(notifItemTemplate).join('');
    listUnread.innerHTML = unread.map(notifItemTemplate).join('');

    document.getElementById('emptyAll').classList.toggle('d-none', filtered.length !== 0);
    document.getElementById('emptyUnread').classList.toggle('d-none', unread.length !== 0);

    updateUnreadBadges();
    attachItemHandlers();
  }

  function updateUnreadBadges(){
    const unreadCount = notifications.filter(n => n.unread).length;
    const badge = document.getElementById('unreadCountBadge');
    if (badge) badge.textContent = unreadCount;

    const bellBadge = document.querySelector('.topbar-nav .notification');
    if (bellBadge) bellBadge.textContent = unreadCount;
  }

  function attachItemHandlers(){
    document.querySelectorAll('.mark-read').forEach(btn => {
      btn.addEventListener('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        const id = parseInt(this.closest('.notif-item').dataset.id, 10);
        const item = notifications.find(n => n.id === id);
        if (item){ item.unread = false; renderLists(document.getElementById('notifSearch').value); }
      });
    });
  }

  document.getElementById('markAllBtn').addEventListener('click', function(){
    notifications.forEach(n => n.unread = false);
    renderLists(document.getElementById('notifSearch').value);
  });

  document.getElementById('clearReadBtn').addEventListener('click', function(){
    for (let i = notifications.length - 1; i >= 0; i--){
      if (!notifications[i].unread) notifications.splice(i,1);
    }
    renderLists(document.getElementById('notifSearch').value);
  });

  document.getElementById('notifSearch').addEventListener('input', function(){
    renderLists(this.value);
  });

  renderLists();
</script>

<?php require_once VIEWS_ROOT . '/asset_for_pages/footer.php'; ?>

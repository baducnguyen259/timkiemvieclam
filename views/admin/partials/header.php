<header class="admin-header">
  <div class="header-left">
    <button class="btn-toggle-sidebar">
      <i class="fa-solid fa-bars"></i>
    </button>

    <h1><?= htmlspecialchars($title ?? 'Tổng quan') ?></h1>
  </div>

  <div class="header-right">
    <a href="<?= BASE_PATH ?>/" target="_blank" class="btn btn-sm">
      <i class="fa-solid fa-arrow-up-right-from-square"></i>
      Xem trang web
    </a>

    <?php if (isset($GLOBALS['current_user'])): ?>
    <div class="user-dropdown">
      <button class="user-dropdown-toggle">
        <?php if ($GLOBALS['current_user']->avatar): ?>
        <img src="<?= BASE_PATH ?>/uploads/<?= htmlspecialchars($GLOBALS['current_user']->avatar) ?>" alt="Ảnh đại diện" class="user-avatar">
        <?php else: ?>
        <i class="fa-solid fa-circle-user"></i>
        <?php endif; ?>
        <span><?= htmlspecialchars($GLOBALS['current_user']->full_name) ?></span>
        <i class="fa-solid fa-chevron-down"></i>
      </button>

      <div class="user-dropdown-menu">
        <a href="<?= BASE_PATH ?>/admin/account">
          <i class="fa-solid fa-user"></i>
          Tài khoản của tôi
        </a>
        <form method="POST" action="<?= BASE_PATH ?>/admin/auth/logout" class="dropdown-form">
            <?= csrf_field() ?>
          <button type="submit" class="dropdown-button">
            <i class="fa-solid fa-right-from-bracket"></i>
            Đăng xuất
          </button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
</header>

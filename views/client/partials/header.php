<header class="header" id="main-header">
  <div class="container">
    <div class="header-inner">
      <div class="logo">
        <a href="<?= BASE_PATH ?>/">
          <i class="fa-solid fa-briefcase"></i>
          <span><strong>DDS</strong></span>
        </a>
      </div>

      <button class="menu-toggle" id="menu-toggle" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
      </button>

      <nav class="nav" id="main-nav">
        <ul>
          <li><a href="<?= BASE_PATH ?>/" class="nav-link"><i class="fa-solid fa-house"></i> Trang chủ</a></li>
          <li><a href="<?= BASE_PATH ?>/jobs" class="nav-link"><i class="fa-solid fa-magnifying-glass"></i> Việc làm</a>
          </li>

          <?php if (!empty($GLOBALS['layoutProductsCategory'])): ?>
          <li class="dropdown">
            <a href="#" class="nav-link"><i class="fa-solid fa-layer-group"></i> Danh mục <i
                class="fa-solid fa-chevron-down dropdown-arrow"></i></a>
            <ul class="dropdown-menu">
              <?php foreach ($GLOBALS['layoutProductsCategory'] as $category): ?>
              <li>
                <a href="<?= BASE_PATH ?>/jobs/<?= htmlspecialchars($category->slug) ?>">
                  <?= htmlspecialchars($category->title) ?>
                </a>

                <?php if (!empty($category->children)): ?>
                <ul class="sub-menu">
                  <?php foreach ($category->children as $child): ?>
                  <li>
                    <a href="<?= BASE_PATH ?>/jobs/<?= htmlspecialchars($child->slug) ?>">
                      <?= htmlspecialchars($child->title) ?>
                    </a>
                  </li>
                  <?php endforeach; ?>
                </ul>
                <?php endif; ?>
              </li>
              <?php endforeach; ?>
            </ul>
          </li>
          <?php endif; ?>

          <li>
            <a href="<?= BASE_PATH ?>/saved-jobs" class="nav-link">
              <i class="fa-solid fa-bookmark"></i> Đã lưu
              <?php if (isset($GLOBALS['miniSavedJobs']) && ($GLOBALS['miniSavedJobs']->total_saved_jobs ?? 0) > 0): ?>
              <span class="badge-count"><?= $GLOBALS['miniSavedJobs']->total_saved_jobs ?></span>
              <?php endif; ?>
            </a>
          </li>
        </ul>
      </nav>

      <div class="user-menu">
        <?php if (isset($GLOBALS['current_user'])): ?>
        <div class="dropdown user-dropdown">
          <a href="#" class="user-avatar-btn">
            <div class="avatar-circle">
              <?= strtoupper(substr($GLOBALS['current_user']->full_name, 0, 1)) ?>
            </div>
            <span class="user-name"><?= htmlspecialchars($GLOBALS['current_user']->full_name) ?></span>
            <i class="fa-solid fa-chevron-down dropdown-arrow"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-right">
            <li class="dropdown-header">
              <strong><?= htmlspecialchars($GLOBALS['current_user']->full_name) ?></strong>
            </li>
            <li class="dropdown-divider"></li>
            <li><a href="<?= BASE_PATH ?>/user/info"><i class="fa-solid fa-user"></i> Thông tin cá nhân</a></li>
            <li><a href="<?= BASE_PATH ?>/applications"><i class="fa-solid fa-file-lines"></i> Đơn ứng tuyển</a></li>
            <li><a href="<?= BASE_PATH ?>/saved-jobs"><i class="fa-solid fa-bookmark"></i> Việc đã lưu</a></li>
            <li class="dropdown-divider"></li>
            <li>
              <form method="POST" action="<?= BASE_PATH ?>/user/logout" class="dropdown-form">
                  <?= csrf_field() ?>
                <button type="submit" class="dropdown-button text-danger">
                  <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                </button>
              </form>
            </li>
          </ul>
        </div>
        <?php else: ?>
        <a href="<?= BASE_PATH ?>/user/login" class="btn btn-outline" id="btn-login">
          <i class="fa-solid fa-right-to-bracket"></i> Đăng nhập
        </a>
        <a href="<?= BASE_PATH ?>/user/register" class="btn btn-primary" id="btn-register">
          <i class="fa-solid fa-user-plus"></i> Đăng ký
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>
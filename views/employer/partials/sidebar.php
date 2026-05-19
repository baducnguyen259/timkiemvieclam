<aside class="admin-sidebar">
  <div class="sidebar-header">
    <h2>DDS</h2>
    <span>Nhà tuyển dụng</span>
  </div>

  <nav class="sidebar-nav">
    <ul>
      <li>
        <a href="<?= BASE_PATH ?>/employer/dashboard"
          class="<?= strpos($_SERVER['REQUEST_URI'], '/employer/dashboard') !== false || $_SERVER['REQUEST_URI'] === BASE_PATH . '/employer' ? 'active' : '' ?>">
          <i class="fa-solid fa-gauge-high"></i>
          Tổng quan
        </a>
      </li>

      <li>
        <a href="<?= BASE_PATH ?>/employer/job"
          class="<?= strpos($_SERVER['REQUEST_URI'], '/employer/job') !== false ? 'active' : '' ?>">
          <i class="fa-solid fa-briefcase"></i>
          Quản lý tin tuyển dụng
        </a>
      </li>

      <li>
        <a href="<?= BASE_PATH ?>/employer/job/create"
          class="<?= strpos($_SERVER['REQUEST_URI'], '/employer/job/create') !== false ? 'active' : '' ?>">
          <i class="fa-solid fa-plus"></i>
          Đăng tin mới
        </a>
      </li>

      <li>
        <a href="<?= BASE_PATH ?>/employer/application"
          class="<?= strpos($_SERVER['REQUEST_URI'], '/employer/application') !== false ? 'active' : '' ?>">
          <i class="fa-solid fa-file-circle-check"></i>
          Xét duyệt ứng tuyển
        </a>
      </li>
    </ul>
  </nav>
</aside>

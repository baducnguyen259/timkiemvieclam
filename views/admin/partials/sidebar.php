<aside class="admin-sidebar">
  <div class="sidebar-header">
    <h2>DDS</h2>
    <span>Bảng quản trị</span>
  </div>

  <nav class="sidebar-nav">
    <ul>
      <li>
        <a href="<?= BASE_PATH ?>/admin/dashboard"
          class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/dashboard') !== false ? 'active' : '' ?>">
          <i class="fa-solid fa-gauge-high"></i>
          Tổng quan
        </a>
      </li>

      <li>
        <a href="<?= BASE_PATH ?>/admin/job"
          class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/job') !== false && strpos($_SERVER['REQUEST_URI'], '/admin/job-category') === false ? 'active' : '' ?>">
          <i class="fa-solid fa-briefcase"></i>
          Quản lý công việc
        </a>
      </li>

      <li>
        <a href="<?= BASE_PATH ?>/admin/account"
          class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/account') !== false ? 'active' : '' ?>">
          <i class="fa-solid fa-users"></i>
          Quản lý tài khoản
        </a>
      </li>

      <li>
        <a href="<?= BASE_PATH ?>/admin/job-category"
          class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/job-category') !== false ? 'active' : '' ?>">
          <i class="fa-solid fa-folder-open"></i>
          Danh mục công việc
        </a>
      </li>

      <li>
        <a href="<?= BASE_PATH ?>/admin/role"
          class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/role') !== false ? 'active' : '' ?>">
          <i class="fa-solid fa-shield-halved"></i>
          Phân quyền
        </a>
      </li>
    </ul>
  </nav>
</aside>

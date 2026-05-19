<?php ob_start(); ?>

<div class="dashboard">
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon bg-blue">
        <i class="fa-solid fa-briefcase"></i>
      </div>
      <div class="stat-content">
        <h3><?= $totalJobs ?? 0 ?></h3>
        <p>Tổng tin tuyển dụng</p>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon bg-green">
        <i class="fa-solid fa-circle-check"></i>
      </div>
      <div class="stat-content">
        <h3><?= $activeJobs ?? 0 ?></h3>
        <p>Tin đang hoạt động</p>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon bg-orange">
        <i class="fa-solid fa-circle-pause"></i>
      </div>
      <div class="stat-content">
        <h3><?= $inactiveJobs ?? 0 ?></h3>
        <p>Tin tạm dừng</p>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon bg-purple">
        <i class="fa-solid fa-file-lines"></i>
      </div>
      <div class="stat-content">
        <h3><?= $pendingApplications ?? 0 ?></h3>
        <p>Ứng tuyển chờ duyệt</p>
      </div>
    </div>
  </div>

  <div class="dashboard-grid">
    <div class="dashboard-card">
      <h2>Ứng tuyển</h2>
      <div class="quick-stats">
        <div class="quick-stat-item">
          <span class="label">Tổng số ứng tuyển:</span>
          <span class="value"><?= $totalApplications ?? 0 ?></span>
        </div>
        <div class="quick-stat-item">
          <span class="label">Đang chờ xử lý:</span>
          <span class="value"><?= $pendingApplications ?? 0 ?></span>
        </div>
      </div>
    </div>

    <div class="dashboard-card">
      <h2>Tác vụ nhanh</h2>
      <div class="page-actions">
        <a href="<?= BASE_PATH ?>/employer/job/create" class="btn btn-primary">
          <i class="fa-solid fa-plus"></i>
          Đăng tin mới
        </a>
        <a href="<?= BASE_PATH ?>/employer/job" class="btn btn-secondary">
          <i class="fa-solid fa-list"></i>
          Quản lý tin
        </a>
        <a href="<?= BASE_PATH ?>/employer/application" class="btn btn-secondary">
          <i class="fa-solid fa-file-circle-check"></i>
          Xét duyệt ứng tuyển
        </a>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

<?php ob_start(); ?>

<div class="dashboard">
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon bg-blue">
        <i class="fa-solid fa-briefcase"></i>
      </div>
      <div class="stat-content">
        <h3><?= $totalJobs ?? 0 ?></h3>
        <p>Tổng công việc</p>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon bg-green">
        <i class="fa-solid fa-circle-check"></i>
      </div>
      <div class="stat-content">
        <h3><?= $activeJobs ?? 0 ?></h3>
        <p>Công việc đang hoạt động</p>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon bg-orange">
        <i class="fa-solid fa-users"></i>
      </div>
      <div class="stat-content">
        <h3>0</h3>
        <p>Người dùng</p>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon bg-purple">
        <i class="fa-solid fa-file-lines"></i>
      </div>
      <div class="stat-content">
        <h3>0</h3>
        <p>Đơn ứng tuyển</p>
      </div>
    </div>
  </div>

  <div class="dashboard-grid">
    <div class="dashboard-card">
      <h2>Hoạt động gần đây</h2>
      <div class="activity-list">
        <p class="text-muted">Chưa có hoạt động nào</p>
      </div>
    </div>

    <div class="dashboard-card">
      <h2>Thống kê nhanh</h2>
      <div class="quick-stats">
        <div class="quick-stat-item">
          <span class="label">Công việc hôm nay:</span>
          <span class="value">0</span>
        </div>
        <div class="quick-stat-item">
          <span class="label">Ứng tuyển hôm nay:</span>
          <span class="value">0</span>
        </div>
        <div class="quick-stat-item">
          <span class="label">Người dùng mới:</span>
          <span class="value">0</span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>
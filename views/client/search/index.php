<?php ob_start(); ?>
<?php
// Chuẩn hóa đường dẫn ảnh/logo để view dùng được cả URL tuyệt đối, public path và upload path.
$buildAssetUrl = static function($path) {
  if (!is_string($path)) {
    return '';
  }

  $path = trim($path);
  if ($path === '') {
    return '';
  }

  if (preg_match('#^https?://#i', $path)) {
    return $path;
  }

  if (strpos($path, 'public/') === 0) {
    $path = substr($path, 7);
  }

  if (strpos($path, '/') !== false) {
    return BASE_PATH . '/' . ltrim($path, '/');
  }

  return BASE_PATH . '/uploads/' . ltrim($path, '/');
};

// Định dạng khoảng lương thành chuỗi hiển thị thân thiện cho kết quả tìm kiếm.
$formatSalary = static function($salaryMin, $salaryMax) {
  $salaryMin = (float)$salaryMin;
  $salaryMax = (float)$salaryMax;

  if ($salaryMin > 0 && $salaryMax > 0) {
    if (abs($salaryMin - $salaryMax) < 0.01) {
      return number_format($salaryMin) . ' VNĐ';
    }
    return number_format($salaryMin) . ' - ' . number_format($salaryMax) . ' VNĐ';
  }

  if ($salaryMin > 0) {
    return number_format($salaryMin) . ' VNĐ';
  }

  if ($salaryMax > 0) {
    return number_format($salaryMax) . ' VNĐ';
  }

  return 'Thỏa thuận';
};
?>

<div class="search-page">
  <h1><?= htmlspecialchars($title) ?></h1>

  <div class="search-bar">
    <form action="<?= BASE_PATH ?>/search" method="GET" class="search-form">
      <input type="text" name="keyword" placeholder="Tìm kiếm công việc..."
        value="<?= htmlspecialchars($keyword ?? '') ?>" class="form-control" required>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
    </form>
  </div>

  <?php if (!empty($jobs)): ?>
  <p class="search-count">Tìm thấy <strong><?= count($jobs) ?></strong> công việc phù hợp</p>

  <div class="jobs-list">
    <?php foreach ($jobs as $job): ?>
    <?php
      $logoSource = $job->company_logo ?? ($job->thumbnail ?? '');
      $logoUrl = $buildAssetUrl($logoSource);
    ?>
    <div class="job-item">
      <div class="job-thumbnail job-logo">
        <?php if ($logoUrl !== ''): ?>
        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($job->company_name ?: $job->title) ?>">
        <?php else: ?>
        <div class="no-image"><i class="fa-solid fa-building"></i></div>
        <?php endif; ?>
      </div>

      <div class="job-content">
        <h3>
          <a href="<?= BASE_PATH ?>/jobs/detail/<?= htmlspecialchars($job->slug) ?>">
            <?= htmlspecialchars($job->title) ?>
          </a>
        </h3>

        <?php if (!empty($job->company_name)): ?>
        <p class="job-company">
          <i class="fa-solid fa-building"></i>
          <?= htmlspecialchars($job->company_name) ?>
        </p>
        <?php endif; ?>

        <div class="job-info">
          <span class="location">
            <i class="fa-solid fa-location-dot"></i>
            <?= htmlspecialchars($job->location ?? 'Không xác định') ?>
          </span>

          <span class="type">
            <i class="fa-solid fa-briefcase"></i>
            <?= htmlspecialchars(JobType::label($job->type ?? 'Full-time')) ?>
          </span>

          <span class="salary">
            <i class="fa-solid fa-money-bill-wave"></i>
            <?= htmlspecialchars($formatSalary($job->salary_min ?? 0, $job->salary_max ?? 0)) ?>
          </span>
        </div>

        <div class="job-footer">
          <div class="job-footer-meta">
            <span class="time">
              <i class="fa-regular fa-clock"></i>
              <?= date('d/m/Y', strtotime($job->created_at)) ?>
            </span>

            <?php if ($job->featured === '1'): ?>
            <span class="badge badge-featured"><i class="fa-solid fa-star"></i> Nổi bật</span>
            <?php endif; ?>
          </div>

          <a href="<?= BASE_PATH ?>/jobs/detail/<?= htmlspecialchars($job->slug) ?>" class="btn btn-primary btn-sm btn-job-detail">
            <i class="fa-solid fa-eye"></i> Xem chi tiết
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php else: ?>
  <div class="no-results">
    <i class="fa-solid fa-magnifying-glass" style="font-size: 48px; color: var(--gray-300); margin-bottom: 16px;"></i>
    <p>Không tìm thấy công việc nào phù hợp với từ khóa "<strong><?= htmlspecialchars($keyword ?? '') ?></strong>".</p>
    <a href="<?= BASE_PATH ?>/jobs" class="btn btn-primary">Xem tất cả công việc</a>
  </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

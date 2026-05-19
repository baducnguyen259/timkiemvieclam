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

// Định dạng khoảng lương thành chuỗi hiển thị thân thiện cho thẻ việc làm.
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

<section class="hero">
  <div class="hero-bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
  </div>
  <div class="container">
    <div class="hero-content">
      <h1>Tìm công việc <span class="text-gradient">mơ ước</span> của bạn</h1>
      <p class="hero-subtitle">Hàng nghìn cơ hội việc làm từ các công ty hàng đầu đang chờ bạn</p>
      <form action="<?= BASE_PATH ?>/search" method="GET" class="search-form" id="hero-search-form">
        <div class="search-input-wrapper">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="keyword" placeholder="Nhập tên công việc, vị trí..." id="search-keyword">
        </div>
        <button type="submit" class="btn btn-accent btn-large" id="btn-search">
          <i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm
        </button>
      </form>
      <div class="hero-stats">
        <div class="stat-item">
          <i class="fa-solid fa-briefcase"></i>
          <span><strong>1000+</strong> Việc làm</span>
        </div>
        <div class="stat-item">
          <i class="fa-solid fa-building"></i>
          <span><strong>500+</strong> Công ty</span>
        </div>
        <div class="stat-item">
          <i class="fa-solid fa-users"></i>
          <span><strong>10K+</strong> Ứng viên</span>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if (!empty($GLOBALS['layoutProductsCategory'])): ?>
<section class="categories-section">
  <div class="container">
    <div class="section-header">
      <h2><i class="fa-solid fa-layer-group"></i> Danh mục việc làm</h2>
      <a href="<?= BASE_PATH ?>/jobs" class="section-link">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="categories-grid">
      <?php
      $catIcons = [
        'cong-nghe-thong-tin' => 'fa-solid fa-laptop-code',
        'marketing' => 'fa-solid fa-bullhorn',
        'kinh-doanh' => 'fa-solid fa-chart-line',
        'ke-toan-tai-chinh' => 'fa-solid fa-calculator',
        'nhan-su' => 'fa-solid fa-people-group',
      ];
      $catColors = ['#4f46e5', '#7c3aed', '#0891b2', '#059669', '#d97706'];
      $i = 0;
      foreach ($GLOBALS['layoutProductsCategory'] as $category):
        $icon = $catIcons[$category->slug] ?? 'fa-solid fa-folder';
        $color = $catColors[$i % count($catColors)];
      ?>
      <a href="<?= BASE_PATH ?>/jobs/<?= htmlspecialchars($category->slug) ?>" class="category-card" style="--cat-color: <?= $color ?>">
        <div class="category-icon">
          <i class="<?= $icon ?>"></i>
        </div>
        <h3><?= htmlspecialchars($category->title) ?></h3>
        <?php if (!empty($category->children)): ?>
        <span class="category-count"><?= count($category->children) ?> chuyên ngành</span>
        <?php endif; ?>
      </a>
      <?php $i++; endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($jobsFeatured)): ?>
<section class="featured-jobs">
  <div class="container">
    <div class="section-header">
      <h2><i class="fa-solid fa-star"></i> Công việc nổi bật</h2>
      <a href="<?= BASE_PATH ?>/jobs" class="section-link">Xem thêm <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="job-grid">
      <?php foreach ($jobsFeatured as $job): ?>
      <?php
        $logoSource = $job->company_logo ?? ($job->thumbnail ?? '');
        $logoUrl = $buildAssetUrl($logoSource);
      ?>
      <div class="job-card featured-card" id="job-<?= $job->id ?>">
        <div class="job-card-header job-card-header-compact">
          <div class="job-card-company">
            <div class="job-card-logo">
              <?php if ($logoUrl !== ''): ?>
              <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($job->company_name ?: $job->title) ?>" loading="lazy">
              <?php else: ?>
              <i class="fa-solid fa-building"></i>
              <?php endif; ?>
            </div>
            <div class="job-card-company-meta">
              <span class="job-card-company-name"><?= htmlspecialchars($job->company_name ?: 'Nhà tuyển dụng') ?></span>
              <span class="job-card-company-location"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($job->location ?? 'Không xác định') ?></span>
            </div>
          </div>
          <span class="badge badge-featured"><i class="fa-solid fa-star"></i> Nổi bật</span>
        </div>

        <div class="job-card-body">
          <h3>
            <a href="<?= BASE_PATH ?>/jobs/detail/<?= htmlspecialchars($job->slug) ?>">
              <?= htmlspecialchars($job->title) ?>
            </a>
          </h3>

          <div class="job-card-info">
            <span class="salary">
              <i class="fa-solid fa-money-bill-wave"></i>
              <?= htmlspecialchars($formatSalary($job->salary_min ?? 0, $job->salary_max ?? 0)) ?>
            </span>
          </div>

          <div class="job-card-footer">
            <span class="badge"><?= htmlspecialchars(JobType::label($job->type ?? 'Full-time')) ?></span>
            <?php if (!empty($job->category_name)): ?>
            <span class="badge badge-category"><?= htmlspecialchars($job->category_name) ?></span>
            <?php endif; ?>
          </div>

          <div class="job-card-actions">
            <a href="<?= BASE_PATH ?>/jobs/detail/<?= htmlspecialchars($job->slug) ?>" class="btn btn-primary btn-sm">
              <i class="fa-solid fa-eye"></i> Xem chi tiết
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($jobsNew)): ?>
<section class="new-jobs">
  <div class="container">
    <div class="section-header">
      <h2><i class="fa-solid fa-clock"></i> Việc làm mới nhất</h2>
      <a href="<?= BASE_PATH ?>/jobs" class="section-link">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="job-grid">
      <?php foreach ($jobsNew as $job): ?>
      <?php
        $logoSource = $job->company_logo ?? ($job->thumbnail ?? '');
        $logoUrl = $buildAssetUrl($logoSource);
      ?>
      <div class="job-card" id="new-job-<?= $job->id ?>">
        <div class="job-card-body">
          <div class="job-card-company">
            <div class="job-card-logo">
              <?php if ($logoUrl !== ''): ?>
              <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($job->company_name ?: $job->title) ?>" loading="lazy">
              <?php else: ?>
              <i class="fa-solid fa-building"></i>
              <?php endif; ?>
            </div>
            <div class="job-card-company-meta">
              <span class="job-card-company-name"><?= htmlspecialchars($job->company_name ?: 'Nhà tuyển dụng') ?></span>
              <span class="job-card-company-location"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($job->location ?? 'Không xác định') ?></span>
            </div>
          </div>

          <h3>
            <a href="<?= BASE_PATH ?>/jobs/detail/<?= htmlspecialchars($job->slug) ?>">
              <?= htmlspecialchars($job->title) ?>
            </a>
          </h3>

          <div class="job-card-info">
            <span class="salary">
              <i class="fa-solid fa-money-bill-wave"></i>
              <?= htmlspecialchars($formatSalary($job->salary_min ?? 0, $job->salary_max ?? 0)) ?>
            </span>
          </div>

          <div class="job-card-footer">
            <span class="badge"><?= htmlspecialchars(JobType::label($job->type ?? 'Full-time')) ?></span>
            <span class="time">
              <i class="fa-regular fa-clock"></i>
              <?= date('d/m/Y', strtotime($job->created_at)) ?>
            </span>
          </div>

          <div class="job-card-actions">
            <a href="<?= BASE_PATH ?>/jobs/detail/<?= htmlspecialchars($job->slug) ?>" class="btn btn-primary btn-sm">
              <i class="fa-solid fa-eye"></i> Xem chi tiết
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2>Sẵn sàng tìm kiếm cơ hội mới?</h2>
      <p>Tạo tài khoản miễn phí và bắt đầu ứng tuyển ngay hôm nay</p>
      <div class="cta-buttons">
        <a href="<?= BASE_PATH ?>/user/register" class="btn btn-accent btn-large" id="cta-register">
          <i class="fa-solid fa-user-plus"></i> Đăng ký ngay
        </a>
        <a href="<?= BASE_PATH ?>/jobs" class="btn btn-outline-light btn-large" id="cta-browse">
          <i class="fa-solid fa-magnifying-glass"></i> Xem việc làm
        </a>
      </div>
    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

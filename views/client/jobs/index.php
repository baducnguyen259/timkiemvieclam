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

// Tạo URL phân trang nhưng vẫn giữ nguyên các tham số lọc hiện tại.
$buildPageUrl = static function($page) {
  $query = $_GET;
  $query['page'] = $page;
  return '?' . http_build_query($query);
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

<div class="jobs-page">
  <h1><?= htmlspecialchars($title) ?></h1>

  <div class="filter-section">
    <form method="GET" action="" class="filter-form">
      <div class="filter-group">
        <input type="text" name="keyword" placeholder="Tìm kiếm công việc..."
          value="<?= htmlspecialchars($keyword ?? '') ?>" class="form-control">
      </div>

      <div class="filter-group">
        <select name="location" class="form-control">
          <?php foreach ($filterLocation as $item): ?>
          <option value="<?= htmlspecialchars($item['name']) ?>" <?= $item['selected'] ?>>
            <?= htmlspecialchars($item['label'] ?? ($item['name'] ?: 'Tất cả địa điểm')) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="filter-group">
        <select name="type" class="form-control">
          <?php foreach ($filterType as $item): ?>
          <option value="<?= htmlspecialchars($item['name']) ?>" <?= $item['selected'] ?>>
            <?= htmlspecialchars($item['label'] ?? ($item['name'] ?: 'Tất cả loại hình')) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="filter-group">
        <select name="sortKey" class="form-control">
          <option value="">Sắp xếp</option>
          <option value="position" <?= ($_GET['sortKey'] ?? '') === 'position' ? 'selected' : '' ?>>
            Vị trí
          </option>
          <option value="created_at" <?= ($_GET['sortKey'] ?? '') === 'created_at' ? 'selected' : '' ?>>
            Ngày tạo
          </option>
          <option value="salary_min" <?= ($_GET['sortKey'] ?? '') === 'salary_min' ? 'selected' : '' ?>>
            Lương
          </option>
        </select>
      </div>

      <div class="filter-group">
        <select name="sortValue" class="form-control">
          <option value="desc" <?= ($_GET['sortValue'] ?? 'desc') === 'desc' ? 'selected' : '' ?>>
            Giảm dần
          </option>
          <option value="asc" <?= ($_GET['sortValue'] ?? '') === 'asc' ? 'selected' : '' ?>>
            Tăng dần
          </option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Lọc</button>
      <a href="<?= BASE_PATH ?>/jobs" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i> Xóa lọc</a>
    </form>
  </div>

  <?php if (!empty($jobs)): ?>
  <div class="jobs-list">
    <?php foreach ($jobs as $job): ?>
    <?php
      $logoSource = $job->company_logo ?? ($job->thumbnail ?? '');
      $logoUrl = $buildAssetUrl($logoSource);
    ?>
    <div class="job-item">
      <div class="job-thumbnail job-logo">
        <?php if ($logoUrl !== ''): ?>
        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($job->company_name ?: $job->title) ?>" loading="lazy">
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

        <?php if (!empty($job->skills)): ?>
        <div class="job-skills">
          <?php foreach ($job->skills as $skill): ?>
          <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

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

  <?php if ($pagination['totalPage'] > 1): ?>
  <div class="pagination">
    <?php if ($pagination['page'] > 1): ?>
    <a href="<?= $buildPageUrl($pagination['page'] - 1) ?>" class="btn">« Trước</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
    <a href="<?= $buildPageUrl($i) ?>" class="btn <?= $i === $pagination['page'] ? 'active' : '' ?>">
      <?= $i ?>
    </a>
    <?php endfor; ?>

    <?php if ($pagination['page'] < $pagination['totalPage']): ?>
    <a href="<?= $buildPageUrl($pagination['page'] + 1) ?>" class="btn">Sau »</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php else: ?>
  <div class="no-results">
    <i class="fa-solid fa-magnifying-glass" style="font-size: 48px; color: var(--gray-300); margin-bottom: 16px;"></i>
    <p>Không tìm thấy công việc nào phù hợp.</p>
    <a href="<?= BASE_PATH ?>/jobs" class="btn btn-primary">Xem tất cả công việc</a>
  </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

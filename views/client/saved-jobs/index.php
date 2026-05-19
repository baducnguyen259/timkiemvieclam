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

// Định dạng khoảng lương thành chuỗi hiển thị thân thiện cho việc đã lưu.
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

<div class="saved-jobs-page">
  <h1><i class="fa-solid fa-bookmark"></i> Công việc đã lưu</h1>

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

          <span class="salary">
            <i class="fa-solid fa-money-bill-wave"></i>
            <?= htmlspecialchars($formatSalary($job->salary_min ?? 0, $job->salary_max ?? 0)) ?>
          </span>
        </div>
      </div>

      <div class="job-actions">
        <a href="<?= BASE_PATH ?>/jobs/detail/<?= htmlspecialchars($job->slug) ?>" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-eye"></i> Xem chi tiết
        </a>

        <button class="btn btn-danger btn-sm btn-remove" data-job-id="<?= $job->id ?>">
          <i class="fa-solid fa-trash"></i> Xóa
        </button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="no-results">
    <i class="fa-solid fa-bookmark" style="font-size: 48px; color: var(--gray-300); margin-bottom: 16px;"></i>
    <p>Bạn chưa lưu công việc nào.</p>
    <a href="<?= BASE_PATH ?>/jobs" class="btn btn-primary">Tìm việc làm</a>
  </div>
  <?php endif; ?>
</div>

<script>
document.querySelectorAll('.btn-remove').forEach(btn => {
  btn.addEventListener('click', async function() {
    if (!confirm('Bạn có chắc muốn xóa công việc này?')) return;

    const jobId = this.dataset.jobId;
    const basePath = '<?= BASE_PATH ?>';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    try {
      const response = await fetch(`${basePath}/saved-jobs/remove/${jobId}`, {
        method: 'POST',
        headers: {
          'X-CSRF-Token': csrfToken
        }
      });
      const data = await response.json();

      if (data.success) {
        location.reload();
      } else {
        alert(data.message);
      }
    } catch (error) {
      console.error(error);
      alert('Có lỗi xảy ra');
    }
  });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

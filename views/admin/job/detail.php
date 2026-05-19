<?php ob_start(); ?>
<?php
// Định dạng khoảng lương trong trang chi tiết tin tuyển dụng của admin.
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

<div class="admin-page">
  <div class="page-header">
    <h1><?= htmlspecialchars($title) ?></h1>
    <div class="page-actions">
      <a href="<?= BASE_PATH ?>/admin/job" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        Quay lại
      </a>
    </div>
  </div>

  <div class="job-detail-admin">
    <div class="detail-card">
      <h2><?= htmlspecialchars($job->title) ?></h2>

      <div class="detail-grid">
        <div class="detail-item">
          <label>Trạng thái:</label>
          <span class="badge badge-<?= $job->status ?>">
            <?= $job->status === 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
          </span>
        </div>

        <div class="detail-item">
          <label>Danh mục:</label>
          <span><?= htmlspecialchars($category->title ?? 'Chưa cập nhật') ?></span>
        </div>

        <div class="detail-item">
          <label>Địa điểm:</label>
          <span><?= htmlspecialchars($job->location ?? 'Chưa cập nhật') ?></span>
        </div>

        <div class="detail-item">
          <label>Loại hình:</label>
          <span><?= htmlspecialchars(JobType::label($job->type ?? '')) ?></span>
        </div>

        <div class="detail-item">
          <label>Lương:</label>
          <span>
            <?= htmlspecialchars($formatSalary($job->salary_min ?? 0, $job->salary_max ?? 0)) ?>
          </span>
        </div>

        <div class="detail-item">
          <label>Kinh nghiệm:</label>
          <span><?= htmlspecialchars($job->experience ?? 'Chưa cập nhật') ?></span>
        </div>

        <div class="detail-item">
          <label>Nổi bật:</label>
          <span><?= $job->featured === '1' ? 'Có' : 'Không' ?></span>
        </div>

        <div class="detail-item">
          <label>Vị trí:</label>
          <span><?= $job->position ?></span>
        </div>

        <div class="detail-item">
          <label>Người tạo:</label>
          <span><?= htmlspecialchars($job->creator->full_name ?? 'Chưa cập nhật') ?></span>
        </div>

        <div class="detail-item">
          <label>Ngày tạo:</label>
          <span><?= date('d/m/Y H:i', strtotime($job->created_at)) ?></span>
        </div>
      </div>

      <?php if (!empty($job->skills)): ?>
      <div class="detail-section">
        <h3>Kỹ năng yêu cầu</h3>
        <div class="skills-list">
          <?php foreach ($job->skills as $skill): ?>
          <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="detail-section">
        <h3>Mô tả công việc</h3>
        <div class="description-content">
          <?= nl2br(htmlspecialchars($job->description ?? 'Không có mô tả')) ?>
        </div>
      </div>

      <?php if ($job->thumbnail): ?>
      <div class="detail-section">
        <h3>Hình ảnh</h3>
        <img src="<?= htmlspecialchars($job->thumbnail) ?>" alt="Ảnh thu nhỏ" style="max-width: 400px;">
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<style>
.detail-card {
  background: white;
  border-radius: 8px;
  padding: 30px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin: 20px 0;
}

.detail-item label {
  font-weight: 600;
  display: block;
  margin-bottom: 5px;
  color: #666;
}

.detail-section {
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

.detail-section h3 {
  margin-bottom: 15px;
}

.skills-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.skill-tag {
  background: #e0e7ff;
  color: #3730a3;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 14px;
}

.description-content {
  line-height: 1.8;
  color: #333;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

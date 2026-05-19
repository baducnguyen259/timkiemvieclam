<?php ob_start(); ?>
<?php
// Định dạng khoảng lương trong trang chi tiết tin tuyển dụng của nhà tuyển dụng.
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
      <a href="<?= BASE_PATH ?>/employer/job" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        Quay lại
      </a>
      <a href="<?= BASE_PATH ?>/employer/job/edit/<?= $job->id ?>" class="btn btn-primary">
        <i class="fa-solid fa-pen-to-square"></i>
        Chỉnh sửa
      </a>
    </div>
  </div>

  <div class="detail-card">
    <div class="detail-row">
      <div class="detail-label">Tên công việc</div>
      <div class="detail-value"><strong><?= htmlspecialchars($job->title) ?></strong></div>
    </div>

    <div class="detail-row">
      <div class="detail-label">Tên công ty</div>
      <div class="detail-value"><?= htmlspecialchars($job->company_name ?? 'Chưa cập nhật') ?></div>
    </div>

    <div class="detail-row">
      <div class="detail-label">Logo công ty</div>
      <div class="detail-value">
        <?php if (!empty($job->company_logo)): ?>
        <img src="<?= BASE_PATH . '/' . htmlspecialchars($job->company_logo) ?>" alt="Logo công ty" style="max-height:70px;">
        <?php else: ?>
        Chưa cập nhật
        <?php endif; ?>
      </div>
    </div>

    <div class="detail-row">
      <div class="detail-label">Trạng thái</div>
      <div class="detail-value">
        <span class="badge badge-<?= $job->status ?>">
          <?= $job->status === 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
        </span>
      </div>
    </div>

    <div class="detail-row">
      <div class="detail-label">Danh mục</div>
      <div class="detail-value"><?= htmlspecialchars($category->title ?? 'Chưa cập nhật') ?></div>
    </div>

    <div class="detail-row">
      <div class="detail-label">Thành phố</div>
      <div class="detail-value"><?= htmlspecialchars($job->location ?? 'Chưa cập nhật') ?></div>
    </div>

    <div class="detail-row">
      <div class="detail-label">Địa chỉ chi tiết</div>
      <div class="detail-value"><?= htmlspecialchars($job->address_detail ?? 'Chưa cập nhật') ?></div>
    </div>

    <div class="detail-row">
      <div class="detail-label">Hạn nộp hồ sơ</div>
      <div class="detail-value">
        <?= !empty($job->application_deadline) ? date('d/m/Y', strtotime($job->application_deadline)) : 'Chưa cập nhật' ?>
      </div>
    </div>

    <div class="detail-row">
      <div class="detail-label">Loại hình</div>
      <div class="detail-value"><?= htmlspecialchars(JobType::label($job->type ?? '')) ?></div>
    </div>

    <div class="detail-row">
      <div class="detail-label">Lương</div>
      <div class="detail-value">
        <?= htmlspecialchars($formatSalary($job->salary_min ?? 0, $job->salary_max ?? 0)) ?>
      </div>
    </div>

    <div class="detail-row">
      <div class="detail-label">Kinh nghiệm</div>
      <div class="detail-value"><?= htmlspecialchars($job->experience ?? 'Chưa cập nhật') ?></div>
    </div>

    <div class="detail-row">
      <div class="detail-label">Ngày tạo</div>
      <div class="detail-value"><?= date('d/m/Y H:i', strtotime($job->created_at)) ?></div>
    </div>

    <?php if (!empty($job->skills)): ?>
    <div class="detail-row">
      <div class="detail-label">Kỹ năng</div>
      <div class="detail-value">
        <?php foreach ($job->skills as $skill): ?>
        <span class="badge badge-featured"><?= htmlspecialchars($skill) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="detail-row">
      <div class="detail-label">Yêu cầu ứng viên</div>
      <div class="detail-value"><?= nl2br(htmlspecialchars($job->candidate_requirements ?? 'Không có')) ?></div>
    </div>

    <div class="detail-row">
      <div class="detail-label">Quyền lợi</div>
      <div class="detail-value"><?= nl2br(htmlspecialchars($job->benefits ?? 'Không có')) ?></div>
    </div>

    <div class="detail-row">
      <div class="detail-label">Mô tả</div>
      <div class="detail-value"><?= nl2br(htmlspecialchars($job->description ?? 'Không có mô tả')) ?></div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

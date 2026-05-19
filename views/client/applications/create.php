<?php ob_start(); ?>
<?php
// Định dạng khoảng lương của tin tuyển dụng đang được ứng tuyển.
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

<div class="application-create-page">
  <h1>Ứng tuyển: <?= htmlspecialchars($job->title) ?></h1>

  <div class="job-summary">
    <p><strong>Công ty:</strong> <?= htmlspecialchars($job->company_name ?? 'Chưa cập nhật') ?></p>
    <p><strong>Địa điểm:</strong> <?= htmlspecialchars($job->location ?? 'Chưa cập nhật') ?></p>
    <p><strong>Lương:</strong> <?= htmlspecialchars($formatSalary($job->salary_min ?? 0, $job->salary_max ?? 0)) ?></p>
  </div>

  <form method="POST" action="<?= BASE_PATH ?>/applications/create/<?= $job->id ?>" enctype="multipart/form-data"
    class="application-form">
      <?= csrf_field() ?>

    <div class="form-group">
      <label for="fullName">Họ và tên <span class="required">*</span></label>
      <input type="text" id="fullName" name="fullName" class="form-control"
        value="<?= htmlspecialchars($GLOBALS['current_user']->full_name ?? '') ?>" required>
    </div>

    <div class="form-group">
      <label for="email">Email <span class="required">*</span></label>
      <input type="email" id="email" name="email" class="form-control"
        value="<?= htmlspecialchars($GLOBALS['current_user']->email ?? '') ?>" required>
    </div>

    <div class="form-group">
      <label for="phone">Số điện thoại <span class="required">*</span></label>
      <input type="tel" id="phone" name="phone" class="form-control"
        value="<?= htmlspecialchars($GLOBALS['current_user']->phone ?? '') ?>" required>
    </div>

    <div class="form-group">
      <label for="cvLink">Dán liên kết CV</label>
      <input type="url" id="cvLink" name="cvLink" class="form-control" placeholder="Ví dụ: https://drive.google.com/...">
      <small>Bạn có thể dán liên kết CV hoặc tải tệp CV lên.</small>
    </div>

    <div class="form-group">
      <label for="cvFile">Tải CV lên (PDF, DOC, DOCX - tối đa 5 MB)</label>
      <input type="file" id="cvFile" name="cvFile" class="form-control" accept=".pdf,.doc,.docx">
    </div>

    <div class="form-group">
      <label for="coverLetter">Thư xin việc</label>
      <textarea id="coverLetter" name="coverLetter" class="form-control" rows="6"
        placeholder="Giới thiệu bản thân và lý do ứng tuyển..."></textarea>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Ứng tuyển ngay</button>
      <a href="<?= BASE_PATH ?>/jobs/detail/<?= htmlspecialchars($job->slug) ?>" class="btn btn-secondary">Hủy</a>
    </div>
  </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

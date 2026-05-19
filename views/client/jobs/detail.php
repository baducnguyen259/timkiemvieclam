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

$companyLogoUrl = $buildAssetUrl($job->company_logo ?? ($job->thumbnail ?? ''));
$bannerUrl = $buildAssetUrl($job->thumbnail ?? '');

// Định dạng khoảng lương thành chuỗi hiển thị trong trang chi tiết việc làm.
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

$salaryText = $formatSalary($job->salary_min ?? 0, $job->salary_max ?? 0);
?>

<div class="job-detail-page">
  <div class="container">
    <div class="jobd-hero">
      <div class="jobd-hero-main">
        <div class="jobd-company">
          <div class="jobd-logo <?= $companyLogoUrl === '' ? 'logo-fallback' : '' ?>">
            <?php if ($companyLogoUrl !== ''): ?>
            <img src="<?= htmlspecialchars($companyLogoUrl) ?>" alt="<?= htmlspecialchars($job->company_name ?: $job->title) ?>" loading="lazy"
              onerror="this.style.display='none'; this.parentElement.classList.add('logo-fallback');">
            <?php else: ?>
            <i class="fa-solid fa-building"></i>
            <?php endif; ?>
          </div>
          <div class="jobd-company-text">
            <p class="jobd-company-name"><?= htmlspecialchars($job->company_name ?: 'Nhà tuyển dụng') ?></p>
            <p class="jobd-company-location">
              <i class="fa-solid fa-location-dot"></i>
              <?= htmlspecialchars($job->location ?? 'Không xác định') ?>
              <?php if (!empty($job->address_detail)): ?>
              <span class="jobd-separator">•</span>
              <?= htmlspecialchars($job->address_detail) ?>
              <?php endif; ?>
            </p>
          </div>
        </div>

        <h1 class="jobd-title"><?= htmlspecialchars($job->title) ?></h1>

        <div class="jobd-meta">
          <span class="jobd-chip"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($job->location ?? 'Không xác định') ?></span>
          <span class="jobd-chip"><i class="fa-solid fa-briefcase"></i> <?= htmlspecialchars(JobType::label($job->type ?? 'Full-time')) ?></span>
          <span class="jobd-chip salary"><i class="fa-solid fa-money-bill-wave"></i> <?= htmlspecialchars($salaryText) ?></span>
          <?php if (!empty($job->experience)): ?>
          <span class="jobd-chip"><i class="fa-solid fa-star"></i> <?= htmlspecialchars($job->experience) ?></span>
          <?php endif; ?>
          <?php if (!empty($job->application_deadline)): ?>
          <span class="jobd-chip"><i class="fa-regular fa-calendar-days"></i> Hạn nộp: <?= date('d/m/Y', strtotime($job->application_deadline)) ?></span>
          <?php endif; ?>
        </div>

        <?php if ($category): ?>
        <a class="jobd-category" href="<?= BASE_PATH ?>/jobs/<?= htmlspecialchars($category->slug) ?>">
          <i class="fa-solid fa-folder-open"></i> <?= htmlspecialchars($category->title) ?>
        </a>
        <?php endif; ?>
      </div>

      <div class="jobd-actions">
        <?php if (isset($GLOBALS['current_user'])): ?>
        <?php if (!empty($job->applied)): ?>
        <a href="<?= BASE_PATH ?>/applications" class="btn btn-primary btn-large">
          <i class="fa-solid fa-circle-check"></i> Đã ứng tuyển
        </a>
        <?php else: ?>
        <a href="#apply-now" class="btn btn-primary btn-large">
          <i class="fa-solid fa-paper-plane"></i> Ứng tuyển ngay
        </a>
        <?php endif; ?>
        <?php else: ?>
        <a href="<?= BASE_PATH ?>/user/login" class="btn btn-primary btn-large">
          <i class="fa-solid fa-right-to-bracket"></i> Đăng nhập để ứng tuyển
        </a>
        <?php endif; ?>

        <button class="btn btn-secondary btn-save-job" data-job-id="<?= $job->id ?>"
          data-saved="<?= !empty($job->saved_job) ? 'true' : 'false' ?>">
          <i class="fa-<?= !empty($job->saved_job) ? 'solid' : 'regular' ?> fa-heart"></i>
          <?= !empty($job->saved_job) ? 'Đã lưu tin ứng tuyển' : 'Lưu tin ứng tuyển' ?>
        </button>
      </div>
    </div>

    <?php if ($bannerUrl !== ''): ?>
    <div class="jobd-banner">
      <img src="<?= htmlspecialchars($bannerUrl) ?>" alt="<?= htmlspecialchars($job->title) ?>" loading="lazy">
    </div>
    <?php endif; ?>

    <div class="jobd-layout">
      <div class="jobd-main">
        <section class="jobd-card">
          <h2><i class="fa-solid fa-file-lines"></i> Mô tả công việc</h2>
          <div class="jobd-content">
            <?= nl2br(htmlspecialchars($job->description ?? 'Nhà tuyển dụng chưa cập nhật mô tả.')) ?>
          </div>
        </section>

        <section class="jobd-card">
          <h2><i class="fa-solid fa-list-check"></i> Yêu cầu ứng viên</h2>
          <div class="jobd-content">
            <?= nl2br(htmlspecialchars($job->candidate_requirements ?? 'Nhà tuyển dụng chưa cập nhật yêu cầu.')) ?>
          </div>
        </section>

        <section class="jobd-card">
          <h2><i class="fa-solid fa-gift"></i> Quyền lợi</h2>
          <div class="jobd-content">
            <?= nl2br(htmlspecialchars($job->benefits ?? 'Nhà tuyển dụng chưa cập nhật quyền lợi.')) ?>
          </div>
        </section>

        <?php if (!empty($job->skills)): ?>
        <section class="jobd-card">
          <h2><i class="fa-solid fa-code"></i> Kỹ năng yêu cầu</h2>
          <div class="jobd-skill-list">
            <?php foreach ($job->skills as $skill): ?>
            <span class="jobd-skill"><?= htmlspecialchars($skill) ?></span>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <section class="jobd-card">
          <h2><i class="fa-solid fa-circle-info"></i> Thông tin tuyển dụng</h2>
          <table class="jobd-table">
            <tr>
              <th>Công ty</th>
              <td><?= htmlspecialchars($job->company_name ?? 'Chưa cập nhật') ?></td>
            </tr>
            <tr>
              <th>Thành phố</th>
              <td><?= htmlspecialchars($job->location ?? 'Không xác định') ?></td>
            </tr>
            <tr>
              <th>Địa chỉ</th>
              <td><?= htmlspecialchars($job->address_detail ?? 'Chưa cập nhật') ?></td>
            </tr>
            <tr>
              <th>Loại hình</th>
              <td><?= htmlspecialchars(JobType::label($job->type ?? 'Full-time')) ?></td>
            </tr>
            <tr>
              <th>Mức lương</th>
              <td><?= htmlspecialchars($salaryText) ?></td>
            </tr>
            <tr>
              <th>Kinh nghiệm</th>
              <td><?= htmlspecialchars($job->experience ?? 'Không yêu cầu') ?></td>
            </tr>
            <?php if (!empty($job->application_deadline)): ?>
            <tr>
              <th>Hạn nộp</th>
              <td><?= date('d/m/Y', strtotime($job->application_deadline)) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($category): ?>
            <tr>
              <th>Danh mục</th>
              <td><?= htmlspecialchars($category->title) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
              <th>Ngày đăng</th>
              <td><?= date('d/m/Y', strtotime($job->created_at)) ?></td>
            </tr>
          </table>
        </section>

        <section class="jobd-card jobd-apply-card" id="apply-now">
          <h2><i class="fa-solid fa-paper-plane"></i> Ứng tuyển công việc</h2>

          <?php if (!isset($GLOBALS['current_user'])): ?>
          <p class="jobd-note">Bạn cần đăng nhập để ứng tuyển công việc này.</p>
          <a href="<?= BASE_PATH ?>/user/login" class="btn btn-primary">
            <i class="fa-solid fa-right-to-bracket"></i> Đăng nhập ngay
          </a>
          <?php elseif (!empty($job->applied)): ?>
          <p class="jobd-note-success">
            <i class="fa-solid fa-circle-check"></i> Bạn đã ứng tuyển công việc này.
          </p>
          <a href="<?= BASE_PATH ?>/applications" class="btn btn-secondary">
            <i class="fa-solid fa-file-lines"></i> Xem đơn ứng tuyển của tôi
          </a>
          <?php else: ?>
          <form method="POST" action="<?= BASE_PATH ?>/applications/create/<?= $job->id ?>" enctype="multipart/form-data"
            class="application-form inline-application-form">
              <?= csrf_field() ?>

            <div class="jobd-form-grid">
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
            </div>

            <div class="form-group">
                <label for="cvLink">Dán liên kết CV</label>
              <input type="url" id="cvLink" name="cvLink" class="form-control"
                placeholder="Ví dụ: https://drive.google.com/...">
              <small>Bạn có thể dán liên kết CV hoặc tải tệp CV bên dưới.</small>
            </div>

            <div class="form-group">
              <label for="cvFile">Tải CV lên (PDF, DOC, DOCX - tối đa 5 MB)</label>
              <input type="file" id="cvFile" name="cvFile" class="form-control" accept=".pdf,.doc,.docx">
            </div>

            <div class="form-group">
              <label for="coverLetter">Thư xin việc</label>
              <textarea id="coverLetter" name="coverLetter" class="form-control" rows="6"
                placeholder="Giới thiệu ngắn gọn về kinh nghiệm và điểm mạnh của bạn..."></textarea>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Ứng tuyển ngay
              </button>
              <button type="button" class="btn btn-secondary btn-save-job" data-job-id="<?= $job->id ?>"
                data-saved="<?= !empty($job->saved_job) ? 'true' : 'false' ?>">
                <i class="fa-<?= !empty($job->saved_job) ? 'solid' : 'regular' ?> fa-heart"></i>
                <?= !empty($job->saved_job) ? 'Đã lưu tin ứng tuyển' : 'Lưu tin ứng tuyển' ?>
              </button>
            </div>
          </form>
          <?php endif; ?>
        </section>
      </div>

      <aside class="jobd-sidebar">
        <div class="jobd-side-card">
          <h3>Thông tin nhanh</h3>
          <ul class="jobd-side-list">
            <li><span>Mức lương</span><strong><?= htmlspecialchars($salaryText) ?></strong></li>
            <li><span>Loại hình</span><strong><?= htmlspecialchars(JobType::label($job->type ?? 'Full-time')) ?></strong></li>
            <li><span>Kinh nghiệm</span><strong><?= htmlspecialchars($job->experience ?? 'Không yêu cầu') ?></strong></li>
            <li><span>Địa điểm</span><strong><?= htmlspecialchars($job->location ?? 'Không xác định') ?></strong></li>
            <?php if (!empty($job->application_deadline)): ?>
            <li><span>Hạn nộp</span><strong><?= date('d/m/Y', strtotime($job->application_deadline)) ?></strong></li>
            <?php endif; ?>
            <li><span>Ngày đăng</span><strong><?= date('d/m/Y', strtotime($job->created_at)) ?></strong></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</div>

<script>
const saveJobButtons = document.querySelectorAll('.btn-save-job');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

saveJobButtons.forEach((button) => {
  button.addEventListener('click', async function() {
    const jobId = this.dataset.jobId;
    const isSaved = this.dataset.saved === 'true';
    const basePath = '<?= BASE_PATH ?>';
    const url = isSaved ? `${basePath}/saved-jobs/remove/${jobId}` : `${basePath}/saved-jobs/add/${jobId}`;

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-Token': csrfToken
        }
      });
      const data = await response.json();

      if (data.success) {
        const newSavedState = isSaved ? 'false' : 'true';
        const newButtonHtml = isSaved ?
          '<i class="fa-regular fa-heart"></i> Lưu tin ứng tuyển' :
          '<i class="fa-solid fa-heart"></i> Đã lưu tin ứng tuyển';

        saveJobButtons.forEach((btn) => {
          btn.dataset.saved = newSavedState;
          btn.innerHTML = newButtonHtml;
        });
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

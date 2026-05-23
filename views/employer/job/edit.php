<?php ob_start(); ?>

<div class="admin-page">
  <div class="page-header">
    <h1><?= htmlspecialchars($title) ?></h1>
    <div class="page-actions">
      <a href="<?= BASE_PATH ?>/employer/job" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        Quay lại
      </a>
    </div>
  </div>

  <form method="POST" action="<?= BASE_PATH ?>/employer/job/edit/<?= $job->id ?>" class="admin-form" enctype="multipart/form-data">
      <?= csrf_field() ?>
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="title">Tên công việc <span class="required">*</span></label>
        <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($job->title) ?>" required>
      </div>

      <div class="form-group col-md-6">
        <label for="company_name">Tên công ty <span class="required">*</span></label>
        <input type="text" id="company_name" name="company_name" class="form-control"
          value="<?= htmlspecialchars($job->company_name ?? '') ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-md-3">
        <label for="location">Thành phố <span class="required">*</span></label>
        <?php $cityOptions = ['Hà Nội', 'TP.HCM', 'Đà Nẵng']; ?>
        <select id="location" name="location" class="form-control" required>
          <option value="">Chọn thành phố</option>
          <?php foreach ($cityOptions as $city): ?>
          <option value="<?= $city ?>" <?= ($job->location ?? '') === $city ? 'selected' : '' ?>><?= $city ?></option>
          <?php endforeach; ?>
          <?php if (!empty($job->location) && !in_array($job->location, $cityOptions, true)): ?>
          <option value="<?= htmlspecialchars($job->location) ?>" selected><?= htmlspecialchars($job->location) ?></option>
          <?php endif; ?>
        </select>
      </div>

      <div class="form-group col-md-3">
        <label for="application_deadline">Hạn nộp hồ sơ <span class="required">*</span></label>
        <input type="date" id="application_deadline" name="application_deadline" class="form-control"
          value="<?= !empty($job->application_deadline) ? date('Y-m-d', strtotime($job->application_deadline)) : '' ?>" required>
      </div>

      <div class="form-group col-md-3">
        <label for="status">Trạng thái</label>
        <select id="status" name="status" class="form-control">
          <option value="active" <?= $job->status === 'active' ? 'selected' : '' ?>>Hoạt động</option>
          <option value="inactive" <?= $job->status === 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
        </select>
      </div>

      <div class="form-group col-md-3">
        <label for="position">Vị trí</label>
        <input type="number" id="position" name="position" class="form-control" value="<?= $job->position ?>" min="0">
      </div>
    </div>

    <div class="form-group">
      <label for="address_detail">Địa chỉ chi tiết <span class="required">*</span></label>
      <input type="text" id="address_detail" name="address_detail" class="form-control"
        value="<?= htmlspecialchars($job->address_detail ?? '') ?>" placeholder="VD: 123 Nguyễn Văn Linh, Quận 7" required>
    </div>

    <div class="form-group">
      <label for="company_logo">Tải logo công ty lên</label>
      <input type="file" id="company_logo" name="company_logo" class="form-control" accept=".jpg,.jpeg,.jfif,.png,.gif,.webp,.avif,.bmp,.ico,image/*">
      <?php if (!empty($job->company_logo)): ?>
      <div style="margin-top:8px;">
        <img src="<?= BASE_PATH . '/' . htmlspecialchars($job->company_logo) ?>" alt="Logo công ty" style="max-height:60px;">
      </div>
      <?php endif; ?>
      <small>Để trống nếu không muốn thay đổi logo.</small>
    </div>

    <div class="form-group">
      <label for="description">Mô tả</label>
      <textarea id="description" name="description" class="form-control"
        rows="6"><?= htmlspecialchars($job->description ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label for="candidate_requirements">Yêu cầu ứng viên</label>
      <textarea id="candidate_requirements" name="candidate_requirements" class="form-control"
        rows="5"><?= htmlspecialchars($job->candidate_requirements ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label for="benefits">Quyền lợi</label>
      <textarea id="benefits" name="benefits" class="form-control"
        rows="5"><?= htmlspecialchars($job->benefits ?? '') ?></textarea>
    </div>

    <div class="form-row">
      <div class="form-group col-md-12">
        <label for="category_id">Danh mục</label>
        <select id="category_id" name="category_id" class="form-control">
          <option value="">Chọn danh mục</option>
          <?php
          // Render option danh mục theo cây và đánh dấu danh mục hiện tại của tin tuyển dụng.
          function renderEmployerCategoryOptionsEdit($categories, $selectedId, $level = 0) {
              foreach ($categories as $cat) {
                  $selected = $cat->id == $selectedId ? 'selected' : '';
                  echo '<option value="' . $cat->id . '" ' . $selected . '>'
                      . str_repeat('&nbsp;&nbsp;', $level)
                      . htmlspecialchars($cat->title)
                      . '</option>';
                  if (!empty($cat->children)) {
                      renderEmployerCategoryOptionsEdit($cat->children, $selectedId, $level + 1);
                  }
              }
          }
          renderEmployerCategoryOptionsEdit($categoryTree ?? [], $job->category_id);
          ?>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="salary">Mức lương</label>
        <?php $salaryValue = ($job->salary_min ?? 0) > 0 ? $job->salary_min : ($job->salary_max ?? ''); ?>
        <input type="number" id="salary" name="salary" class="form-control" value="<?= htmlspecialchars((string)$salaryValue) ?>" min="0">
      </div>

      <div class="form-group col-md-6">
        <label for="type">Loại hình</label>
        <select id="type" name="type" class="form-control">
          <option value="">Chọn loại hình</option>
          <option value="Full-time" <?= $job->type === 'Full-time' ? 'selected' : '' ?>>Toàn thời gian</option>
          <option value="Part-time" <?= $job->type === 'Part-time' ? 'selected' : '' ?>>Bán thời gian</option>
          <option value="Contract" <?= $job->type === 'Contract' ? 'selected' : '' ?>>Hợp đồng</option>
          <option value="Internship" <?= $job->type === 'Internship' ? 'selected' : '' ?>>Thực tập</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-md-12">
        <label for="experience">Kinh nghiệm</label>
        <input type="text" id="experience" name="experience" class="form-control"
          value="<?= htmlspecialchars($job->experience ?? '') ?>" placeholder="VD: 1-2 năm">
      </div>
    </div>

    <div class="form-group">
      <label for="skill">Kỹ năng (tối đa 5, mỗi kỹ năng tối đa 20 ký tự)</label>
      <input type="text" id="skill" name="skill" class="form-control"
        value="<?= htmlspecialchars(implode(', ', $job->skills ?? [])) ?>">
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Cập nhật</button>
      <a href="<?= BASE_PATH ?>/employer/job" class="btn btn-secondary">Hủy</a>
    </div>
  </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

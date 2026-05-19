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

  <form method="POST" action="<?= BASE_PATH ?>/employer/job/create" class="admin-form" enctype="multipart/form-data">
      <?= csrf_field() ?>
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="title">Tên công việc <span class="required">*</span></label>
        <input type="text" id="title" name="title" class="form-control" required>
      </div>

      <div class="form-group col-md-6">
        <label for="company_name">Tên công ty <span class="required">*</span></label>
        <input type="text" id="company_name" name="company_name" class="form-control" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-md-3">
        <label for="location">Thành phố <span class="required">*</span></label>
        <select id="location" name="location" class="form-control" required>
          <option value="">Chọn thành phố</option>
          <option value="Hà Nội">Hà Nội</option>
          <option value="TP.HCM">TP.HCM</option>
          <option value="Đà Nẵng">Đà Nẵng</option>
        </select>
      </div>

      <div class="form-group col-md-3">
        <label for="application_deadline">Hạn nộp hồ sơ <span class="required">*</span></label>
        <input type="date" id="application_deadline" name="application_deadline" class="form-control" required>
      </div>

      <div class="form-group col-md-3">
        <label for="status">Trạng thái</label>
        <select id="status" name="status" class="form-control">
          <option value="active">Hoạt động</option>
          <option value="inactive">Không hoạt động</option>
        </select>
      </div>

      <div class="form-group col-md-3">
        <label for="position">Vị trí</label>
        <input type="number" id="position" name="position" class="form-control" min="0">
      </div>
    </div>

    <div class="form-group">
      <label for="address_detail">Địa chỉ chi tiết <span class="required">*</span></label>
      <input type="text" id="address_detail" name="address_detail" class="form-control"
        placeholder="VD: 123 Nguyễn Văn Linh, Quận 7" required>
    </div>

    <div class="form-group">
      <label for="company_logo">Tải logo công ty lên</label>
      <input type="file" id="company_logo" name="company_logo" class="form-control" accept=".jpg,.jpeg,.png">
      <small>Định dạng: JPG, JPEG, PNG. Tối đa 5MB.</small>
    </div>

    <div class="form-group">
      <label for="description">Mô tả</label>
      <textarea id="description" name="description" class="form-control" rows="6"></textarea>
    </div>

    <div class="form-group">
      <label for="candidate_requirements">Yêu cầu ứng viên</label>
      <textarea id="candidate_requirements" name="candidate_requirements" class="form-control" rows="5"></textarea>
    </div>

    <div class="form-group">
      <label for="benefits">Quyền lợi</label>
      <textarea id="benefits" name="benefits" class="form-control" rows="5"></textarea>
    </div>

    <div class="form-row">
      <div class="form-group col-md-12">
        <label for="category_id">Danh mục</label>
        <select id="category_id" name="category_id" class="form-control">
          <option value="">Chọn danh mục</option>
          <?php
          // Render option danh mục theo cây cho form đăng tin của nhà tuyển dụng.
          function renderEmployerCategoryOptions($categories, $level = 0) {
              foreach ($categories as $cat) {
                  echo '<option value="' . $cat->id . '">'
                      . str_repeat('&nbsp;&nbsp;', $level)
                      . htmlspecialchars($cat->title)
                      . '</option>';
                  if (!empty($cat->children)) {
                      renderEmployerCategoryOptions($cat->children, $level + 1);
                  }
              }
          }
          renderEmployerCategoryOptions($categoryTree ?? []);
          ?>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="salary">Mức lương</label>
        <input type="number" id="salary" name="salary" class="form-control" min="0" placeholder="VD: 15000000">
      </div>

      <div class="form-group col-md-6">
        <label for="type">Loại hình</label>
        <select id="type" name="type" class="form-control">
          <option value="">Chọn loại hình</option>
          <option value="Full-time">Toàn thời gian</option>
          <option value="Part-time">Bán thời gian</option>
          <option value="Contract">Hợp đồng</option>
          <option value="Internship">Thực tập</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-md-12">
        <label for="experience">Kinh nghiệm</label>
        <input type="text" id="experience" name="experience" class="form-control" placeholder="VD: 1-2 năm">
      </div>
    </div>

    <div class="form-group">
      <label for="skill">Kỹ năng (tối đa 5, cách nhau bởi dấu phẩy)</label>
      <input type="text" id="skill" name="skill" class="form-control" placeholder="VD: PHP, MySQL, JavaScript">
      <small>Mỗi kỹ năng tối đa 20 ký tự</small>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Đăng tin tuyển dụng</button>
      <a href="<?= BASE_PATH ?>/employer/job" class="btn btn-secondary">Hủy</a>
    </div>
  </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

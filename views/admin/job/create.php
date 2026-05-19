<?php ob_start(); ?>

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

  <form method="POST" action="<?= BASE_PATH ?>/admin/job/create" class="admin-form">
      <?= csrf_field() ?>
    <div class="form-row">
      <div class="form-group col-md-8">
        <label for="title">Tiêu đề <span class="required">*</span></label>
        <input type="text" id="title" name="title" class="form-control" required>
      </div>

      <div class="form-group col-md-4">
        <label for="status">Trạng thái</label>
        <select id="status" name="status" class="form-control">
          <option value="active">Hoạt động</option>
          <option value="inactive">Không hoạt động</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="description">Mô tả</label>
      <textarea id="description" name="description" class="form-control" rows="6"></textarea>
    </div>

    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="location">Địa điểm</label>
        <select id="location" name="location" class="form-control">
          <option value="">Chọn địa điểm</option>
          <option value="Hà Nội">Hà Nội</option>
          <option value="TP.HCM">TP.HCM</option>
          <option value="Đà Nẵng">Đà Nẵng</option>
        </select>
      </div>

      <div class="form-group col-md-6">
        <label for="category_id">Danh mục</label>
        <select id="category_id" name="category_id" class="form-control">
          <option value="">Chọn danh mục</option>
          <?php 
                    // Render option danh mục việc làm theo cây trong form tạo tin.
                    function renderCategoryOptions($categories, $level = 0) {
                        foreach ($categories as $cat) {
                            echo '<option value="' . $cat->id . '">' 
                                 . str_repeat('&nbsp;&nbsp;', $level) 
                                 . htmlspecialchars($cat->title) 
                                 . '</option>';
                            if (!empty($cat->children)) {
                                renderCategoryOptions($cat->children, $level + 1);
                            }
                        }
                    }
                    renderCategoryOptions($categoryTree ?? []);
                    ?>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-md-4">
        <label for="salaryMin">Lương tối thiểu</label>
        <input type="number" id="salaryMin" name="salaryMin" class="form-control" min="0">
      </div>

      <div class="form-group col-md-4">
        <label for="salaryMax">Lương tối đa</label>
        <input type="number" id="salaryMax" name="salaryMax" class="form-control" min="0">
      </div>

      <div class="form-group col-md-4">
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
      <div class="form-group col-md-6">
        <label for="experience">Kinh nghiệm</label>
        <input type="text" id="experience" name="experience" class="form-control" placeholder="VD: 1-2 năm">
      </div>

      <div class="form-group col-md-6">
        <label for="featured">Nổi bật</label>
        <select id="featured" name="featured" class="form-control">
          <option value="0">Không</option>
          <option value="1">Có</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="skill">Kỹ năng (tối đa 5, mỗi kỹ năng tối đa 20 ký tự, cách nhau bởi dấu phẩy)</label>
      <input type="text" id="skill" name="skill" class="form-control" placeholder="VD: PHP, MySQL, JavaScript">
      <small>Ví dụ: PHP, MySQL, JavaScript, HTML, CSS</small>
    </div>

    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="thumbnail">URL ảnh thu nhỏ</label>
        <input type="text" id="thumbnail" name="thumbnail" class="form-control">
      </div>

      <div class="form-group col-md-6">
        <label for="position">Vị trí</label>
        <input type="number" id="position" name="position" class="form-control" min="0">
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Tạo công việc</button>
      <a href="<?= BASE_PATH ?>/admin/job" class="btn btn-secondary">Hủy</a>
    </div>
  </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

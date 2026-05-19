<?php ob_start(); ?>

<div class="admin-page">
  <div class="page-header">
    <h1><?= htmlspecialchars($title) ?></h1>
    <div class="page-actions">
      <a href="<?= BASE_PATH ?>/admin/job-category" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        Quay lại
      </a>
    </div>
  </div>

  <form method="POST" action="<?= BASE_PATH ?>/admin/job-category/create" class="admin-form">
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
      <label for="parent_id">Danh mục cha</label>
      <select id="parent_id" name="parent_id" class="form-control">
        <option value="">-- Không có (Danh mục gốc) --</option>
        <?php 
                // Render option danh mục cha theo cây để admin chọn cấp cha khi tạo danh mục.
                function renderParentOptions($categories, $level = 0) {
                    foreach ($categories as $cat) {
                        echo '<option value="' . $cat->id . '">' 
                             . str_repeat('&nbsp;&nbsp;', $level) 
                             . htmlspecialchars($cat->title) 
                             . '</option>';
                        if (!empty($cat->children)) {
                            renderParentOptions($cat->children, $level + 1);
                        }
                    }
                }
                renderParentOptions($categoryTree ?? []);
                ?>
      </select>
    </div>

    <div class="form-group">
      <label for="description">Mô tả</label>
      <textarea id="description" name="description" class="form-control" rows="4"></textarea>
    </div>

    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="thumbnail">URL ảnh thu nhỏ</label>
        <input type="text" id="thumbnail" name="thumbnail" class="form-control">
      </div>

      <div class="form-group col-md-6">
        <label for="position">Vị trí</label>
        <input type="number" id="position" name="position" class="form-control" value="0" min="0">
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Tạo danh mục</button>
      <a href="<?= BASE_PATH ?>/admin/job-category" class="btn btn-secondary">Hủy</a>
    </div>
  </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

<?php ob_start(); ?>

<div class="admin-page">
  <div class="page-header">
    <h1><?= htmlspecialchars($title) ?></h1>
    <div class="page-actions">
      <a href="<?= BASE_PATH ?>/admin/role" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        Quay lại
      </a>
    </div>
  </div>

  <form method="POST" action="<?= BASE_PATH ?>/admin/role/edit/<?= $role->id ?>" class="admin-form">
      <?= csrf_field() ?>
    <div class="form-group">
      <label for="title">Tên vai trò <span class="required">*</span></label>
      <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($role->title) ?>"
        <?= !empty($isSystemAdminRole) ? 'readonly' : '' ?> required>
      <?php if (!empty($isSystemAdminRole)): ?>
      <small>Vai trò Admin là vai trò hệ thống nên không thể đổi tên.</small>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label for="description">Mô tả</label>
      <textarea id="description" name="description" class="form-control"
        rows="3"><?= htmlspecialchars($role->description ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label>Quyền hạn</label>
      <div class="permissions-grid">
        <?php foreach ($availablePermissions as $key => $label): ?>
        <div class="permission-item">
          <label>
            <input type="checkbox" name="permissions[]" value="<?= htmlspecialchars($key) ?>"
              <?= in_array($key, $currentPermissions) ? 'checked' : '' ?>>
            <?= htmlspecialchars($label) ?>
          </label>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Cập nhật</button>
      <a href="<?= BASE_PATH ?>/admin/role" class="btn btn-secondary">Hủy</a>
    </div>
  </form>
</div>

<style>
.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 15px;
  padding: 20px;
  background: #f9fafb;
  border-radius: 8px;
}

.permission-item label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.permission-item input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.admin-form small {
  display: block;
  margin-top: 6px;
  color: #64748b;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

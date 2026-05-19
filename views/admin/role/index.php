<?php ob_start(); ?>

<div class="admin-page">
  <div class="page-header">
    <h1><?= htmlspecialchars($title) ?></h1>
  </div>

  <?php if (!empty($roles)): ?>
  <div class="roles-grid">
    <?php foreach ($roles as $role): ?>
    <?php $isSystemAdminRole = strtolower(trim((string)$role->title)) === 'admin'; ?>
    <div class="role-card">
      <div class="role-header">
        <h3><?= htmlspecialchars($role->title) ?></h3>
      </div>

      <div class="role-body">
        <?php if ($role->description): ?>
        <p class="role-description">
          <?= htmlspecialchars($role->description) ?>
        </p>
        <?php endif; ?>

        <div class="role-permissions">
          <strong>Quyền hạn:</strong>
          <?php
          $permissions = json_decode($role->permissions, true);
          $permissions = array_values(array_intersect($permissions ?? [], array_keys($availablePermissions ?? [])));
          if (!empty($permissions)):
          ?>
          <ul>
            <?php foreach ($permissions as $permission): ?>
            <li><?= htmlspecialchars($availablePermissions[$permission] ?? $permission) ?></li>
            <?php endforeach; ?>
          </ul>
          <?php else: ?>
          <p class="text-muted">Không có quyền nào</p>
          <?php endif; ?>
        </div>
      </div>

      <div class="role-footer">
        <a href="<?= BASE_PATH ?>/admin/role/edit/<?= $role->id ?>" class="btn btn-sm btn-primary">
          <i class="fa-solid fa-pen-to-square"></i> Sửa
        </a>

        <?php if (!$isSystemAdminRole): ?>
        <form method="POST" action="<?= BASE_PATH ?>/admin/role/delete/<?= $role->id ?>" style="display: inline;"
          onsubmit="return confirm('Bạn có chắc muốn xóa?')">
            <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm btn-danger">
            <i class="fa-solid fa-trash"></i> Xóa
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="no-results">
    <p>Chưa có vai trò nào.</p>
  </div>
  <?php endif; ?>
</div>

<style>
.roles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.role-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.role-header h3 {
  margin: 0 0 10px 0;
  color: #333;
}

.role-description {
  color: #666;
  margin-bottom: 15px;
}

.role-permissions {
  margin-bottom: 15px;
}

.role-permissions ul {
  margin: 10px 0;
  padding-left: 20px;
}

.role-permissions li {
  padding: 3px 0;
}

.role-footer {
  display: flex;
  gap: 10px;
  padding-top: 15px;
  border-top: 1px solid #eee;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

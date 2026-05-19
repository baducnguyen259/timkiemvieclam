<?php ob_start(); ?>
<?php
// Chuẩn hóa tên vai trò để hiển thị dễ hiểu trong bảng tài khoản.
$formatRole = static function($roleTitle) {
  $normalized = strtolower(trim((string)$roleTitle));

  if (strpos($normalized, 'admin') !== false) {
    return 'Quản trị viên';
  }

  if (strpos($normalized, 'employer') !== false) {
    return 'Nhà tuyển dụng';
  }

  return 'Người dùng';
};
?>

<div class="admin-page account-management-page">
  <div class="account-summary-header">
    <h2>Quản lý người dùng</h2>
    <p>Tổng: <span><?= (int)($totalAccounts ?? count($accounts ?? [])) ?></span></p>
  </div>

  <?php if (!empty($accounts)): ?>
  <div class="table-responsive">
    <table class="table account-management-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Họ tên</th>
          <th>Email</th>
          <th>Vai trò</th>
          <th>Trạng thái</th>
          <th>Ngày tạo</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($accounts as $account): ?>
        <?php
          $isActive = ($account->status ?? '') === 'active';
          $entityType = $account->entity_type ?? 'account';
          $isCurrentAccount = $entityType === 'account' && (int)$account->id === (int)($GLOBALS['current_user']->id ?? 0);
        ?>
        <tr>
          <td><?= (int)$account->id ?></td>
          <td><?= htmlspecialchars($account->full_name ?? 'Chưa cập nhật') ?></td>
          <td><?= htmlspecialchars($account->email ?? 'Chưa cập nhật') ?></td>
          <td><?= htmlspecialchars($formatRole($account->role_title ?? '')) ?></td>
          <td>
            <span class="badge <?= $isActive ? 'badge-active' : 'badge-inactive' ?> account-status-badge">
              <?= $isActive ? 'Đang hoạt động' : 'Đã khóa' ?>
            </span>
          </td>
          <td><?= !empty($account->created_at) ? date('j/n/Y', strtotime($account->created_at)) : '-' ?></td>
          <td>
            <?php if ($isCurrentAccount): ?>
            <span class="account-self-tag">Hiện tại</span>
            <?php else: ?>
            <div class="account-actions">
              <form method="POST" action="<?= BASE_PATH ?>/admin/account/toggle-status/<?= (int)$account->id ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="entity_type" value="<?= htmlspecialchars($entityType) ?>">
                <button type="submit" class="account-action-link <?= $isActive ? 'lock' : 'unlock' ?>">
                  <?= $isActive ? 'Khóa' : 'Mở khóa' ?>
                </button>
              </form>

              <form method="POST" action="<?= BASE_PATH ?>/admin/account/delete/<?= (int)$account->id ?>"
                onsubmit="return confirm('Bạn có chắc muốn xóa tài khoản này?')">
                <?= csrf_field() ?>
                <input type="hidden" name="entity_type" value="<?= htmlspecialchars($entityType) ?>">
                <button type="submit" class="account-action-link delete">Xóa</button>
              </form>
            </div>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="no-results">
    <p>Không có tài khoản nào.</p>
  </div>
  <?php endif; ?>
</div>

<style>
.account-management-page {
  padding: 0;
  overflow: hidden;
}

.account-summary-header {
  padding: 22px 22px 18px;
  border-bottom: 1px solid #dbe3ef;
}

.account-summary-header h2 {
  font-size: 40px;
  font-weight: 700;
  margin: 0 0 8px;
  color: #0f2244;
}

.account-summary-header p {
  margin: 0;
  font-size: 30px;
  color: #5c6d89;
}

.account-summary-header p span {
  color: #2d5ea8;
  font-weight: 700;
}

.account-management-table {
  margin: 0;
}

.account-management-table th,
.account-management-table td {
  padding: 16px 22px;
  border-bottom: 1px solid #dbe3ef;
  font-size: 16px;
}

.account-management-table th {
  background: #fff;
  color: #2d5ea8;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.2px;
}

.account-management-table tbody tr:hover {
  background: #f8fbff;
}

.account-status-badge {
  font-size: 14px;
  font-weight: 600;
  padding: 6px 14px;
}

.account-actions {
  display: flex;
  align-items: center;
  gap: 14px;
}

.account-action-link {
  border: none;
  background: transparent;
  font-weight: 700;
  font-size: 17px;
  cursor: pointer;
  padding: 0;
}

.account-action-link.lock {
  color: #ef4444;
}

.account-action-link.unlock {
  color: #2563eb;
}

.account-action-link.delete {
  color: #dc2626;
}

.account-self-tag {
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
}

@media (max-width: 991px) {
  .account-summary-header h2 {
    font-size: 30px;
  }

  .account-summary-header p {
    font-size: 22px;
  }

  .account-management-table th,
  .account-management-table td {
    padding: 12px 14px;
    font-size: 14px;
  }

  .account-action-link {
    font-size: 14px;
  }
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

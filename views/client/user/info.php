<?php ob_start(); ?>

<div class="user-info-page">
  <h1><i class="fa-solid fa-user-circle"></i> Thông tin tài khoản</h1>

  <div class="user-info-card">
    <?php if ($GLOBALS['current_user']->avatar): ?>
    <div class="user-avatar">
      <img src="<?= BASE_PATH ?>/uploads/<?= htmlspecialchars($GLOBALS['current_user']->avatar) ?>" alt="Ảnh đại diện">
    </div>
    <?php endif; ?>

    <div class="user-details">
      <div class="info-row">
        <label><i class="fa-solid fa-user"></i> Họ tên:</label>
        <span><?= htmlspecialchars($GLOBALS['current_user']->full_name) ?></span>
      </div>

      <div class="info-row">
        <label><i class="fa-solid fa-envelope"></i> Email:</label>
        <span><?= htmlspecialchars($GLOBALS['current_user']->email) ?></span>
      </div>

      <?php if ($GLOBALS['current_user']->phone): ?>
      <div class="info-row">
        <label><i class="fa-solid fa-phone"></i> Số điện thoại:</label>
        <span><?= htmlspecialchars($GLOBALS['current_user']->phone) ?></span>
      </div>
      <?php endif; ?>

      <div class="info-row">
        <label><i class="fa-solid fa-circle-check"></i> Trạng thái:</label>
        <span class="badge badge-<?= $GLOBALS['current_user']->status ?>">
          <?= $GLOBALS['current_user']->status === 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
        </span>
      </div>

      <div class="info-row">
        <label><i class="fa-regular fa-calendar"></i> Ngày tạo:</label>
        <span><?= date('d/m/Y', strtotime($GLOBALS['current_user']->created_at)) ?></span>
      </div>
    </div>

    <div class="user-actions">
      <a href="<?= BASE_PATH ?>/user/edit" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i> Chỉnh sửa thông tin</a>
      <a href="<?= BASE_PATH ?>/user/password/forgot" class="btn btn-secondary"><i class="fa-solid fa-lock"></i> Đổi mật khẩu</a>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

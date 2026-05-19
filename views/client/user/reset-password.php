<?php ob_start(); ?>

<div class="auth-page">
  <div class="auth-container">
    <h1>Đặt lại mật khẩu</h1>

    <form method="POST" action="<?= BASE_PATH ?>/user/password/reset" class="auth-form">
        <?= csrf_field() ?>
      <div class="form-group">
        <label for="password">Mật khẩu mới <span class="required">*</span></label>
        <input type="password" id="password" name="password" class="form-control" minlength="8" required>
      </div>

      <div class="form-group">
        <label for="confirmPassword">Xác nhận mật khẩu <span class="required">*</span></label>
        <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" minlength="8" required>
      </div>

      <button type="submit" class="btn btn-primary btn-block">
        Đặt lại mật khẩu
      </button>
    </form>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>
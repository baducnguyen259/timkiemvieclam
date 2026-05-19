<?php ob_start(); ?>

<div class="auth-page">
  <div class="auth-container">
    <h1>Quên mật khẩu</h1>
    <p>Nhập email của bạn để nhận mã OTP</p>

    <form method="POST" action="<?= BASE_PATH ?>/user/password/forgot" class="auth-form">
        <?= csrf_field() ?>
      <div class="form-group">
        <label for="email">Email <span class="required">*</span></label>
        <input type="email" id="email" name="email" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-primary btn-block">
        Gửi mã OTP
      </button>
    </form>

    <div class="auth-footer">
      <p><a href="<?= BASE_PATH ?>/user/login">Quay lại đăng nhập</a></p>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>
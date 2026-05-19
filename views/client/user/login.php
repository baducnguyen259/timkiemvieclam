<?php ob_start(); ?>

<div class="auth-page">
  <div class="auth-container">
    <h1>Đăng nhập</h1>
    <p class="auth-subtitle">Dùng chung cho Người dùng và Nhà tuyển dụng</p>

    <form method="POST" action="<?= BASE_PATH ?>/user/login" class="auth-form">
        <?= csrf_field() ?>
      <div class="form-group">
        <label for="email">Email <span class="required">*</span></label>
        <input type="email" id="email" name="email" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="password">Mật khẩu <span class="required">*</span></label>
        <input type="password" id="password" name="password" class="form-control" minlength="8" required>
      </div>

      <div class="form-group">
        <a href="<?= BASE_PATH ?>/user/password/forgot" class="forgot-password">
          Quên mật khẩu?
        </a>
      </div>

      <button type="submit" class="btn btn-primary btn-block">
        Đăng nhập
      </button>
    </form>

    <div class="auth-footer">
      <p>Chưa có tài khoản? <a href="<?= BASE_PATH ?>/user/register">Đăng ký ngay</a></p>
      <p>Tài khoản quản trị đăng nhập tại <a href="<?= BASE_PATH ?>/admin/auth/login">cổng quản trị</a>.</p>
    </div>
  </div>
</div>

<style>
.auth-subtitle {
  margin: -8px 0 16px;
  color: #64748b;
  font-size: 14px;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

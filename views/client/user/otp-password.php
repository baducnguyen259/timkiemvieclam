<?php ob_start(); ?>

<div class="auth-page">
  <div class="auth-container">
    <h1>Nhập mã OTP</h1>
    <p>Mã OTP đã được gửi đến email: <strong><?= htmlspecialchars($email) ?></strong></p>

    <form method="POST" action="<?= BASE_PATH ?>/user/password/otp" class="auth-form">
        <?= csrf_field() ?>
      <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

      <div class="form-group">
        <label for="otp">Mã OTP <span class="required">*</span></label>
        <input type="text" id="otp" name="otp" class="form-control" maxlength="8" required>
        <small>Mã OTP có hiệu lực trong 3 phút</small>
      </div>

      <button type="submit" class="btn btn-primary btn-block">
        Xác nhận
      </button>
    </form>

    <div class="auth-footer">
      <p><a href="<?= BASE_PATH ?>/user/password/forgot">Gửi lại mã OTP</a></p>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>
<?php ob_start(); ?>

<div class="auth-page">
  <div class="auth-container">
    <h1>Đăng ký tài khoản</h1>

    <form method="POST" action="<?= BASE_PATH ?>/user/register" class="auth-form">
        <?= csrf_field() ?>
      <div class="form-group">
        <label>Bạn đăng ký với vai trò <span class="required">*</span></label>
        <div class="account-type-options">
          <label class="account-type-option">
            <input type="radio" name="accountType" value="candidate" checked>
            <span>Ứng viên</span>
          </label>
          <label class="account-type-option">
            <input type="radio" name="accountType" value="employer">
            <span>Nhà tuyển dụng</span>
          </label>
        </div>
        <small>Nếu chọn nhà tuyển dụng, bạn sẽ được chuyển đến cổng nhà tuyển dụng sau khi đăng ký.</small>
      </div>

      <div class="form-group">
        <label for="fullName">Họ và tên <span class="required">*</span></label>
        <input type="text" id="fullName" name="fullName" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="email">Email <span class="required">*</span></label>
        <input type="email" id="email" name="email" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="password">Mật khẩu <span class="required">*</span></label>
        <div class="password-field">
          <input type="password" id="password" name="password" class="form-control" minlength="8" required>
          <button type="button" class="password-toggle" data-password-toggle aria-label="Hiện mật khẩu" aria-pressed="false">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
        <small>Mật khẩu phải có ít nhất 8 ký tự</small>
      </div>

      <div class="form-group">
        <label for="confirmPassword">Xác nhận mật khẩu <span class="required">*</span></label>
        <div class="password-field">
          <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" minlength="8" required>
          <button type="button" class="password-toggle" data-password-toggle aria-label="Hiện mật khẩu" aria-pressed="false">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
      </div>

      <div class="form-group policy-consent">
        <label class="checkbox-label">
          <input type="checkbox" name="acceptPolicy" value="1" required>
          <span>Tôi đồng ý với Điều khoản sử dụng và Chính sách bảo mật.</span>
        </label>
      </div>

      <button type="submit" class="btn btn-primary btn-block">
        Đăng ký
      </button>
    </form>

    <div class="auth-footer">
      <p>Đã có tài khoản? <a href="<?= BASE_PATH ?>/user/login">Đăng nhập ngay</a></p>
      <p>Nhà tuyển dụng đã có tài khoản? <a href="<?= BASE_PATH ?>/user/login">Đăng nhập nhà tuyển dụng</a></p>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

<!DOCTYPE html>
<html lang="vi">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập nhà tuyển dụng</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/css/admin.css">
  </head>

  <body class="auth-body employer-body">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <h1>Nhà tuyển dụng</h1>
          <p>Cổng nhà tuyển dụng</p>
        </div>

        <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error">
          <?= htmlspecialchars($_SESSION['flash_error']) ?>
          <?php unset($_SESSION['flash_error']); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_PATH ?>/employer/auth/login" class="auth-form">
            <?= csrf_field() ?>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" required autofocus>
          </div>

          <div class="form-group">
            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" class="form-control" required>
          </div>

          <button type="submit" class="btn btn-primary btn-block">
            Đăng nhập
          </button>
        </form>

        <div class="auth-footer">
          <a href="<?= BASE_PATH ?>/">Quay về trang chủ</a>
        </div>
      </div>
    </div>
  </body>

</html>

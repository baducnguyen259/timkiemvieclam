<!DOCTYPE html>
<html lang="vi">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($title ?? 'Bảng quản trị') ?> - Quản trị Job Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/css/admin.css">
  </head>

  <body class="admin-body">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="admin-main">
      <?php include __DIR__ . '/../partials/header.php'; ?>

      <div class="admin-content">
        <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success">
          <?= htmlspecialchars($_SESSION['flash_success']) ?>
          <?php unset($_SESSION['flash_success']); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error">
          <?= htmlspecialchars($_SESSION['flash_error']) ?>
          <?php unset($_SESSION['flash_error']); ?>
        </div>
        <?php endif; ?>

        <?php echo $content ?? ''; ?>
      </div>

      <?php include __DIR__ . '/../partials/footer.php'; ?>
    </div>

    <script src="<?= BASE_PATH ?>/js/admin.js"></script>
  </body>

</html>

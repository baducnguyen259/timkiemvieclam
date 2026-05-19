<!DOCTYPE html>
<html lang="vi">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'Job Portal - Nền tảng tìm việc làm hàng đầu Việt Nam') ?>">
    <title><?= htmlspecialchars($title ?? 'Job Portal') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/css/style.css">
  </head>

  <body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success">
      <i class="fa-solid fa-circle-check"></i>
      <?= htmlspecialchars($_SESSION['flash_success']) ?>
      <?php unset($_SESSION['flash_success']); ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-error">
      <i class="fa-solid fa-circle-xmark"></i>
      <?= htmlspecialchars($_SESSION['flash_error']) ?>
      <?php unset($_SESSION['flash_error']); ?>
    </div>
    <?php endif; ?>

    <main>
      <?php echo $content ?? ''; ?>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="<?= BASE_PATH ?>/js/script.js"></script>
  </body>

</html>

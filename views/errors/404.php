<!DOCTYPE html>
<html lang="vi">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy trang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
      rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/css/404.css">
  </head>

  <body>
    <div class="error-container">
      <div class="error-code">404</div>
      <div class="error-message">Không tìm thấy trang</div>
      <div class="error-description">
        Trang bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.
      </div>
      <a href="<?= BASE_PATH ?>/" class="btn">← Về trang chủ</a>
    </div>
  </body>

</html>
<footer class="footer">
  <div class="container">
    <div class="footer-content">
      <div class="footer-section footer-brand">
        <div class="footer-logo">
          <i class="fa-solid fa-briefcase"></i>
          <span><strong>DDS</strong></span>
        </div>
        <p>Nền tảng tìm việc làm hàng đầu Việt Nam. Kết nối nhà tuyển dụng với ứng viên tiềm năng.</p>
        <div class="footer-social">
          <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
          <a href="#" aria-label="Twitter"><i class="fa-brands fa-twitter"></i></a>
          <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
        </div>
      </div>

      <div class="footer-section">
        <h4>Dành cho ứng viên</h4>
        <ul>
          <li><a href="<?= BASE_PATH ?>/jobs"><i class="fa-solid fa-angle-right"></i> Tìm việc làm</a></li>
          <li><a href="<?= BASE_PATH ?>/saved-jobs"><i class="fa-solid fa-angle-right"></i> Việc đã lưu</a></li>
          <li><a href="<?= BASE_PATH ?>/applications"><i class="fa-solid fa-angle-right"></i> Đơn ứng tuyển</a></li>
          <li><a href="<?= BASE_PATH ?>/user/register"><i class="fa-solid fa-angle-right"></i> Đăng ký tài khoản</a>
          </li>
        </ul>
      </div>

      <div class="footer-section">
        <h4>Danh mục phổ biến</h4>
        <ul>
          <?php if (!empty($GLOBALS['layoutProductsCategory'])): ?>
          <?php foreach (array_slice($GLOBALS['layoutProductsCategory'], 0, 5) as $cat): ?>
          <li><a href="<?= BASE_PATH ?>/jobs/<?= htmlspecialchars($cat->slug) ?>"><i
                class="fa-solid fa-angle-right"></i> <?= htmlspecialchars($cat->title) ?></a></li>
          <?php endforeach; ?>
          <?php else: ?>
          <li><a href="<?= BASE_PATH ?>/jobs"><i class="fa-solid fa-angle-right"></i> Xem tất cả việc làm</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="footer-section">
        <h4>Liên hệ</h4>
        <ul class="footer-contact">
          <li><i class="fa-solid fa-envelope"></i> baducnguyen259@gmail.com</li>
          <li><i class="fa-solid fa-phone"></i> 0898 936 177</li>
          <li><i class="fa-solid fa-location-dot"></i> TP. Hà Nội, Việt Nam</li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> DDS. All rights reserved.</p>
    </div>
  </div>
</footer>
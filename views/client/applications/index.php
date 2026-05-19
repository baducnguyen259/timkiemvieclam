<?php ob_start(); ?>

<div class="applications-page">
  <h1>Đơn ứng tuyển của tôi</h1>

  <?php if (!empty($applications)): ?>
  <div class="applications-list">
    <?php foreach ($applications as $app): ?>
    <div class="application-item">
      <div class="application-header">
        <h3>
          <a href="<?= BASE_PATH ?>/jobs/detail/<?= htmlspecialchars($app->job_slug) ?>">
            <?= htmlspecialchars($app->job_title) ?>
          </a>
        </h3>

        <span class="badge badge-<?= $app->status ?>">
          <?php
            $statusText = [
              'pending' => 'Chờ xử lý',
              'reviewed' => 'Đã xem',
              'accepted' => 'Chấp nhận',
              'rejected' => 'Từ chối'
            ];
            echo $statusText[$app->status] ?? $app->status;
          ?>
        </span>
      </div>

      <div class="application-info">
        <p><strong>Email:</strong> <?= htmlspecialchars($app->email) ?></p>
        <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($app->phone ?? 'Không có') ?></p>
        <p><strong>Ngày ứng tuyển:</strong> <?= date('d/m/Y H:i', strtotime($app->created_at)) ?></p>

        <?php if (!empty($app->cv_link ?? '')): ?>
        <p>
          <strong>Liên kết CV:</strong>
          <a href="<?= htmlspecialchars($app->cv_link) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm">
            Mở liên kết CV
          </a>
        </p>
        <?php endif; ?>

        <?php if (!empty($app->cv_file ?? '')): ?>
        <p>
          <strong>Tệp CV:</strong>
          <a href="<?= BASE_PATH ?>/<?= htmlspecialchars($app->cv_file) ?>" target="_blank" class="btn btn-sm">
            Xem CV
          </a>
        </p>
        <?php endif; ?>
      </div>

      <?php if (!empty($app->cover_letter)): ?>
      <div class="application-cover-letter">
        <strong>Thư xin việc:</strong>
        <p><?= nl2br(htmlspecialchars($app->cover_letter)) ?></p>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="no-results">
    <p>Bạn chưa ứng tuyển công việc nào.</p>
    <a href="<?= BASE_PATH ?>/jobs" class="btn btn-primary">Tìm việc làm</a>
  </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

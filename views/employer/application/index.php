<?php ob_start(); ?>
<?php
$statusText = [
  'pending' => 'Chờ xử lý',
  'reviewed' => 'Đã xem',
  'accepted' => 'Chấp nhận',
  'rejected' => 'Từ chối'
];

$statusBadgeClass = [
  'pending' => 'badge-pending',
  'reviewed' => 'badge-reviewed',
  'accepted' => 'badge-accepted',
  'rejected' => 'badge-rejected'
];

$queryParams = $_GET;
unset($queryParams['page']);
$baseQuery = http_build_query($queryParams);
// Tạo URL phân trang cho danh sách ứng tuyển nhưng giữ lại các bộ lọc hiện tại.
$buildPageUrl = static function ($page) use ($baseQuery) {
  if ($baseQuery !== '') {
    return '?' . $baseQuery . '&page=' . (int)$page;
  }
  return '?page=' . (int)$page;
};
?>

<div class="admin-page">
  <div class="page-header">
    <h1><?= htmlspecialchars($title) ?></h1>
  </div>

  <div class="filter-section">
    <form method="GET" action="<?= BASE_PATH ?>/employer/application" class="filter-form">
      <div class="filter-group">
        <input type="text" name="keyword" placeholder="Tìm theo ứng viên, email, vị trí..."
          value="<?= htmlspecialchars($keyword ?? '') ?>" class="form-control">
      </div>

      <div class="filter-group">
        <select name="job_id" class="form-control">
          <option value="">Tất cả tin tuyển dụng</option>
          <?php foreach ($jobs as $job): ?>
          <option value="<?= (int)$job->id ?>" <?= ((string)($filters['job_id'] ?? '') === (string)$job->id) ? 'selected' : '' ?>>
            <?= htmlspecialchars($job->title) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="filter-group">
        <select name="status" class="form-control">
          <?php foreach ($filterStatus as $item): ?>
          <option value="<?= htmlspecialchars($item['value']) ?>" <?= $item['selected'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($item['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">Lọc</button>
      <a href="<?= BASE_PATH ?>/employer/application" class="btn btn-secondary">Xóa lọc</a>
    </form>
  </div>

  <?php if (!empty($applications)): ?>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Ứng viên</th>
          <th>Tin tuyển dụng</th>
          <th>CV</th>
          <th>Trạng thái</th>
          <th>Ngày ứng tuyển</th>
          <th width="280">Xét duyệt</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($applications as $app): ?>
        <tr>
          <td>
            <strong><?= htmlspecialchars($app->full_name) ?></strong><br>
            <small><?= htmlspecialchars($app->email) ?></small><br>
            <small><?= htmlspecialchars($app->phone ?: 'Không có số điện thoại') ?></small>
          </td>

          <td>
            <a href="<?= BASE_PATH ?>/employer/job/detail/<?= (int)$app->job_id ?>">
              <?= htmlspecialchars($app->job_title) ?>
            </a>
            <?php if (!empty($app->cover_letter)): ?>
            <p style="margin-top: 6px; color: var(--gray-500);">
              <?= nl2br(htmlspecialchars($app->cover_letter)) ?>
            </p>
            <?php endif; ?>
          </td>

          <td>
            <?php if (!empty($app->cv_link ?? '')): ?>
            <a href="<?= htmlspecialchars($app->cv_link) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm">
              Liên kết CV
            </a>
            <?php endif; ?>

            <?php if (!empty($app->cv_file ?? '')): ?>
            <a href="<?= BASE_PATH ?>/<?= htmlspecialchars($app->cv_file) ?>" target="_blank" class="btn btn-sm">
              Tệp CV
            </a>
            <?php endif; ?>

            <?php if (empty($app->cv_link ?? '') && empty($app->cv_file ?? '')): ?>
            <span class="text-muted">Không có CV</span>
            <?php endif; ?>
          </td>

          <td>
            <?php $badgeClass = $statusBadgeClass[$app->status] ?? 'badge-pending'; ?>
            <span class="badge <?= $badgeClass ?>">
              <?= htmlspecialchars($statusText[$app->status] ?? $app->status) ?>
            </span>
          </td>

          <td><?= !empty($app->created_at) ? date('d/m/Y H:i', strtotime($app->created_at)) : 'Chưa cập nhật' ?></td>

          <td>
            <div class="btn-group application-actions">
              <?php if ($app->status !== 'pending'): ?>
              <form method="POST" action="<?= BASE_PATH ?>/employer/application/change-status/pending/<?= (int)$app->id ?>">
                  <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm">Chờ xử lý</button>
              </form>
              <?php endif; ?>

              <?php if ($app->status !== 'reviewed'): ?>
              <form method="POST" action="<?= BASE_PATH ?>/employer/application/change-status/reviewed/<?= (int)$app->id ?>">
                  <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-secondary">Đã xem</button>
              </form>
              <?php endif; ?>

              <?php if ($app->status !== 'accepted'): ?>
              <form method="POST" action="<?= BASE_PATH ?>/employer/application/change-status/accepted/<?= (int)$app->id ?>">
                  <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-primary">Chấp nhận</button>
              </form>
              <?php endif; ?>

              <?php if ($app->status !== 'rejected'): ?>
              <form method="POST" action="<?= BASE_PATH ?>/employer/application/change-status/rejected/<?= (int)$app->id ?>">
                  <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-danger">Từ chối</button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pagination['totalPage'] > 1): ?>
  <div class="pagination">
    <?php if ($pagination['page'] > 1): ?>
    <a href="<?= $buildPageUrl($pagination['page'] - 1) ?>" class="btn">« Trước</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
    <a href="<?= $buildPageUrl($i) ?>" class="btn <?= $i === $pagination['page'] ? 'active' : '' ?>">
      <?= $i ?>
    </a>
    <?php endfor; ?>

    <?php if ($pagination['page'] < $pagination['totalPage']): ?>
    <a href="<?= $buildPageUrl($pagination['page'] + 1) ?>" class="btn">Sau »</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php else: ?>
  <div class="no-results">
    <p>Chưa có ứng tuyển nào phù hợp bộ lọc.</p>
  </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

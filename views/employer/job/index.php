<?php ob_start(); ?>
<?php
// Định dạng khoảng lương cho bảng tin tuyển dụng của nhà tuyển dụng.
$formatSalary = static function($salaryMin, $salaryMax) {
  $salaryMin = (float)$salaryMin;
  $salaryMax = (float)$salaryMax;

  if ($salaryMin > 0 && $salaryMax > 0) {
    if (abs($salaryMin - $salaryMax) < 0.01) {
      return number_format($salaryMin) . ' VNĐ';
    }
    return number_format($salaryMin) . ' - ' . number_format($salaryMax) . ' VNĐ';
  }

  if ($salaryMin > 0) {
    return number_format($salaryMin) . ' VNĐ';
  }

  if ($salaryMax > 0) {
    return number_format($salaryMax) . ' VNĐ';
  }

  return 'Thỏa thuận';
};
?>

<div class="admin-page">
  <div class="page-header">
    <h1><?= htmlspecialchars($title) ?></h1>
    <div class="page-actions">
      <a href="<?= BASE_PATH ?>/employer/job/create" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i>
        Đăng tin mới
      </a>
    </div>
  </div>

  <div class="filter-section">
    <form method="GET" action="<?= BASE_PATH ?>/employer/job" class="filter-form">
      <div class="filter-group">
        <input type="text" name="keyword" placeholder="Tìm kiếm tiêu đề..." value="<?= htmlspecialchars($keyword ?? '') ?>"
          class="form-control">
      </div>

      <div class="filter-group">
        <select name="status" class="form-control">
          <?php foreach ($filterStatus as $item): ?>
          <option value="<?= htmlspecialchars($item['value']) ?>" <?= $item['selected'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($item['name'] ?: 'Tất cả trạng thái') ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">Lọc</button>
      <a href="<?= BASE_PATH ?>/employer/job" class="btn btn-secondary">Xóa lọc</a>
    </form>
  </div>

  <?php if (!empty($jobs)): ?>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Tiêu đề</th>
          <th>Địa điểm</th>
          <th>Lương</th>
          <th>Trạng thái</th>
          <th>Ngày tạo</th>
          <th width="160">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($jobs as $job): ?>
        <tr>
          <td>
            <a href="<?= BASE_PATH ?>/employer/job/detail/<?= $job->id ?>">
              <?= htmlspecialchars($job->title) ?>
            </a>
          </td>
          <td><?= htmlspecialchars($job->location ?? 'Chưa cập nhật') ?></td>
          <td><?= htmlspecialchars($formatSalary($job->salary_min ?? 0, $job->salary_max ?? 0)) ?></td>
          <td>
            <span class="badge badge-<?= $job->status ?>">
              <?= $job->status === 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
            </span>
          </td>
          <td><?= date('d/m/Y', strtotime($job->created_at)) ?></td>
          <td>
            <div class="btn-group">
              <a href="<?= BASE_PATH ?>/employer/job/edit/<?= $job->id ?>" class="btn btn-sm btn-primary" title="Sửa">
                <i class="fa-solid fa-pen-to-square"></i>
              </a>

              <form method="POST"
                action="<?= BASE_PATH ?>/employer/job/change-status/<?= $job->status === 'active' ? 'inactive' : 'active' ?>/<?= $job->id ?>"
                style="display: inline;">
                  <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-warning" title="Đổi trạng thái">
                  <i class="fa-solid fa-rotate"></i>
                </button>
              </form>

              <form method="POST" action="<?= BASE_PATH ?>/employer/job/delete/<?= $job->id ?>" style="display: inline;"
                onsubmit="return confirm('Bạn có chắc muốn xóa tin tuyển dụng này?')">
                  <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>
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
    <a href="?page=<?= $pagination['page'] - 1 ?>" class="btn">« Trước</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $pagination['totalPage']; $i++): ?>
    <a href="?page=<?= $i ?>" class="btn <?= $i === $pagination['page'] ? 'active' : '' ?>">
      <?= $i ?>
    </a>
    <?php endfor; ?>

    <?php if ($pagination['page'] < $pagination['totalPage']): ?>
    <a href="?page=<?= $pagination['page'] + 1 ?>" class="btn">Sau »</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php else: ?>
  <div class="no-results">
    <p>Bạn chưa có tin tuyển dụng nào.</p>
    <a href="<?= BASE_PATH ?>/employer/job/create" class="btn btn-primary">Đăng tin đầu tiên</a>
  </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

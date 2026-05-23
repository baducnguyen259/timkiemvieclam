<?php ob_start(); ?>
<?php
// Định dạng khoảng lương cho bảng quản lý tin tuyển dụng.
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
$queryParams = $_GET;
unset($queryParams['page']);
$baseQuery = http_build_query($queryParams);
// Tạo URL phân trang cho danh sách tin tuyển dụng nhưng giữ lại các bộ lọc hiện tại.
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
    <form method="GET" action="<?= BASE_PATH ?>/admin/job" class="filter-form">
      <div class="filter-group">
        <input type="text" name="keyword" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($keyword ?? '') ?>"
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
      <a href="<?= BASE_PATH ?>/admin/job" class="btn btn-secondary">Xóa lọc</a>
    </form>
  </div>

  <div class="bulk-actions">
    <form method="POST" action="<?= BASE_PATH ?>/admin/job/change-multi" id="form-change-multi">
        <?= csrf_field() ?>
      <input type="hidden" name="ids" id="input-ids">

      <select name="type" class="form-control" id="select-type">
        <option value="">Chọn hành động</option>
        <option value="active">Kích hoạt</option>
        <option value="inactive">Vô hiệu hóa</option>
        <option value="delete-all">Xóa tất cả</option>
      </select>

      <button type="submit" class="btn btn-primary">Áp dụng</button>
    </form>
  </div>

  <?php if (!empty($jobs)): ?>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th width="50">
            <input type="checkbox" id="checkbox-all">
          </th>
          <th width="80">STT</th>
          <th>Tiêu đề</th>
          <th>Địa điểm</th>
          <th>Lương</th>
          <th>Trạng thái</th>
          <th>Người tạo</th>
          <th width="120">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($jobs as $index => $job): ?>
        <tr>
          <td>
            <input type="checkbox" class="checkbox-item" value="<?= $job->id ?>">
          </td>
          <td><?= (int)$pagination['skipItem'] + $index + 1 ?></td>
          <td>
            <a href="<?= BASE_PATH ?>/admin/job/detail/<?= $job->id ?>">
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
          <td><?= htmlspecialchars($job->account_full_name ?? 'Chưa cập nhật') ?></td>
          <td>
            <div class="btn-group">
              <form method="POST"
                action="<?= BASE_PATH ?>/admin/job/change-status/<?= $job->status === 'active' ? 'inactive' : 'active' ?>/<?= $job->id ?>"
                style="display: inline;">
                  <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-warning" title="Đổi trạng thái">
                  <i class="fa-solid fa-rotate"></i>
                </button>
              </form>

              <form method="POST" action="<?= BASE_PATH ?>/admin/job/delete/<?= $job->id ?>" style="display: inline;"
                onsubmit="return confirm('Bạn có chắc muốn xóa?')">
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
    <p>Không có công việc nào.</p>
  </div>
  <?php endif; ?>
</div>

<script>
document.getElementById('checkbox-all')?.addEventListener('change', function() {
  document.querySelectorAll('.checkbox-item').forEach(cb => {
    cb.checked = this.checked;
  });
});

document.getElementById('form-change-multi')?.addEventListener('submit', function(e) {
  const type = document.getElementById('select-type').value;

  if (!type) {
    e.preventDefault();
    alert('Vui lòng chọn hành động');
    return;
  }

  const ids = [];
  document.querySelectorAll('.checkbox-item:checked').forEach(cb => {
    ids.push(cb.value);
  });

  if (ids.length === 0) {
    e.preventDefault();
    alert('Vui lòng chọn ít nhất một mục');
    return;
  }

  document.getElementById('input-ids').value = ids.join(', ');
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

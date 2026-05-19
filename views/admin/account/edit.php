<?php ob_start(); ?>

<div class="admin-page">
  <div class="page-header">
    <h1><?= htmlspecialchars($title) ?></h1>
    <div class="page-actions">
      <a href="<?= BASE_PATH ?>/admin/account" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        Quay lại
      </a>
    </div>
  </div>

  <form method="POST" action="<?= BASE_PATH ?>/admin/account/edit/<?= $account->id ?>" class="admin-form">
      <?= csrf_field() ?>
    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="fullName">Họ tên <span class="required">*</span></label>
        <input type="text" id="fullName" name="fullName" class="form-control"
          value="<?= htmlspecialchars($account->full_name) ?>" required>
      </div>

      <div class="form-group col-md-6">
        <label for="email">Email <span class="required">*</span></label>
        <input type="email" id="email" name="email" class="form-control"
          value="<?= htmlspecialchars($account->email) ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-md-6">
        <label for="phone">Số điện thoại</label>
        <input type="tel" id="phone" name="phone" class="form-control"
          value="<?= htmlspecialchars($account->phone ?? '') ?>">
      </div>

      <div class="form-group col-md-6">
        <label for="password">Mật khẩu mới</label>
        <input type="password" id="password" name="password" class="form-control"
          placeholder="Để trống nếu không đổi">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-md-4">
        <label for="role_id">Vai trò</label>
        <select id="role_id" name="role_id" class="form-control">
          <option value="">Chọn vai trò</option>
          <?php foreach ($roles as $role): ?>
          <option value="<?= $role->id ?>" <?= $account->role_id == $role->id ? 'selected' : '' ?>>
            <?= htmlspecialchars($role->title) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group col-md-4">
        <label for="company_id">Công ty</label>
        <select id="company_id" name="company_id" class="form-control">
          <option value="">Không có</option>
          <?php foreach ($companies as $company): ?>
          <option value="<?= $company->id ?>" <?= $account->company_id == $company->id ? 'selected' : '' ?>>
            <?= htmlspecialchars($company->name) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group col-md-4">
        <label for="status">Trạng thái</label>
        <select id="status" name="status" class="form-control">
          <option value="active" <?= $account->status === 'active' ? 'selected' : '' ?>>Hoạt động</option>
          <option value="inactive" <?= $account->status === 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
        </select>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Cập nhật</button>
      <a href="<?= BASE_PATH ?>/admin/account" class="btn btn-secondary">Hủy</a>
    </div>
  </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

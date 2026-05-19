<?php ob_start(); ?>

<div class="admin-page">
  <div class="page-header">
    <h1><?= htmlspecialchars($title) ?></h1>
    <div class="page-actions">
      <a href="<?= BASE_PATH ?>/admin/job-category/create" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i>
        Tạo danh mục mới
      </a>
    </div>
  </div>

  <?php if (!empty($categoryTree)): ?>
  <div class="category-tree">
    <?php 
            // Render đệ quy cây danh mục, tăng thụt lề theo cấp con.
            function renderCategoryTree($categories, $level = 0) {
                foreach ($categories as $category) {
                    echo '<div class="category-item" style="padding-left: ' . ($level * 30) . 'px;">';
                    echo '<div class="category-content">';
                    echo '<span class="category-title">';
                    if ($level > 0) {
                        echo str_repeat('└─ ', 1);
                    }
                    echo htmlspecialchars($category->title);
                    echo '</span>';
                    
                    echo '<span class="badge badge-' . $category->status . '">';
                    echo $category->status === 'active' ? 'Hoạt động' : 'Không hoạt động';
                    echo '</span>';
                    
                    echo '<div class="category-actions">';
                    echo '<a href="' . BASE_PATH . '/admin/job-category/edit/' . $category->id . '" class="btn btn-sm btn-primary">';
                    echo '<i class="fa-solid fa-pen-to-square"></i> Sửa';
                    echo '</a>';
                    echo '<form method="POST" action="' . BASE_PATH . '/admin/job-category/delete/' . $category->id . '" style="display: inline;" onsubmit="return confirm(\'Bạn có chắc muốn xóa?\')">';
                    echo csrf_field();
                    echo '<button type="submit" class="btn btn-sm btn-danger">';
                    echo '<i class="fa-solid fa-trash"></i> Xóa';
                    echo '</button>';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    
                    if (!empty($category->children)) {
                        renderCategoryTree($category->children, $level + 1);
                    }
                }
            }
            renderCategoryTree($categoryTree);
            ?>
  </div>
  <?php else: ?>
  <div class="no-results">
    <p>Chưa có danh mục nào.</p>
  </div>
  <?php endif; ?>
</div>

<style>
.category-tree {
  background: white;
  border-radius: 8px;
  padding: 20px;
}

.category-item {
  padding: 12px 0;
  border-bottom: 1px solid #eee;
}

.category-item:last-child {
  border-bottom: none;
}

.category-content {
  display: flex;
  align-items: center;
  gap: 15px;
}

.category-title {
  flex: 1;
  font-weight: 500;
}

.category-actions {
  display: flex;
  gap: 5px;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/default.php';
?>

<?php
/**
 * Giải thích mã:
 * - Tiện ích dùng chung cho nghiệp vụ danh mục sản phẩm.
 * - Đóng gói logic lặp lại để controller/model tập trung vào luồng nghiệp vụ chính.
 */
require_once __DIR__ . '/../models/JobCategory.php';

class ProductCategory {
    // SỬA HIỆU NĂNG: Lấy toàn bộ danh mục một lần thay vì truy vấn đệ quy
    /**
     * Lấy toàn bộ danh mục con của một danh mục cha bằng cách nạp danh mục một lần rồi duyệt trong bộ nhớ.
     */
    public static function getSubCategories($parentId) {
        $categoryModel = new JobCategory();
        
        // Lấy toàn bộ danh mục trong một lần truy vấn
        $allCategories = $categoryModel->find([
            'deleted' => false,
            'status' => 'active'
        ]);
        
        // Tạo bảng ánh xạ để tra cứu nhanh
        $categoryMap = [];
        foreach ($allCategories as $cat) {
            $categoryMap[$cat->id] = $cat;
        }
        
        // Lấy danh mục con theo đệ quy
        return self::getSubCategoriesRecursive($parentId, $categoryMap);
    }
    
    /**
     * Duyệt đệ quy trên map danh mục đã nạp để gom tất cả cấp con của parent hiện tại.
     */
    private static function getSubCategoriesRecursive($parentId, $categoryMap) {
        $result = [];
        
        foreach ($categoryMap as $category) {
            if ($category->parent_id == $parentId) {
                $result[] = $category;
                
                // Lấy các phần tử con
                $children = self::getSubCategoriesRecursive($category->id, $categoryMap);
                $result = array_merge($result, $children);
            }
        }
        
        return $result;
    }
}

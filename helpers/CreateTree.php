<?php
/**
 * Giải thích mã:
 * - Tiện ích dùng chung cho nghiệp vụ tạo cây.
 * - Đóng gói logic lặp lại để controller/model tập trung vào luồng nghiệp vụ chính.
 */
class CreateTree {
    // SỬA ĐỘ TIN CẬY: Thêm cơ chế chặn tham chiếu vòng
    /**
     * Dựng danh sách phẳng thành cây cha-con, đồng thời giới hạn độ sâu để tránh vòng lặp dữ liệu.
     */
    public static function build($arr, $parentId = '', $visited = [], $maxDepth = 10, $currentDepth = 0) {
        // Ngăn đệ quy vô hạn
        if ($currentDepth >= $maxDepth) {
            return [];
        }
        
        if (in_array($parentId, $visited)) {
            return []; // Phát hiện tham chiếu vòng
        }
        
        $visited[] = $parentId;
        $tree = [];
        
        foreach ($arr as $item) {
            $itemParentId = $item->parent_id ?? '';
            
            if ($itemParentId == $parentId) {
                $newItem = clone $item;
                
                $children = self::build(
                    $arr, 
                    (string)$item->id, 
                    $visited, 
                    $maxDepth, 
                    $currentDepth + 1
                );
                
                if (!empty($children)) {
                    $newItem->children = $children;
                }
                
                $tree[] = $newItem;
            }
        }
        
        return $tree;
    }
}

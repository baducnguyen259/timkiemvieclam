<?php
/**
 * Giải thích mã:
 * - Tiện ích dùng chung cho nghiệp vụ phân trang.
 * - Đóng gói logic lặp lại để controller/model tập trung vào luồng nghiệp vụ chính.
 */
class Pagination {
    /**
     * Tính trang hiện tại, offset và tổng số trang từ tổng số bản ghi.
     */
    public static function calculate($limitItem, $currentPage, $totalItems) {
        $page = (int)$currentPage;
        if ($page < 1) {
            $page = 1;
        }
        
        $totalPage = ceil($totalItems / $limitItem);
        
        // Ngăn tràn số trang
        if ($page > $totalPage && $totalPage > 0) {
            $page = $totalPage;
        }
        
        $skipItem = ($page - 1) * $limitItem;
        
        return [
            'page' => $page,
            'limitItem' => $limitItem,
            'skipItem' => $skipItem,
            'totalPage' => $totalPage,
            'totalItems' => $totalItems
        ];
    }
}

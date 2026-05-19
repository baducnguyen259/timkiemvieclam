<?php
/**
 * Chuẩn hóa nhãn hiển thị cho loại hình công việc.
 */
class JobType {
    private const LABELS = [
        'Full-time' => 'Toàn thời gian',
        'Part-time' => 'Bán thời gian',
        'Contract' => 'Hợp đồng',
        'Internship' => 'Thực tập',
    ];

    /**
     * Chuyển mã loại việc làm sang nhãn tiếng Việt, giữ nguyên giá trị gốc nếu chưa có ánh xạ.
     */
    public static function label($type, $fallback = 'Chưa cập nhật') {
        $type = trim((string)$type);

        if ($type === '') {
            return $fallback;
        }

        return self::LABELS[$type] ?? $type;
    }
}

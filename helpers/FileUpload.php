<?php
/**
 * Giải thích mã:
 * - Tiện ích dùng chung cho nghiệp vụ tải tệp.
 * - Đóng gói logic lặp lại để controller/model tập trung vào luồng nghiệp vụ chính.
 */
class FileUpload {
    private $allowedExtensions;
    private $allowedMimeTypes;
    private $maxSize;

    public function __construct($allowedExtensions = null, $allowedMimeTypes = null, $maxSize = null) {
        $this->allowedExtensions = $this->normalizeExtensions($allowedExtensions ?? $this->extensionsFromEnv());
        $this->allowedMimeTypes = $allowedMimeTypes ?? $this->defaultMimeTypes();
        $this->maxSize = $maxSize ?? $this->maxSizeFromEnv();
    }
    
    /**
     * Kiểm tra file upload, tạo tên mới an toàn và chuyển file vào thư mục đích trong project.
     */
    public function upload($file, $destination = 'uploads/') {
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new Exception($this->uploadErrorMessage((int)$file['error']));
        }

        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Không có tệp nào được tải lên');
        }
        
        // Kiểm tra kích thước file
        $fileSize = (int)($file['size'] ?? filesize($file['tmp_name']));
        if ($fileSize > $this->maxSize) {
            throw new Exception('Kích thước tệp vượt quá giới hạn');
        }
        
        // Kiểm tra phần mở rộng file
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            throw new Exception('Loại tệp không hợp lệ');
        }

        $this->validateMimeType($file['tmp_name'], $extension);
        
        // Tạo tên file duy nhất để tránh trùng
        $filename = bin2hex(random_bytes(8)) . '_' . time() . '.' . $extension;
        
        // Chuẩn hóa đường dẫn đích thành đường dẫn tuyệt đối trong project
        $normalizedDestination = $this->normalizeDestination($destination);
        $projectRoot = dirname(__DIR__);
        $absoluteDestination = $projectRoot . DIRECTORY_SEPARATOR . $normalizedDestination;
        
        // Giữ đường dẫn trả về ở dạng tương đối của project (dùng dấu /)
        $relativeDestination = str_replace('\\', '/', trim($normalizedDestination, '\\/')) . '/';
        $filepath = $relativeDestination . $filename;
        $absoluteFilePath = rtrim($absoluteDestination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        
        // Tạo thư mục đích nếu chưa tồn tại
        if (!is_dir($absoluteDestination)) {
            if (!mkdir($absoluteDestination, 0755, true) && !is_dir($absoluteDestination)) {
                throw new Exception('Không thể tạo thư mục tải lên');
            }
        }
        
        // Di chuyển file upload vào thư mục đích
        if (!move_uploaded_file($file['tmp_name'], $absoluteFilePath)) {
            throw new Exception('Không thể di chuyển tệp đã tải lên');
        }
        
        return $filepath;
    }

    private function extensionsFromEnv() {
        $configured = trim((string)($_ENV['ALLOWED_EXTENSIONS'] ?? ''));
        if ($configured === '') {
            return ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        }

        return array_filter(array_map('trim', explode(',', $configured)));
    }

    private function maxSizeFromEnv() {
        $maxSize = filter_var($_ENV['MAX_FILE_SIZE'] ?? null, FILTER_VALIDATE_INT);
        return $maxSize !== false && $maxSize > 0 ? $maxSize : 5 * 1024 * 1024;
    }

    private function normalizeExtensions($extensions) {
        return array_values(array_unique(array_map(static function($extension) {
            return strtolower(ltrim((string)$extension, '.'));
        }, (array)$extensions)));
    }

    private function defaultMimeTypes() {
        return [
            'jpg' => ['image/jpeg', 'image/pjpeg'],
            'jpeg' => ['image/jpeg', 'image/pjpeg'],
            'png' => ['image/png'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword', 'application/octet-stream'],
            'docx' => [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip',
                'application/octet-stream'
            ],
        ];
    }

    private function validateMimeType($tmpPath, $extension) {
        if (!function_exists('finfo_open')) {
            return;
        }

        $allowedMimeTypes = $this->allowedMimeTypes[$extension] ?? [];
        if (empty($allowedMimeTypes)) {
            return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return;
        }

        $mimeType = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        if (!is_string($mimeType) || !in_array($mimeType, $allowedMimeTypes, true)) {
            throw new Exception('Nội dung tệp không khớp định dạng cho phép');
        }
    }

    private function normalizeDestination($destination) {
        $destination = trim((string)$destination);
        if ($destination === '') {
            throw new Exception('Thư mục tải lên không hợp lệ');
        }

        if (preg_match('/^[a-zA-Z]:/', $destination) || str_starts_with($destination, '/') || str_starts_with($destination, '\\')) {
            throw new Exception('Thư mục tải lên không hợp lệ');
        }

        $parts = preg_split('#[\\\\/]+#', $destination, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($parts as $part) {
            if ($part === '.' || $part === '..') {
                throw new Exception('Thư mục tải lên không hợp lệ');
            }
        }

        if (empty($parts)) {
            throw new Exception('Thư mục tải lên không hợp lệ');
        }

        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    private function uploadErrorMessage($errorCode) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Kích thước tệp vượt quá giới hạn máy chủ',
            UPLOAD_ERR_FORM_SIZE => 'Kích thước tệp vượt quá giới hạn biểu mẫu',
            UPLOAD_ERR_PARTIAL => 'Tệp chỉ được tải lên một phần',
            UPLOAD_ERR_NO_FILE => 'Không có tệp nào được tải lên',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm để tải lên',
            UPLOAD_ERR_CANT_WRITE => 'Không thể ghi tệp đã tải lên',
            UPLOAD_ERR_EXTENSION => 'Tệp tải lên bị chặn bởi extension PHP',
        ];

        return $messages[$errorCode] ?? 'Tải tệp lên thất bại';
    }
}

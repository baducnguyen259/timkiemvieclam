<?php
/**
 * Giải thích mã:
 * - Lớp truy cập cơ sở dữ liệu xây trên PDO.
 * - Cung cấp hàm truy vấn an toàn, giao dịch và các tiện ích vận hành cơ sở dữ liệu.
 * Lớp kết nối cơ sở dữ liệu
 * Quản lý kết nối và truy vấn MySQL bằng PDO
 */

class Database {
    private static $connection = null;
    
    /**
     * Thiết lập kết nối cơ sở dữ liệu
     * @return PDO
     */
    public static function connect() {
        if (self::$connection === null) {
            try {
                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $dbname = $_ENV['DB_NAME'] ?? 'job_portal';
                $username = $_ENV['DB_USER'] ?? 'root';
                $password = $_ENV['DB_PASSWORD'] ?? '';
                $charset = 'utf8mb4';
                
                $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];
                
                self::$connection = new PDO($dsn, $username, $password, $options);
                
            } catch (PDOException $e) {
                // Ghi log lỗi
                error_log("Không thể kết nối cơ sở dữ liệu: " . $e->getMessage());

                if (PHP_SAPI === 'cli') {
                    throw new RuntimeException("Không thể kết nối cơ sở dữ liệu: " . $e->getMessage(), 0, $e);
                }
                
                // Chỉ hiển thị lỗi chi tiết khi bật chế độ debug
                $debugMode = defined('APP_DEBUG')
                    ? APP_DEBUG
                    : filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
                if ($debugMode) {
                    die("Không thể kết nối cơ sở dữ liệu: " . $e->getMessage());
                } else {
                    die("Không thể kết nối cơ sở dữ liệu. Vui lòng liên hệ quản trị viên.");
                }
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Thực thi truy vấn và trả về PDOStatement
     * @param string $sql Câu lệnh SQL
     * @param array $params Tham số cho câu lệnh chuẩn bị
     * @return PDOStatement
     */
    public static function query($sql, $params = []) {
        try {
            $stmt = self::connect()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Lỗi truy vấn: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }
    
    /**
     * Lấy toàn bộ dòng từ truy vấn
     * @param string $sql Câu lệnh SQL
     * @param array $params Tham số cho câu lệnh chuẩn bị
     * @return array
     */
    public static function fetchAll($sql, $params = []) {
        try {
            return self::query($sql, $params)->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi lấy danh sách dữ liệu: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lấy một dòng từ truy vấn
     * @param string $sql Câu lệnh SQL
     * @param array $params Tham số cho câu lệnh chuẩn bị
     * @return object|false
     */
    public static function fetchOne($sql, $params = []) {
        try {
            return self::query($sql, $params)->fetch();
        } catch (PDOException $e) {
            error_log("Lỗi lấy một dòng dữ liệu: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Thực thi truy vấn và trả về số dòng bị ảnh hưởng
     * @param string $sql Câu lệnh SQL
     * @param array $params Tham số cho câu lệnh chuẩn bị
     * @return int Số dòng bị ảnh hưởng
     */
    public static function execute($sql, $params = []) {
        try {
            return self::query($sql, $params)->rowCount();
        } catch (PDOException $e) {
            error_log("Lỗi thực thi truy vấn: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Lấy ID vừa thêm
     * @return string
     */
    public static function lastInsertId() {
        return self::connect()->lastInsertId();
    }
    
    /**
     * Bắt đầu giao dịch
     * @return bool
     */
    public static function beginTransaction() {
        return self::connect()->beginTransaction();
    }
    
    /**
     * Xác nhận giao dịch
     * @return bool
     */
    public static function commit() {
        return self::connect()->commit();
    }
    
    /**
     * Hoàn tác giao dịch
     * @return bool
     */
    public static function rollback() {
        return self::connect()->rollBack();
    }
    
    /**
     * Kiểm tra có đang trong giao dịch hay không
     * @return bool
     */
    public static function inTransaction() {
        return self::connect()->inTransaction();
    }
    
    /**
     * Đóng kết nối cơ sở dữ liệu
     */
    public static function disconnect() {
        self::$connection = null;
    }
    
    /**
     * Kiểm tra kết nối cơ sở dữ liệu
     * @return bool
     */
    public static function testConnection() {
        try {
            self::connect();
            $stmt = self::query("SELECT 1");
            return $stmt !== false;
        } catch (Exception $e) {
            error_log("Kiểm tra kết nối thất bại: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lấy thông tin kết nối cơ sở dữ liệu (phục vụ gỡ lỗi)
     * @return array
     */
    public static function getConnectionInfo() {
        if (self::$connection === null) {
            return ['status' => 'Chưa kết nối'];
        }
        
        try {
            return [
                'status' => 'Đã kết nối',
                'driver' => self::$connection->getAttribute(PDO::ATTR_DRIVER_NAME),
                'server_version' => self::$connection->getAttribute(PDO::ATTR_SERVER_VERSION),
                'client_version' => self::$connection->getAttribute(PDO::ATTR_CLIENT_VERSION),
                'connection_status' => self::$connection->getAttribute(PDO::ATTR_CONNECTION_STATUS)
            ];
        } catch (PDOException $e) {
            return ['status' => 'Lỗi', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Chuẩn hóa chuỗi cho truy vấn SQL LIKE
     * @param string $string
     * @return string
     */
    public static function escapeLike($string) {
        return str_replace(['%', '_'], ['\%', '\_'], $string);
    }
    
    /**
     * Tạo mệnh đề WHERE IN an toàn
     * @param array $values
     * @return string Chuỗi giữ chỗ cho mệnh đề IN
     */
    public static function buildInClause($values) {
        if (empty($values)) {
            return '';
        }
        return str_repeat('?,', count($values) - 1) . '?';
    }
    
    /**
     * Thực thi nhiều truy vấn trong giao dịch
     * @param array $queries Mảng ['sql' => string, 'params' => array]
     * @return bool
     */
    public static function executeTransaction($queries) {
        try {
            self::beginTransaction();
            
            foreach ($queries as $query) {
                $sql = $query['sql'];
                $params = $query['params'] ?? [];
                self::execute($sql, $params);
            }
            
            self::commit();
            return true;
            
        } catch (Exception $e) {
            if (self::inTransaction()) {
                self::rollback();
            }
            error_log("Giao dịch thất bại: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Lấy cột của bảng
     * @param string $tableName
     * @return array
     */
    public static function getTableColumns($tableName) {
        try {
            $sql = "SHOW COLUMNS FROM `$tableName`";
            $columns = self::fetchAll($sql);
            return array_map(function($col) {
                return $col->Field;
            }, $columns);
        } catch (PDOException $e) {
            error_log("Lỗi lấy danh sách cột: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Kiểm tra bảng có tồn tại không
     * @param string $tableName
     * @return bool
     */
    public static function tableExists($tableName) {
        try {
            $sql = "SHOW TABLES LIKE ?";
            $result = self::fetchOne($sql, [$tableName]);
            return $result !== false;
        } catch (PDOException $e) {
            error_log("Lỗi kiểm tra bảng tồn tại: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lấy dung lượng cơ sở dữ liệu (MB)
     * @return float
     */
    public static function getDatabaseSize() {
        try {
            $dbname = $_ENV['DB_NAME'] ?? 'job_portal';
            $sql = "SELECT 
                    SUM(data_length + index_length) / 1024 / 1024 AS size_mb
                    FROM information_schema.TABLES 
                    WHERE table_schema = ?";
            $result = self::fetchOne($sql, [$dbname]);
            return $result ? round($result->size_mb, 2) : 0;
        } catch (PDOException $e) {
            error_log("Lỗi lấy dung lượng cơ sở dữ liệu: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Tối ưu toàn bộ bảng
     * @return bool
     */
    public static function optimizeTables() {
        try {
            $dbname = $_ENV['DB_NAME'] ?? 'job_portal';
            $sql = "SELECT table_name FROM information_schema.TABLES WHERE table_schema = ?";
            $tables = self::fetchAll($sql, [$dbname]);
            
            foreach ($tables as $table) {
                $tableName = $table->table_name;
                self::query("OPTIMIZE TABLE `$tableName`");
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Lỗi tối ưu bảng: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sao lưu cơ sở dữ liệu ra file SQL
     * @param string $filepath Đường dẫn lưu file sao lưu
     * @return bool
     */
    public static function backup($filepath) {
        try {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'job_portal';
            $username = $_ENV['DB_USER'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            
            $command = sprintf(
                'mysqldump -h %s -u %s -p%s %s > %s',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($dbname),
                escapeshellarg($filepath)
            );
            
            exec($command, $output, $returnVar);
            
            return $returnVar === 0;
            
        } catch (Exception $e) {
            error_log("Lỗi sao lưu: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lấy thời gian thực thi truy vấn (phục vụ gỡ lỗi)
     * @param string $sql
     * @param array $params
     * @return array ['result' => mixed, 'time' => float]
     */
    public static function queryWithTime($sql, $params = []) {
        $start = microtime(true);
        $result = self::fetchAll($sql, $params);
        $time = microtime(true) - $start;
        
        return [
            'result' => $result,
            'time' => round($time * 1000, 2) // mili giây
        ];
    }
    
    /**
     * Ghi log truy vấn chậm (truy vấn vượt ngưỡng thời gian)
     * @param string $sql
     * @param array $params
     * @param float $threshold Ngưỡng theo giây (mặc định 1.0)
     * @return mixed
     */
    public static function queryWithLogging($sql, $params = [], $threshold = 1.0) {
        $start = microtime(true);
        $result = self::fetchAll($sql, $params);
        $time = microtime(true) - $start;
        
        if ($time > $threshold) {
            error_log(sprintf(
                "TRUY VẤN CHẬM (%.2fs): %s | Tham số: %s",
                $time,
                $sql,
                json_encode($params)
            ));
        }
        
        return $result;
    }
}

// Tự kết nối ở lần dùng đầu tiên (tùy chọn)
// Gọi Database::connect(); nếu muốn kết nối sớm.

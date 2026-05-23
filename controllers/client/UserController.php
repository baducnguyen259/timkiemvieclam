<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển phía người dùng cho luồng User.
 * - Điều phối dữ liệu đầu vào, luật nghiệp vụ và phản hồi giao diện cho người dùng.
 */
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Account.php';
require_once __DIR__ . '/../../models/Role.php';
require_once __DIR__ . '/../../models/SavedJob.php';
require_once __DIR__ . '/../../models/ForgotPassword.php';
require_once __DIR__ . '/../../helpers/Generate.php';
require_once __DIR__ . '/../../helpers/SendMail.php';
require_once __DIR__ . '/../../helpers/Security.php';
require_once __DIR__ . '/../../helpers/Csrf.php';
require_once __DIR__ . '/../../helpers/PasswordResetSession.php';

class UserController {
    private $userModel;
    private $accountModel;
    private $roleModel;
    private $savedJobModel;
    
    /**
     * Khởi tạo các model phục vụ đăng ký, đăng nhập, quên mật khẩu và danh sách việc đã lưu.
     */
    public function __construct() {
        $this->userModel = new User();
        $this->accountModel = new Account();
        $this->roleModel = new Role();
        $this->savedJobModel = new SavedJob();
    }

    // Xác định loại tài khoản cho luồng xác thực dùng chung giữa ứng viên và nhà tuyển dụng.
    /**
     * Xác định email thuộc ứng viên hay nhà tuyển dụng để đặt lại mật khẩu đúng bảng dữ liệu.
     */
    private function resolvePasswordOwnerByEmail($email) {
        $user = $this->userModel->findByEmail($email);
        if ($user) {
            return [
                'type' => 'user',
                'email' => $user->email,
                'user' => $user,
                'account' => null
            ];
        }

        $account = $this->accountModel->findByEmail($email);
        if (!$account) {
            return null;
        }

        $roleTitle = '';
        if (!empty($account->role_id)) {
            $role = $this->roleModel->findById($account->role_id);
            $roleTitle = strtolower(trim((string)($role->title ?? '')));
        }

        // /user/login dùng chung cho ứng viên và nhà tuyển dụng, không dùng cho admin.
        if ($roleTitle !== 'employer') {
            return null;
        }

        return [
            'type' => 'account',
            'email' => $account->email,
            'user' => null,
            'account' => $account
        ];
    }
    
    // Tuyến GET: /user/register
    /**
     * Hiển thị form đăng ký tài khoản ứng viên hoặc nhà tuyển dụng.
     */
    public function register() {
        $pageTitle = "Đăng ký tài khoản";
        require_once __DIR__ . '/../../views/client/user/register.php';
    }
    
    // Tuyến POST: /user/register
    /**
     * Xử lý đăng ký, validate dữ liệu, tạo tài khoản đúng loại và đăng nhập ngay sau khi tạo.
     */
    public function registerPost() {
        // Kiểm tra dữ liệu
        $errors = [];
        $accountType = $_POST['accountType'] ?? 'candidate';
        $allowedAccountTypes = ['candidate', 'employer'];
        
        if (!in_array($accountType, $allowedAccountTypes, true)) {
            $errors[] = 'Loại tài khoản không hợp lệ';
        }
        
        if (empty($_POST['fullName'])) {
            $errors[] = 'Tên không được để trống';
        }
        
        if (empty($_POST['email'])) {
            $errors[] = 'Email không được để trống';
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        }
        
        if (empty($_POST['password'])) {
            $errors[] = 'Mật khẩu không được để trống';
        } elseif (strlen($_POST['password']) < 8) {
            $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự';
        }

        if (empty($_POST['confirmPassword'])) {
            $errors[] = 'Xác nhận mật khẩu không được để trống';
        } elseif (($_POST['password'] ?? '') !== $_POST['confirmPassword']) {
            $errors[] = 'Mật khẩu xác nhận không khớp';
        }

        if (($_POST['acceptPolicy'] ?? '') !== '1') {
            $errors[] = 'Bạn cần đồng ý với điều khoản và chính sách';
        }
        
        if (!empty($errors)) {
        }
        
        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(', ', $errors);
            header('Location: ' . BASE_PATH . '/user/register');
            exit;
        }
        
        // Kiểm tra email đã tồn tại theo loại tài khoản đã chọn
        $existingEmployer = $this->accountModel->findByEmail($_POST['email']);
        $existingUser = $this->userModel->findByEmail($_POST['email']);
        if ($existingEmployer || $existingUser) {
            $_SESSION['flash_error'] = 'Email đã được sử dụng';
            header('Location: ' . BASE_PATH . '/user/register');
            exit;
        }
        
        try {
            $fullName = trim($_POST['fullName']);
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);

            if ($accountType === 'employer') {
                $employerRole = $this->roleModel->findByTitle('Employer');
                if (!$employerRole) {
                    throw new Exception('Không tìm thấy vai trò nhà tuyển dụng');
                }

                $accountData = [
                    'full_name' => $fullName,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'role_id' => (int)$employerRole->id,
                    'status' => 'active'
                ];

                $account = $this->accountModel->create($accountData);

                // Xóa cookie candidate nếu có
                Security::clearCookie('tokenUser');

                // Tách riêng cổng admin
                Security::clearCookie('tokenAdmin');

                // Đăng nhập nhà tuyển dụng vào cổng employer
                session_regenerate_id(true);
                Csrf::rotate();
                Security::setCookie('tokenEmployer', $account['token'], Security::authCookieExpiresAt());

                $_SESSION['flash_success'] = 'Đăng ký nhà tuyển dụng thành công!';
                header('Location: ' . BASE_PATH . '/employer/dashboard');
                exit;
            }

            // Đăng ký ứng viên
            $userData = [
                'full_name' => $fullName,
                'email' => $email,
                'password' => $hashedPassword
            ];
            
            $user = $this->userModel->create($userData);
            
            // Xóa cookie admin/employer nếu có
            Security::clearCookie('tokenAdmin');
            Security::clearCookie('tokenEmployer');

            // Thiết lập cookie với cờ bảo mật
            session_regenerate_id(true);
            Csrf::rotate();
            Security::setCookie('tokenUser', $user['token_user'], Security::authCookieExpiresAt());
            
            $_SESSION['flash_success'] = 'Đăng ký thành công!';
            header('Location: ' . BASE_PATH . '/');
            exit;
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra, vui lòng thử lại';
            header('Location: ' . BASE_PATH . '/user/register');
            exit;
        }
    }
    
    // Tuyến GET: /user/login
    /**
     * Hiển thị form đăng nhập dùng chung cho ứng viên và nhà tuyển dụng.
     */
    public function login() {
        $pageTitle = "Đăng nhập";
        require_once __DIR__ . '/../../views/client/user/login.php';
    }
    
    // Tuyến POST: /user/login
    /**
     * Xử lý đăng nhập hợp nhất, điều hướng theo vai trò và gộp danh sách việc đã lưu nếu cần.
     */
    public function loginPost() {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Kiểm tra dữ liệu
        if (empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Vui lòng nhập đầy đủ thông tin';
            header('Location: ' . BASE_PATH . '/user/login');
            exit;
        }
        
        if (strlen($password) < 8) {
            $_SESSION['flash_error'] = 'Mật khẩu phải có ít nhất 8 ký tự';
            header('Location: ' . BASE_PATH . '/user/login');
            exit;
        }
        
        // Đăng nhập hợp nhất: nếu email tồn tại trong bảng accounts thì điều hướng theo vai trò.
        $user = $this->userModel->findByEmail($email);
        $account = $this->accountModel->findByEmail($email);
        if ($account && !$user) {
            if (password_verify($password, $account->password)) {
                if (($account->status ?? '') !== 'active') {
                    $_SESSION['flash_error'] = 'Tài khoản đã bị khóa';
                    header('Location: ' . BASE_PATH . '/user/login');
                    exit;
                }

                $role = null;
                if (!empty($account->role_id)) {
                    $role = $this->roleModel->findById($account->role_id);
                }

                $roleTitle = strtolower(trim((string)($role->title ?? '')));

                if ($roleTitle === 'employer') {
                    // Tách riêng phiên đăng nhập theo từng cổng.
                    $rotatedToken = $this->accountModel->rotateToken($account->id);
                    session_regenerate_id(true);
                    Csrf::rotate();

                    Security::clearCookie('tokenUser');
                    Security::clearCookie('tokenAdmin');
                    Security::setCookie('tokenEmployer', $rotatedToken, Security::authCookieExpiresAt());

                    $_SESSION['flash_success'] = 'Đăng nhập nhà tuyển dụng thành công!';
                    header('Location: ' . BASE_PATH . '/employer/dashboard');
                    exit;
                }

                if ($roleTitle === 'admin') {
                    $_SESSION['flash_error'] = 'Tài khoản quản trị vui lòng đăng nhập tại cổng quản trị';
                    header('Location: ' . BASE_PATH . '/admin/auth/login');
                    exit;
                }

                $_SESSION['flash_error'] = 'Tài khoản này không thuộc cổng người dùng';
                header('Location: ' . BASE_PATH . '/user/login');
                exit;
            }
            // Fix #11: Account tồn tại nhưng sai mật khẩu → trả lỗi ngay, không rơi xuống kiểm tra $user
            $_SESSION['flash_error'] = 'Mật khẩu không đúng';
            header('Location: ' . BASE_PATH . '/user/login');
            exit;
        }

        // Fix #10: Bỏ truy vấn $user thừa — đã lấy ở trên rồi
        if (!$account && !$user) {
            $_SESSION['flash_error'] = 'Email không tồn tại';
            header('Location: ' . BASE_PATH . '/user/login');
            exit;
        }
        
        // SỬA BẢO MẬT: Dùng password_verify thay cho MD5
        if (!$user || !password_verify($password, $user->password)) {
            $_SESSION['flash_error'] = 'Mật khẩu không đúng';
            header('Location: ' . BASE_PATH . '/user/login');
            exit;
        }
        
        if ($user->status !== 'active') {
            $_SESSION['flash_error'] = 'Tài khoản đã bị khóa';
            header('Location: ' . BASE_PATH . '/user/login');
            exit;
        }
        
        // SỬA LỖI: Gộp danh sách lưu ẩn danh vào danh sách lưu của người dùng
        $userSavedJob = $this->savedJobModel->findByUserId($user->id);
        
        if (isset($_COOKIE['saveJobId'])) {
            $anonymousSavedJob = $this->savedJobModel->findBySessionId($_COOKIE['saveJobId']);
            
            if ($anonymousSavedJob) {
                if ($userSavedJob) {
                    // Gộp việc đã lưu ẩn danh vào danh sách lưu của người dùng
                    if ((int)$anonymousSavedJob->id !== (int)$userSavedJob->id) {
                        $this->savedJobModel->mergeJobs($anonymousSavedJob->id, $userSavedJob->id);
                    }
                    Security::setCookie('saveJobId', $userSavedJob->session_id, Security::persistentCookieExpiresAt());
                } else {
                    // Cập nhật việc lưu ẩn danh với user ID
                    $this->savedJobModel->updateUserId($anonymousSavedJob->session_id, $user->id);
                }
            }
        } elseif ($userSavedJob) {
            Security::setCookie('saveJobId', $userSavedJob->session_id, Security::persistentCookieExpiresAt());
        }
        
        // Thiết lập cookie token người dùng
        $rotatedToken = $this->userModel->rotateToken($user->id);
        session_regenerate_id(true);
        Csrf::rotate();

        Security::clearCookie('tokenEmployer');
        Security::clearCookie('tokenAdmin');
        Security::setCookie('tokenUser', $rotatedToken, Security::authCookieExpiresAt());
        
        $_SESSION['flash_success'] = 'Đăng nhập thành công!';
        header('Location: ' . BASE_PATH . '/');
        exit;
    }
    
        // Route POST: /user/logout
    /**
     * Đăng xuất ứng viên, thu hồi token, xóa cookie liên quan và xoay CSRF token.
     */
    public function logout() {
        $token = $_COOKIE['tokenUser'] ?? '';
        if (is_string($token) && $token !== '') {
            $this->userModel->revokeTokenByRawToken($token);
        }

        Security::clearCookie('tokenUser');
        Security::clearCookie('saveJobId');
        session_regenerate_id(true);
        Csrf::rotate();
        
        $_SESSION['flash_success'] = 'Đăng xuất thành công!';
        header('Location: ' . BASE_PATH . '/');
        exit;
    }
    
    // Tuyến GET: /user/password/forgot
    /**
     * Hiển thị form nhập email để bắt đầu quy trình lấy lại mật khẩu.
     */
    public function forgotPassword() {
        $pageTitle = "Lấy lại mật khẩu";
        require_once __DIR__ . '/../../views/client/user/forgot-password.php';
    }
    
    // Tuyến POST: /user/password/forgot
    /**
     * Tạo OTP đặt lại mật khẩu, lưu database và gửi email cho tài khoản hợp lệ.
     */
    public function forgotPasswordPost() {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $_SESSION['flash_error'] = 'Email không được để trống';
            header('Location: ' . BASE_PATH . '/user/password/forgot');
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Email không hợp lệ';
            header('Location: ' . BASE_PATH . '/user/password/forgot');
            exit;
        }

        $owner = $this->resolvePasswordOwnerByEmail($email);
        
        if (!$owner) {
            $_SESSION['flash_error'] = 'Email không tồn tại';
            header('Location: ' . BASE_PATH . '/user/password/forgot');
            exit;
        }
        
        try {
            // Tạo OTP
            $otp = Generate::randomNumber(8);
            
            // Lưu vào database
            $forgotPasswordModel = new ForgotPassword();
            $forgotPasswordModel->create($email, $otp);
            
            // Gửi email
            $subject = "Mã OTP lấy lại mật khẩu";
            $html = "<p>Mã OTP của bạn là: <b>$otp</b></p><p>Mã OTP có hiệu lực trong vòng 3 phút</p>";
            $isSent = SendMail::send($email, $subject, $html);
            if (!$isSent) {
                $_SESSION['flash_error'] = 'Không thể gửi OTP. Vui lòng kiểm tra cấu hình email hệ thống.';
                header('Location: ' . BASE_PATH . '/user/password/forgot');
                exit;
            }
            
            $_SESSION['flash_success'] = 'Mã OTP đã được gửi đến email của bạn';
            header("Location: " . BASE_PATH . "/user/password/otp?email=" . urlencode($email));
            exit;
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra, vui lòng thử lại';
            header('Location: ' . BASE_PATH . '/user/password/forgot');
            exit;
        }
    }
    
    // Tuyến GET: /user/password/otp
    /**
     * Hiển thị form nhập OTP sau khi email đặt lại mật khẩu đã được xác nhận.
     */
    public function otpPassword() {
        $email = trim($_GET['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Email không hợp lệ';
            header('Location: ' . BASE_PATH . '/user/password/forgot');
            exit;
        }
        $pageTitle = "Nhập mã OTP";
        require_once __DIR__ . '/../../views/client/user/otp-password.php';
    }
    
    // Tuyến POST: /user/password/otp
    /**
     * Xác minh OTP và tạo phiên password_reset ngắn hạn để cho phép đặt mật khẩu mới.
     */
    public function otpPasswordPost() {
        $email = trim($_POST['email'] ?? '');
        $otp = trim($_POST['otp'] ?? '');
        
        if (empty($email) || empty($otp)) {
            $_SESSION['flash_error'] = 'Vui lòng nhập đầy đủ thông tin';
            header('Location: ' . BASE_PATH . '/user/password/otp?email=' . urlencode($email));
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Email không hợp lệ';
            header('Location: ' . BASE_PATH . '/user/password/forgot');
            exit;
        }

        if (!preg_match('/^\d{8}$/', $otp)) {
            $_SESSION['flash_error'] = 'OTP phải gồm 8 chữ số';
            header('Location: ' . BASE_PATH . '/user/password/otp?email=' . urlencode($email));
            exit;
        }

        $forgotPasswordModel = new ForgotPassword();
        $result = $forgotPasswordModel->verify($email, $otp);
        
        if (!$result) {
            $_SESSION['flash_error'] = 'Mã OTP không đúng hoặc đã hết hạn';
            header('Location: ' . BASE_PATH . '/user/password/otp?email=' . urlencode($email));
            exit;
        }
        
        $owner = $this->resolvePasswordOwnerByEmail($email);
        
        if (!$owner) {
            $_SESSION['flash_error'] = 'Tài khoản không tồn tại';
            header('Location: ' . BASE_PATH . '/user/password/forgot');
            exit;
        }

        $_SESSION['password_reset'] = PasswordResetSession::payload($owner['email'], $owner['type']);
        
        header('Location: ' . BASE_PATH . '/user/password/reset');
        exit;
    }
    
    // Tuyến GET: /user/password/reset
    /**
     * Hiển thị form đặt lại mật khẩu nếu phiên password_reset còn hợp lệ.
     */
    public function resetPassword() {
        if (!PasswordResetSession::isValid($_SESSION['password_reset'] ?? null)) {
            unset($_SESSION['password_reset']);
            $_SESSION['flash_error'] = 'Phiên đặt lại mật khẩu đã hết hạn';
            header('Location: ' . BASE_PATH . '/user/password/forgot');
            exit;
        }
        $pageTitle = "Đặt lại mật khẩu";
        require_once __DIR__ . '/../../views/client/user/reset-password.php';
    }
    
    // Tuyến POST: /user/password/reset
    /**
     * Cập nhật mật khẩu mới cho ứng viên hoặc nhà tuyển dụng sau khi qua bước OTP.
     */
    public function resetPasswordPost() {
        if (!PasswordResetSession::isValid($_SESSION['password_reset'] ?? null)) {
            unset($_SESSION['password_reset']);
            $_SESSION['flash_error'] = 'Phiên đặt lại mật khẩu đã hết hạn';
            header('Location: ' . BASE_PATH . '/user/password/forgot');
            exit;
        }

        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        
        if (empty($password) || empty($confirmPassword)) {
            $_SESSION['flash_error'] = 'Vui lòng nhập đầy đủ thông tin';
            header('Location: ' . BASE_PATH . '/user/password/reset');
            exit;
        }
        
        if (strlen($password) < 8) {
            $_SESSION['flash_error'] = 'Mật khẩu phải có ít nhất 8 ký tự';
            header('Location: ' . BASE_PATH . '/user/password/reset');
            exit;
        }
        
        if ($password !== $confirmPassword) {
            $_SESSION['flash_error'] = 'Mật khẩu không khớp';
            header('Location: ' . BASE_PATH . '/user/password/reset');
            exit;
        }
        
        try {
            $email = $_SESSION['password_reset']['email'];
            $target = $_SESSION['password_reset']['target'] ?? 'user';
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            if ($target === 'account') {
                $owner = $this->resolvePasswordOwnerByEmail($email);
                if (!$owner || $owner['type'] !== 'account' || empty($owner['account'])) {
                    unset($_SESSION['password_reset']);
                    $_SESSION['flash_error'] = 'Tài khoản không tồn tại';
                    header('Location: ' . BASE_PATH . '/user/password/forgot');
                    exit;
                }

                $accountUpdated = $this->accountModel->update($owner['account']->id, ['password' => $hashedPassword]);
                if ($accountUpdated === false) {
                    throw new Exception('Không thể cập nhật mật khẩu nhà tuyển dụng');
                }
            } else {
                $user = $this->userModel->findByEmail($email);
                if (!$user) {
                    unset($_SESSION['password_reset']);
                    $_SESSION['flash_error'] = 'Tài khoản không tồn tại';
                    header('Location: ' . BASE_PATH . '/user/password/forgot');
                    exit;
                }

                $userUpdated = $this->userModel->update($user->id, ['password' => $hashedPassword]);
                // Đồng bộ cả hai bảng nếu cùng email cũng tồn tại trong accounts.
                $account = $this->accountModel->findByEmail($email);
                if ($account) {
                    $this->accountModel->update($account->id, ['password' => $hashedPassword]);
                }
                if ($userUpdated === false) {
                    throw new Exception('Không thể cập nhật mật khẩu người dùng');
                }
            }
            unset($_SESSION['password_reset']);
            
            $_SESSION['flash_success'] = 'Đặt lại mật khẩu thành công!';
            header('Location: ' . BASE_PATH . '/user/login');
            exit;
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra, vui lòng thử lại';
            header('Location: ' . BASE_PATH . '/user/password/reset');
            exit;
        }
    }
    
    // Tuyến GET: /user/info
    /**
     * Hiển thị trang thông tin tài khoản của người dùng đang đăng nhập.
     */
    public function info() {
        $pageTitle = "Thông tin tài khoản";
        require_once __DIR__ . '/../../views/client/user/info.php';
    }
}

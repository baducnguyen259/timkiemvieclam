# Cổng Việc Làm PHP

## Checklist Deploy Nhanh

1. Môi trường
- Sao chép `.env.example` thành `.env` và điền giá trị thật cho `APP_URL`, database, SMTP, và các secret.
- Đặt `APP_ENV=production` và `APP_DEBUG=false`.
- Đặt `APP_TIMEZONE=Asia/Ho_Chi_Minh` để dùng múi giờ Việt Nam.

2. Máy chủ
- Trỏ web root vào thư mục `public/`.
- Bật module Apache `rewrite` và `headers`.
- Giữ `.htaccess` hoạt động (`AllowOverride All`).

3. Phụ thuộc
- Cài PHP 8.0+ với các extension: `pdo`, `pdo_mysql`, `mbstring`, `json`.
- Chạy `composer install --no-dev --optimize-autoloader`.

4. Phân quyền
- Đảm bảo web server có quyền ghi vào `public/uploads/` và `logs/`.
- Giữ các file `.gitkeep` để thư mục upload luôn tồn tại sau khi deploy.

5. Bảo mật
- Không commit file `.env`.
- Dùng mật khẩu mạnh cho DB và SMTP.
- Bắt buộc HTTPS ở reverse proxy/web server khi chạy production.

## Ghi Chú Vận Hành

- `BASE_URL` ưu tiên lấy từ `APP_URL` trong `.env`; nếu thiếu sẽ tự nhận diện theo request headers.
- Cookie session được cấu hình với `HttpOnly`, `Secure` (khi HTTPS), và `SameSite`.
- Khi chạy production (`APP_DEBUG=false`), lỗi PHP sẽ không hiển thị cho người dùng cuối.

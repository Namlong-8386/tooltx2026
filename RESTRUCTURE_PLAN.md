# Kế hoạch tái cấu trúc Website TOOLTX2026

## 1. Cấu trúc thư mục mới đề xuất
Mục tiêu: Sắp xếp lại các file để dễ quản lý, tách biệt rõ ràng giữa logic xử lý, giao diện người dùng và trang quản trị.

```text
/toolvip
├── admin/              # Trang quản trị (Tách biệt hoàn toàn)
│   ├── login.php       # Đăng nhập admin riêng
│   ├── dashboard.php   # Tổng quan admin
│   ├── banks.php       # Quản lý ngân hàng
│   ├── keys.php        # Quản lý key (mới)
│   ├── users.php       # Quản lý người dùng (mới)
│   └── includes/       # Logic riêng cho admin
├── assets/             # Tài nguyên tĩnh
│   ├── css/            # File CSS (bao gồm hiệu ứng chuyển trang)
│   ├── js/             # File JavaScript (xử lý hiệu ứng)
│   └── images/         # Hình ảnh
├── core/               # Logic cốt lõi (Thay cho includes cũ)
│   ├── functions.php   # Hàm dùng chung
│   ├── auth.php        # Xử lý đăng nhập/phân quyền
│   ├── database.php    # Xử lý đọc/ghi JSON
│   └── icons.php       # Thư viện icon
├── data/               # Dữ liệu JSON (Giữ nguyên nhưng bảo mật hơn)
├── user/               # Trang dành cho người dùng
│   ├── dashboard.php
│   ├── buy-key.php
│   ├── deposit.php
│   └── history.php
├── index.php           # Trang chủ
├── login.php           # Đăng nhập người dùng
├── register.php        # Đăng ký người dùng
└── logout.php          # Đăng xuất
```

## 2. Tách biệt trang Admin
- **Đường dẫn riêng**: Truy cập qua `/admin/login.php` thay vì dùng chung trang đăng nhập người dùng.
- **Session riêng**: Sử dụng `$_SESSION['admin_id']` để tránh xung đột với session người dùng.
- **Middleware**: Kiểm tra quyền admin chặt chẽ hơn trong thư mục `/admin/`.

## 3. Hiệu ứng chuyển trang
- Sử dụng **Animate.css** hoặc **GSAP** kết hợp với một lớp phủ (overlay) khi chuyển trang.
- Thêm hiệu ứng **Fade In/Out** hoặc **Slide** khi người dùng nhấn vào các liên kết.
- Tích hợp vào file `assets/js/main.js` dùng chung.

## 4. Các bước thực hiện
1. Tạo các thư mục mới (`core`, `assets/css`, `assets/js`).
2. Di chuyển và cập nhật các file logic vào `core/`.
3. Cập nhật đường dẫn `require_once` trong tất cả các file.
4. Xây dựng trang đăng nhập admin riêng biệt.
5. Tích hợp thư viện hiệu ứng chuyển trang.
```

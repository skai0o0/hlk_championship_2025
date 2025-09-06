# HLK Championship 2025 - Menu System

## Tổng quan

Hệ thống menu dropdown cho trang web HLK Championship 2025, tương thích với Apache XAMPP. Menu sẽ hiển thị nội dung khác nhau dựa trên trạng thái đăng nhập của người dùng.

## Tính năng

### Khi chưa đăng nhập:
- Hiển thị nút "Đăng nhập" → chuyển đến `login.html`
- Hiển thị nút "Đăng ký" → chuyển đến `register.html`

### Khi đã đăng nhập:
- Hiển thị thông tin người dùng (tên, lớp, khối) từ database
- Avatar với chữ cái đầu của tên
- Menu các chức năng:
  - Hồ sơ cá nhân
  - Giải đấu của tôi  
  - Thông báo
  - Cài đặt
  - Đăng xuất

## Cài đặt

### 1. Yêu cầu hệ thống
- Apache server (XAMPP)
- PHP 7.4+
- MySQL/MariaDB
- Trình duyệt web hiện đại

### 2. Cấu hình database
```sql
-- Tạo database
CREATE DATABASE hlk_championship_2025;

-- Import bảng users từ file users.sql
SOURCE users.sql;
```

### 3. Cấu hình PHP
Đảm bảo file `php/db_connect.php` có thông tin kết nối đúng:
```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hlk_championship_2025";
```

### 4. Cấu trúc file
```
hlk_championship_2025/
├── index.html          # Trang chính (đã tích hợp menu.js)
├── menu.js            # File menu system chính
├── script.js          # Các script khác (theme toggle)
├── style.css          # CSS styles (đã có dropdown styles)
├── test-menu.html     # Trang test hệ thống
├── php/
│   ├── db_connect.php # Kết nối database
│   ├── session.php    # API kiểm tra login status
│   ├── logout.php     # API đăng xuất
│   ├── login.php      # Xử lý đăng nhập
│   └── register.php   # Xử lý đăng ký
└── users.sql          # Database schema
```

## Sử dụng

### 1. Khởi động XAMPP
- Khởi động Apache
- Khởi động MySQL
- Truy cập `http://localhost/hlk_championship_2025/`

### 2. Test hệ thống
- Truy cập `http://localhost/hlk_championship_2025/test-menu.html`
- Click "Test Session API" để kiểm tra kết nối PHP
- Click "Test Logout API" để kiểm tra logout functionality

### 3. Tích hợp vào các trang khác
Để sử dụng menu system trong các trang HTML khác:

```html
<!-- Thêm vào <head> -->
<script src="menu.js"></script>

<!-- Thêm vào navbar -->
<div class="profile-dropdown-container">
    <button class="navbar-btn profile-btn" onclick="toggleProfileDropdown()">
        <!-- User icon -->
    </button>
    <div class="profile-dropdown" id="profileMenu"></div>
</div>
```

## API Endpoints

### GET /php/session.php
Kiểm tra trạng thái đăng nhập và lấy thông tin user.

**Response khi đã đăng nhập:**
```json
{
    "ok": true,
    "user": {
        "name": "Nguyễn Văn A",
        "class": "12A1",
        "grade": "K31"
    }
}
```

**Response khi chưa đăng nhập:**
```json
{
    "ok": false
}
```

### POST /php/logout.php
Đăng xuất user và xóa session/cookies.

**Response:**
```json
{
    "success": true,
    "redirect": "../index.html"
}
```

## Troubleshooting

### 1. Menu không hiển thị
- Kiểm tra console browser có lỗi JavaScript không
- Đảm bảo `menu.js` đã được load
- Kiểm tra CSS có đúng structure không

### 2. API không hoạt động
- Kiểm tra Apache có đang chạy không
- Kiểm tra đường dẫn file PHP
- Kiểm tra kết nối database trong `db_connect.php`
- Xem error log Apache: `xampp/apache/logs/error.log`

### 3. Database connection error
- Kiểm tra MySQL có đang chạy không
- Kiểm tra tên database, username, password
- Đảm bảo bảng `users` đã được tạo

### 4. Session không persist
- Kiểm tra PHP session configuration
- Kiểm tra cookies có được set đúng không
- Kiểm tra domain/path của cookies

## Development

### Debug mode
Khi chạy trên localhost, menu system sẽ log debug information vào console:
```javascript
// Xem debug info
console.log('MenuManager: ...');
```

### Customization
Để tùy chỉnh menu:

1. **Thêm menu item mới:**
```javascript
// Trong renderLoggedInMenu() method
<a href="#" class="dropdown-item" onclick="menuManager.customAction(); return false;">
    <svg>...</svg>
    Custom Action
</a>
```

2. **Thay đổi API endpoint:**
```javascript
// Trong checkLoginStatusAndRender() method
const response = await fetch('./php/custom-session.php', {
    // ...
});
```

3. **Tùy chỉnh styles:**
```css
/* Trong style.css */
.dropdown-item.custom {
    /* Custom styles */
}
```

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Security

- Sử dụng prepared statements để tránh SQL injection
- Validate input data
- Sử dụng HTTPS trong production
- Set secure cookies trong production
- Implement CSRF protection

## License

© 2025 HLK Championship. All rights reserved.

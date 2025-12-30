# WebLaptop - Cửa hàng laptop đơn giản (Demo)

Đây là một dự án demo thương mại điện tử tối giản sử dụng PHP thuần, MySQL và Bootstrap. Phù hợp làm đề tài hoặc bài tập đại học.

## Tính năng
- Trang chủ hiển thị sản phẩm từ cơ sở dữ liệu
- Trang chi tiết sản phẩm với nút "Thêm vào giỏ"
- Giỏ hàng lưu trong PHP SESSION (thêm / cập nhật / xóa)
- Khu vực quản trị với đăng nhập và CRUD sản phẩm
- Schema cơ sở dữ liệu và dữ liệu mẫu (xem `database.sql` và `config/create_db.php`)

## Cấu trúc file
- `database.sql` — schema SQL và dữ liệu mẫu
- `config/create_db.php` — chạy `database.sql` và tạo tài khoản admin (username: `admin`, password: `admin123`)
- `config/database.php` — kết nối PDO
- `includes/header.php`, `includes/footer.php` — layout chung + Bootstrap
- `functions.php` — hàm tiện ích
- `index.php` — trang chủ
- `product.php` — chi tiết sản phẩm + thêm vào giỏ
- `cart.php` — xem và quản lý giỏ hàng
- `admin/` — quản trị (login, products, add/edit/delete)

## Cấu trúc DB (tóm tắt)
- `products` — id, name, description, price, image (URL), stock, created_at
- `users` — id, username, password (hashed), role

## Hướng dẫn chạy (XAMPP)
1. Sao chép thư mục vào `C:\xampp\htdocs\weblaptop` (hoặc dùng workspace hiện tại).
2. Khởi động Apache & MySQL trong XAMPP.
3. Mở trình duyệt và truy cập: `http://localhost/weblaptop/config/create_db.php` — script sẽ tạo DB, bảng, sản phẩm mẫu và tài khoản admin.
4. Truy cập `http://localhost/weblaptop` để xem trang.
5. Truy cập quản trị: `http://localhost/weblaptop/admin/login.php` (username: `admin`, password: `admin123`)

## Ghi chú & đề xuất cải tiến
- Sử dụng prepared statements (đã dùng PDO) và validation đầu vào
- Tăng cường bảo mật: CSRF, xác thực mạnh hơn, kiểm tra upload hình
- Thêm đặt hàng (orders), quy trình thanh toán, đăng ký người dùng
- Thêm phân trang, tìm kiếm, phân loại

## Cho báo cáo
- Giải thích cách triển khai giỏ hàng (SESSION với productId => qty)
- Trình bày thiết kế SQL và quan hệ
- Mô tả luồng CRUD & xác thực dành cho admin

---
Phiên bản demo, phù hợp làm bài tập hoặc ví dụ. Nếu cần thêm chức năng (checkout, tài khoản người dùng, upload hình, lọc theo danh mục, cập nhật giỏ hàng bằng Ajax), tôi có thể bổ sung.
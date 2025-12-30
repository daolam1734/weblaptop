# Giải thích chi tiết (Tiếng Việt)

Mục tiêu: Tạo website bán laptop đơn giản, phù hợp làm đồ án đại học.

1) Cơ sở dữ liệu
- `products` (id, name, description, price, image, stock, created_at): lưu thông tin sản phẩm.
- `users` (id, username, password, role): lưu admin (password được hash bằng `password_hash`).

Bạn có thể xem `database.sql` để biết chi tiết.

2) Cách chạy dự án
- Copy thư mục `weblaptop` vào `htdocs` trong XAMPP.
- Bật Apache & MySQL.
- Mở: `http://localhost/weblaptop/config/create_db.php` để tạo DB và user admin (username: admin, password: admin123).

3) Các file chính
- `config/database.php`: kết nối PDO với MySQL.
- `config/create_db.php`: chạy SQL và tạo user admin (dùng PHP để hash password an toàn).
- `includes/header.php` + `includes/footer.php`: layout, import Bootstrap, nav bar.
- `index.php`: hiển thị list sản phẩm (sử dụng `getProducts()` trong `functions.php`).
- `product.php`: chi tiết sản phẩm và form thêm vào giỏ hàng (POST sẽ thêm vào `$_SESSION['cart']`).
- `cart.php`: xem và cập nhật giỏ hàng (update quantities, xóa, hoặc clear toàn bộ). Cart lưu dưới dạng `$_SESSION['cart'] = [product_id => quantity]`.
- `admin/`: login + CRUD sản phẩm. Có `login.php`, `products.php`, `add_product.php`, `edit_product.php`, `delete_product.php`.

4) Tính năng bảo mật cơ bản
- Password được hash (bcrypt) với `password_hash`.
- DB queries dùng PDO.

5) Những điểm có thể nêu trong báo cáo đồ án
- Thiết kế schema (ER diagram đơn giản)
- Flow: người dùng xem sản phẩm -> thêm giỏ hàng -> admin quản lý sản phẩm
- Nêu các cải tiến có thể làm (checkout, orders table, hình ảnh upload, phân quyền, CSRF, XSS prevention)

6) Mở rộng (đề xuất để hoàn thiện đồ án)
- Thêm chức năng đặt hàng, bảng `orders` và `order_items`.
- Thêm đăng ký/đăng nhập user, profile, lịch sử mua hàng.
- Thực hiện upload ảnh vào `uploads/` và validate MIME type.
- Tests: unit tests cho functions, integration tests cho flow.

---
Nếu bạn muốn, tôi sẽ bổ sung:
- Hướng dẫn thuyết trình slide (mẫu),
- Mẫu báo cáo đồ án (mục lục, nội dung từng chương),
- Tính năng đặt hàng và hóa đơn PDF để làm đồ án hoàn chỉnh.


# 🏫 Faculty of Information Technology (DaiNam University)

# **XÂY DỰNG ỨNG DỤNG WEB QUẢN LÝ PHÒNG KHÁM NHA KHOA**

<p align="center">
  <img src="public\images\logo1.png" width="600">
  <img src="public\images\logo2.png" width="600">
  <img src="public\images\logo3.png" width="600">
</p>

<p align="center" style="margin-top:12px;">
  <!-- Three quick link buttons. Replace href values with your target URLs. -->
  <a href="https://www.facebook.com/DNUAIoTLab" style="display:inline-block;margin:6px 8px;padding:10px 18px;border-radius:6px;background:#7ed957;color:#0b2b00;text-decoration:none;font-weight:700;">AIOTLAB</a>
  <a href="https://dainam.edu.vn/vi/khoa-cong-nghe-thong-tin" style="display:inline-block;margin:6px 8px;padding:10px 18px;border-radius:6px;background:#1f8fe6;color:#fff;text-decoration:none;font-weight:700;">FACULTY OF INFORMATION TECHNOLOGY</a>
  <a href="https://dainam.edu.vn/vi" style="display:inline-block;margin:6px 8px;padding:10px 18px;border-radius:6px;background:#ff8a3d;color:#fff;text-decoration:none;font-weight:700;">DAINAM UNIVERSITY</a>
</p>

---

# 1. 📘 Giới thiệu

Hệ thống quản lý phòng khám nha khoa được xây dựng nhằm mô phỏng đầy đủ quy trình hoạt động thực tế của phòng khám:
- Đặt lịch khám
- Xử lý lịch hẹn bởi lễ tân
- Bác sĩ khám bệnh, ghi hồ sơ
- Tạo và thanh toán hóa đơn
- Thống kê doanh thu

Ứng dụng bao gồm 4 nhóm người dùng chính:  
**Admin – Lễ tân – Bác sĩ – Bệnh nhân**

---

# 2. 🛠 Các công nghệ được sử dụng

### **Hệ điều hành**
| macOS | Windows | Ubuntu |
|-------|----------|---------|

### **Công nghệ chính**
| PHP | HTML5 | CSS | SCSS | JavaScript|
|-----|-------|-----|-------|------------|

### **Web Server & Database**
| Apache | MySQL | XAMPP |
|--------|--------|---------|

### **Database Management Tools**
| MySQL Workbench |
|------------------|

---

# 3. 🚀 Hình ảnh các chức năng

## **Trang đăng nhập**
<p align="center">
  <img src="public\images\login.png" width="700">
</p>

## **Trang dashboard admin**
<p align="center">
  <img src="public\images\admin_dashboard.png" width="700">
</p>

## **Trang dashboard lễ tân**
<p align="center">
  <img src="public\images\reception.png" width="700">
</p>

## **Trang dashboard nha sĩ**
<p align="center">
  <img src="public\images\dentist.png" width="700">
</p>

## **Trang dashboard bệnh nhân**
<p align="center">
  <img src="public\images\user.png" width="700">
</p>

---

# 4. ⚙ Cài đặt

## **4.1 Cài đặt công cụ, môi trường cần thiết**

### ✔ Cài XAMPP
https://www.apachefriends.org/download.html

### ✔ Cài VS Code + Extensions
- PHP Intellisense
- MySQL
- Prettier
- PHP Debug
---

## **4.2 Tải project**

```bash
cd C:\xampp\htdocs
git clone https://github.com/duytienkaka/Quan-Ly-Phong-Kham-Nha-Khoa.git
```

Truy cập:
```
http://localhost/dental_clinic
```

---

## **4.3 Setup database**

```sql
CREATE DATABASE IF NOT EXISTS dental_clinic
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

---

## **4.4 Setup tham số kết nối**

```php
<?php
function getPDO() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host = 'localhost';
    $db   = 'dental_clinic';
    $user = 'root';
    $pass = '';           
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (PDOException $e) {
        die('Lỗi kết nối database: ' . $e->getMessage());
    }
}
```

---

## **4.5 Chạy hệ thống**
Mở XAMPP Control Panel -> Start Apache và MySQL

Truy cập:
```
http://localhost/dental_clinic/
```

---

## **4.6 Đăng nhập lần đầu**
Các tài khoản mẫu để đăng nhập lần đầu (bạn có thể đổi mật khẩu sau khi đăng nhập):

- **Admin**
  - Username: `admin`  
  - Password: `123456`

- **Receptionist (Lễ tân)**
  - Username: `reception`  
  - Password: 123456`

- **Doctor (Bác sĩ)**
  - Username: `bs1`  
  - Password: `123456`

- **Patient (Bệnh nhân mẫu)**
  - Username: `patient1`  
  - Password: `123456`

Lưu ý: nếu project của bạn không có dữ liệu mẫu trong database, hãy tạo tài khoản trong giao diện Admin (`Quản lý người dùng`) hoặc import dữ liệu mẫu vào bảng `users`.

Chức năng chính theo vai trò:

- **Admin**
  - Quản lý người dùng: tạo/sửa/xóa tài khoản, phân quyền (Admin / Receptionist / Doctor / Patient).
  - Quản lý dịch vụ: thêm/sửa/xóa danh sách dịch vụ và giá.
  - Quản lý bác sĩ và lịch làm việc: thêm bác sĩ, điều chỉnh lịch khám.
  - Quản lý bệnh nhân: xem/sửa thông tin bệnh nhân, xuất dữ liệu.
  - Quản lý hóa đơn và báo cáo: xem hóa đơn, báo cáo doanh thu, xuất/import dữ liệu (backup / export).
  - Cấu hình hệ thống và sao lưu dữ liệu.

- **Receptionist (Lễ tân)**
  - Tạo, sửa, huỷ lịch hẹn cho bệnh nhân; check-in khi bệnh nhân tới.
  - Tạo và in hóa đơn, xử lý thanh toán (mark as paid) và quản lý trạng thái thanh toán.
  - Tạo bệnh nhân mới (khi là bệnh nhân lần đầu) hoặc tìm kiếm bệnh nhân cũ.
  - Quản lý danh sách lịch hẹn, phân công bác sĩ và cập nhật trạng thái lịch.

- **Doctor (Bác sĩ)**
  - Xem lịch khám theo ngày/tuần, nhận thông tin bệnh nhân đã được phân công.
  - Truy cập và ghi chép hồ sơ bệnh án (Medical Record): triệu chứng, chẩn đoán, chỉ định dịch vụ, ghi chú khám.
  - Cập nhật trạng thái khám (đã khám / đang khám / hoàn thành) và tham khảo lịch sử bệnh nhân.
  - Xem chi tiết hóa đơn liên quan đến bệnh nhân (nếu cần để tham khảo).

- **Patient (Bệnh nhân)**
  - Xem trang cá nhân, lịch sử khám, hoá đơn và lịch hẹn của chính mình.
  - Đặt lịch khám mới (booking) và hủy lịch đã đặt (theo quy định).
  - Xem chi tiết hóa đơn, lịch sử khám và hồ sơ y tế (nếu hệ thống cho phép hiển thị).

Hướng dẫn nhanh sau lần đăng nhập đầu tiên:

1. Đăng nhập bằng tài khoản `admin` → vào `Người dùng / Tài khoản` để kiểm tra hoặc tạo các tài khoản Receptionist/Doctor/Patient nếu cần.
2. Kiểm tra `Dịch vụ / Giá` và nhập hoặc điều chỉnh danh sách dịch vụ trước khi nhận lịch hẹn.
3. Receptionist sử dụng `Lễ tân` để tạo lịch hẹn và xuất hoá đơn.
4. Bác sĩ đăng nhập, mở `Bác sĩ` → xem lịch và cập nhật hồ sơ khám.

An toàn & bảo mật:

- Thay mật khẩu mặc định ngay sau lần đăng nhập đầu tiên.
- Hạn chế quyền Admin chỉ cho người quản trị thực sự.
- Sao lưu database định kỳ trước khi chạy các thao tác thay đổi lớn.

---

# 🗂 Cấu trúc thư mục

```
/project
│── /config
│── /controllers
│── /models
│── /views
│── /public
│── /database
└── README.md
```

---

# 👥 Thành viên dự án
| Họ và Tên           | Vai trò   |
|---------------------|-----------|
| Phạm Đức Duy Tiến   | Thành viên |
| Dương Văn Việt      | Thành viên |

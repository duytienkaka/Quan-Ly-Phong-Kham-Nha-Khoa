# 🦷 HỆ THỐNG QUẢN LÝ PHÒNG KHÁM NHA KHOA – README

## 📌 Giới thiệu
Hệ thống quản lý phòng khám nha khoa được xây dựng bằng **PHP MVC thuần**, **MySQL**, và sử dụng **HTML/CSS/JS** cho giao diện.  
Ứng dụng mô phỏng đầy đủ quy trình vận hành của phòng khám: **đặt lịch**, **lễ tân xử lý**, **bác sĩ khám bệnh**, **tạo & thanh toán hóa đơn**, và **thống kê doanh thu**.

Ứng dụng có 4 nhóm người dùng chính: **Admin**, **Lễ tân**, **Bác sĩ**, **Bệnh nhân**.

---

# 🎯 Mục tiêu hệ thống
- Số hóa quy trình tiếp nhận bệnh nhân.
- Tối ưu lịch hẹn, phân bổ bác sĩ hợp lý.
- Hạn chế sai sót khi ghi hồ sơ khám.
- Tự động hóa tạo hóa đơn và báo cáo doanh thu.
- Quản lý người dùng theo vai trò (Role-based Access Control).

---

# 🧩 Role hệ thống

| Role | Mô tả |
|------|-------|
| **Admin** | Quản trị toàn hệ thống, người dùng, dịch vụ, thống kê, sao lưu |
| **Lễ tân** | Tạo & quản lý lịch hẹn, gọi số thứ tự, gán bác sĩ |
| **Bác sĩ** | Khám bệnh, ghi hồ sơ, chọn dịch vụ, tạo hóa đơn |
| **Bệnh nhân** | Đăng ký, đặt lịch, xem lịch, hồ sơ khám & hóa đơn |

---

# 🗂️ Cơ sở dữ liệu chính

- `users`
- `patients`
- `doctors`
- `doctor_schedule`
- `appointments`
- `medical_records`
- `services`
- `invoices`
- `invoice_items`


---

# 🔥 1. Chức năng chi tiết theo vai trò

# 👑 I. ADMIN
## 1. Quản lý người dùng
- Tạo / sửa / xóa tài khoản  
- Phân quyền  
- Reset mật khẩu  
- Khóa / mở khóa tài khoản  

## 2. Quản lý dịch vụ
- CRUD dịch vụ  
- Import dịch vụ từ Excel  
- Kích hoạt / vô hiệu hóa dịch vụ  

## 3. Quản lý nhân sự
- Thêm bác sĩ / lễ tân  
- Gán tài khoản  
- Cập nhật thông tin  

## 4. Báo cáo – Thống kê
- Doanh thu theo ngày / tháng / tùy chọn  
- Doanh thu theo bác sĩ  
- Top dịch vụ  
- Thống kê trạng thái lịch hẹn  

## 5. Backup dữ liệu
- Xuất file SQL  
- Xuất danh sách người dùng / dịch vụ  

---

# 🛎 II. LỄ TÂN
## 1. Tạo lịch hẹn
- Tạo cho bệnh nhân có tài khoản  
- Tạo nhanh bệnh nhân mới  
- Tự sinh số thứ tự  

## 2. Quản lý lịch hẹn
- Lọc theo ngày / buổi / trạng thái  
- Tìm kiếm bệnh nhân  

## 3. Gán bác sĩ
- Hiển thị bác sĩ đang rảnh  
- Không có lịch IN_PROGRESS  
- Hoặc bác sĩ đang phụ trách lịch  

## 4. Cập nhật trạng thái
- WAITING → IN_PROGRESS → COMPLETED  
- Hủy (ghi lý do)  
- Đánh dấu NO_SHOW  

## 5. Gọi số thứ tự
- Danh sách queue theo ngày  

---

# 🩺 III. BÁC SĨ
## 1. Xem lịch
- Xem danh sách lịch được gán  

## 2. Khám bệnh
- Chief complaint  
- Clinical note  
- Diagnosis  
- Treatment plan  
- Next visit  
- Extra note  

## 3. Dịch vụ & hóa đơn
- Chọn dịch vụ  
- Nhập số lượng  
- Tính tổng tiền  
- Áp dụng giảm giá  
- Lưu invoice + invoice_items  

## 4. Lịch sử khám
- Xem toàn bộ lần khám trước  

---

# 👤 IV. BỆNH NHÂN
- Đăng ký / đăng nhập  
- Đặt lịch  
- Xem lịch hẹn  
- Hủy lịch (khi WAITING)  
- Xem hồ sơ khám & hóa đơn  

---

# 🔄 2. Flow tổng thể

## ⭐ Flow 1: Bệnh nhân đặt lịch
1. Đăng nhập  
2. Chọn ngày – buổi  
3. Tạo appointment + queue number  
4. Lễ tân xử lý  

## ⭐ Flow 2: Lễ tân xử lý
1. Kiểm tra lịch  
2. Gán bác sĩ  
3. Đưa sang IN_PROGRESS  

## ⭐ Flow 3: Bác sĩ khám bệnh
1. Ghi hồ sơ  
2. Chọn dịch vụ  
3. Tạo hóa đơn  

## ⭐ Flow 4: Hoàn tất
- Lễ tân thu tiền  
- Cập nhật hóa đơn  
- Ghi log báo cáo  

---

# 👨‍💻 Công nghệ sử dụng
- PHP (MVC)  
- MySQL  
- HTML / CSS / JS  
- XAMPP / Apache  

---

# 🧪 Hướng dẫn chạy
1. Copy source vào `htdocs/`  
2. Import database  
3. Sửa thông tin kết nối trong `config/db.php`  
4. Truy cập:
```
http://localhost/dental_clinic/
```

---

# 🗂 Cấu trúc thư mục
```
/clinic_management
│── /config
│── /controllers
│── /models
│── /views
│── /public
│── /database
└── README.md
```

---
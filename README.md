# 🦷 HỆ THỐNG QUẢN LÝ PHÒNG KHÁM NHA KHOA – README

## 📌 Giới thiệu
Hệ thống quản lý phòng khám nha khoa được xây dựng bằng **PHP – MVC thuần**, **MySQL**, và sử dụng **HTML/CSS/JS** cho giao diện.  
Ứng dụng hỗ trợ đầy đủ quy trình vận hành phòng khám thực tế từ khâu **đặt lịch hẹn**, **lễ tân xử lý**, **bác sĩ khám bệnh**, đến **tạo và thanh toán hóa đơn**, cùng với phần thống kê dành cho admin.

---

# 🎯 Mục tiêu hệ thống
- Số hóa quy trình tiếp nhận bệnh nhân.
- Tối ưu lịch khám và phân bổ bác sĩ.
- Giảm sai sót khi ghi hồ sơ khám.
- Tự động hóa việc tạo hóa đơn và theo dõi doanh thu.
- Tách biệt rõ vai trò (Role-based Access).

---

# 🧩 Cấu trúc Role của hệ thống
Hệ thống có 4 role chính:

| Role | Mô tả |
|------|-------|
| **Admin** | Quản trị toàn bộ, quản lý người dùng, dịch vụ, xem thống kê, backup |
| **Lễ tân** | Tạo & quản lý lịch hẹn, gán bác sĩ, gọi bệnh nhân theo số thứ tự |
| **Bác sĩ** | Xử lý hồ sơ khám, tạo hóa đơn, cập nhật dịch vụ |
| **Bệnh nhân** | Đặt lịch, xem lịch đã tạo, hủy lịch, xem hồ sơ khám & hóa đơn |

---

# 🗂️ Cơ sở dữ liệu chính
Hệ thống gồm các bảng:

- `users`
- `patients`
- `doctors`
- `doctor_schedule`
- `appointments`
- `medical_records`
- `medical_records_details`
- `services`
- `invoices`
- `invoice_items`

---

# 🔥 1. Chức năng chi tiết theo vai trò

# 👑 I. ADMIN

## 1. Quản lý người dùng
- Tạo / sửa / xóa
- Phân quyền (admin / lễ tân / bác sĩ / bệnh nhân)
- Reset mật khẩu
- Kích hoạt / vô hiệu hóa tài khoản

## 2. Quản lý dịch vụ
- CRUD dịch vụ nha khoa
- Import danh sách dịch vụ bằng Excel
- Quản lý trạng thái dịch vụ

## 3. Quản lý nhân sự
- Thêm bác sĩ, lễ tân
- Gán tài khoản người dùng tương ứng
- Quản lý thông tin

## 4. Báo cáo – Thống kê
- Doanh thu theo ngày / tháng / khoảng thời gian
- Doanh thu theo bác sĩ
- Top dịch vụ được sử dụng nhiều nhất
- Thống kê số lịch hẹn theo trạng thái

## 5. Backup – Sao lưu
- Tải file sao lưu SQL
- Xuất dữ liệu dịch vụ / người dùng

---

# 🧾 II. LỄ TÂN

## 1. Tạo lịch hẹn
- Tạo lịch cho bệnh nhân có tài khoản
- Nếu bệnh nhân chưa có tài khoản → tạo nhanh
- Tự động khởi tạo queue_number

## 2. Xem danh sách lịch hẹn
- Lọc theo ngày / buổi / trạng thái / từ khóa

## 3. Gán bác sĩ tự động
Chỉ hiển thị:
- Bác sĩ đang rảnh
- Bác sĩ không có lịch IN_PROGRESS
- Bác sĩ hiện tại của lịch hẹn

## 4. Cập nhật trạng thái lịch hẹn
- WAITING → IN_PROGRESS → COMPLETED  
- Hủy → nhập lý do và lưu vào ghi chú  
- Đánh dấu NO_SHOW

## 5. Gọi bệnh nhân theo số thứ tự
- Xem danh sách queue_number trong ngày

---

# 🩺 III. BÁC SĨ

## 1. Nhận danh sách lịch đã được gán

## 2. Xử lý khám bệnh
- Lý do khám
- Clinical note
- Diagnosis
- Treatment plan
- Suggested next visit
- Extra note

## 3. Dịch vụ & Hóa đơn
- Chọn dịch vụ đã làm + số lượng
- Tổng tiền tự động
- Giảm giá
- Lưu vào invoices + invoice_items

## 4. Lịch sử khám bệnh nhân
- Xem các lần khám trước

---

# 👤 IV. BỆNH NHÂN

## 1. Đăng ký / đăng nhập
## 2. Đặt lịch khám
## 3. Xem lịch hẹn
## 4. Hủy lịch (khi còn WAITING)
## 5. Xem hồ sơ khám & hóa đơn

---

# 🔄 2. Flow Tổng Thể

# ⭐ FLOW 1 – Bệnh nhân đặt lịch
1. Đăng nhập
2. Chọn ngày + buổi
3. Tạo appointment + queue_number
4. Lễ tân nhận và xử lý

---

# ⭐ FLOW 2 – Lễ tân xử lý
1. Xem lịch
2. Gán bác sĩ rảnh
3. Chuyển trạng thái → IN_PROGRESS khi bác sĩ bắt đầu khám

---

# ⭐ FLOW 3 – Bác sĩ khám bệnh
1. Ghi hồ sơ khám
2. Chọn dịch vụ & số lượng
3. Tạo hóa đơn

---

# ⭐ FLOW 4 – Hoàn tất
- Lễ tân thu tiền
- Bệnh nhân xem hóa đơn
- Hệ thống ghi log phục vụ báo cáo

---

# 👨‍💻 3. Công nghệ sử dụng
- PHP (MVC)
- MySQL
- HTML / CSS / JS
- XAMPP / Apache

---

# 🧪 4. Cách chạy dự án
1. Copy source vào `htdocs/`
2. Import database
3. Sửa file config kết nối DB
4. Truy cập:
```
http://localhost/dental_clinic/
```

---

# ✨ 5. Thành viên thực hiện
- **Phạm Đức Duy Tiến**
- **Dương Văn Việt**


-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 01:12 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dental_clinic`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `time_block` enum('MORNING','AFTERNOON','EVENING') NOT NULL,
  `queue_number` int(11) NOT NULL,
  `booking_source` enum('PREBOOKED','WALK_IN') NOT NULL,
  `status` enum('WAITING','IN_PROGRESS','COMPLETED','CANCELLED','NO_SHOW') NOT NULL DEFAULT 'WAITING',
  `note` text DEFAULT NULL,
  `cancel_reason` text DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancelled_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `doctor_id`, `appointment_date`, `time_block`, `queue_number`, `booking_source`, `status`, `note`, `cancel_reason`, `cancelled_at`, `cancelled_by`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2025-11-18', 'EVENING', 1, 'PREBOOKED', 'COMPLETED', 'Bị chảy máu chân răng', NULL, NULL, NULL, '2025-11-18 17:24:22', '2025-11-18 17:32:24'),
(2, 2, 4, '2025-11-18', 'EVENING', 2, 'PREBOOKED', 'COMPLETED', 'Nhổ răng', NULL, NULL, NULL, '2025-11-18 17:25:40', '2025-11-18 18:17:55'),
(3, 3, 5, '2025-11-18', 'EVENING', 3, 'PREBOOKED', 'COMPLETED', 'Niềng răng', NULL, NULL, NULL, '2025-11-18 17:26:08', '2025-11-18 19:01:19');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`doctor_id`, `user_id`, `specialization`, `experience_years`, `note`) VALUES
(1, 3, 'Nha sĩ tổng quát', 5, 'Bác sĩ nhiều kinh nghiệm về điều trị tổng quát'),
(2, 4, 'Chỉnh nha', 7, 'Chuyên niềng răng & hàm mặt'),
(3, 5, 'Nha khoa trẻ em', 4, 'Chuyên điều trị cho trẻ em'),
(4, 6, 'Cấy ghép Implant', 8, 'Chuyên Implant và phục hình'),
(5, 7, 'Nha chu', 6, 'Điều trị viêm nha chu & chăm sóc nướu');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_schedule`
--

CREATE TABLE `doctor_schedule` (
  `schedule_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `weekday` tinyint(4) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `note` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `final_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('UNPAID','PAID','PARTIAL') NOT NULL DEFAULT 'UNPAID',
  `payment_method` varchar(50) DEFAULT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `record_id`, `patient_id`, `created_at`, `total_amount`, `discount`, `final_amount`, `payment_status`, `payment_method`, `note`) VALUES
(4, 2, 2, '2025-11-18 18:11:11', 200000.00, 0.00, 200000.00, 'PAID', 'cash', ''),
(5, 3, 3, '2025-11-18 19:01:29', 200000.00, 0.00, 200000.00, 'PAID', 'cash', '');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `item_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`item_id`, `invoice_id`, `service_id`, `description`, `quantity`, `unit_price`, `line_total`) VALUES
(1, 4, 4, NULL, 1, 200000.00, 200000.00),
(2, 5, 7, NULL, 1, 200000.00, 200000.00);

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `record_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `visit_date` datetime NOT NULL,
  `chief_complaint` varchar(255) DEFAULT NULL,
  `clinical_note` text DEFAULT NULL,
  `diagnosis` varchar(255) DEFAULT NULL,
  `treatment_plan` text DEFAULT NULL,
  `extra_note` text DEFAULT NULL,
  `suggested_next_visit` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_records`
--

INSERT INTO `medical_records` (`record_id`, `appointment_id`, `patient_id`, `doctor_id`, `visit_date`, `chief_complaint`, `clinical_note`, `diagnosis`, `treatment_plan`, `extra_note`, `suggested_next_visit`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, '2025-11-18 17:29:26', 'Khám tổng quát', 'Chảy máu chân răng, Nứt tủy', 'Viêm tủy', 'Nhổ răng', 'Kiêng đồ cay nóng, cứng. Ăn cháo 1 tuần', '2025-11-25', '2025-11-18 17:29:26', '2025-11-18 17:32:24'),
(2, 2, 2, 4, '2025-11-18 17:35:13', 'Đau răng', 'Sâu răng', 'Sâu răng', 'Nhổ răng sâu', 'Kiêng đồ cay nóng', NULL, '2025-11-18 17:35:13', '2025-11-18 18:17:55'),
(3, 3, 3, 5, '2025-11-18 19:01:19', 'Lấy cao răng', '', 'Viêm lợi', '', '', NULL, '2025-11-18 19:01:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `gender` enum('M','F','O') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `user_id`, `full_name`, `gender`, `date_of_birth`, `phone`, `email`, `address`, `note`, `created_at`, `updated_at`) VALUES
(1, 8, 'Nguyễn Anh Minh', 'M', '2000-05-12', '0912345678', 'minh@gmail.com', 'Hà Nội', '', '2025-11-18 17:19:11', NULL),
(2, 9, 'Trần Thị Hương', 'F', '1998-09-20', '0934567890', 'huong@gmail.com', 'TP. Hồ Chí Minh', 'Mẫn cảm với cafein', '2025-11-18 17:19:11', '2025-11-18 17:25:19'),
(3, 10, 'Lê Văn Khánh', 'M', '1995-03-08', '0987654321', 'khanh@gmail.com', 'Đà Nẵng', '', '2025-11-18 17:19:11', NULL),
(4, 11, 'Phạm Thu Thảo', 'F', '2001-07-14', '0971234567', 'thao@gmail.com', 'Hải Phòng', '', '2025-11-18 17:19:11', NULL),
(5, 12, 'Đỗ Đức Thành', 'M', '1997-12-02', '0967890123', 'thanh@gmail.com', 'Nam Định', '', '2025-11-18 17:19:11', NULL),
(6, 13, 'Nguyễn Thùy Linh', 'F', '2002-04-21', '0912341111', 'linh@gmail.com', 'Nghệ An', '', '2025-11-18 17:19:11', NULL),
(7, 14, 'Hoàng Văn Cường', 'M', '1996-08-30', '0932224444', 'cuong@gmail.com', 'Thanh Hóa', '', '2025-11-18 17:19:11', NULL),
(8, 15, 'Vũ Minh Châu', 'F', '1999-11-19', '0925556666', 'chau@gmail.com', 'Vĩnh Phúc', '', '2025-11-18 17:19:11', NULL),
(9, 16, 'Phạm Gia Huy', 'M', '2003-02-16', '0947778888', 'huy@gmail.com', 'Bắc Ninh', '', '2025-11-18 17:19:11', NULL),
(10, 17, 'Trịnh Mai Phương', 'F', '1994-10-01', '0959990000', 'phuong@gmail.com', 'Thái Bình', '', '2025-11-18 17:19:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `description`, `unit_price`, `unit`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Khám tổng quát', 'Khám tổng quát và tư vấn sức khỏe răng miệng', 50000.00, 'Lần', 1, '2025-11-18 17:22:53', NULL),
(2, 'Lấy cao răng', 'Lấy cao răng bằng sóng siêu âm', 150000.00, 'Lần', 1, '2025-11-18 17:22:53', NULL),
(3, 'Trám răng', 'Trám răng thẩm mỹ bằng composite', 200000.00, 'Răng', 1, '2025-11-18 17:22:53', NULL),
(4, 'Nhổ răng thường', 'Nhổ răng không phẫu thuật', 200000.00, 'Răng', 1, '2025-11-18 17:22:53', NULL),
(5, 'Nhổ răng khôn', 'Nhổ răng khôn tiểu phẫu', 1200000.00, 'Răng', 1, '2025-11-18 17:22:53', NULL),
(6, 'Chụp X-quang răng', 'Chụp phim X-quang toàn hàm hoặc từng vùng', 80000.00, 'Lần', 1, '2025-11-18 17:22:53', NULL),
(7, 'Cạo vôi + đánh bóng', 'Làm sạch vôi răng và đánh bóng toàn bộ', 200000.00, 'Lần', 1, '2025-11-18 17:22:53', NULL),
(8, 'Làm răng sứ', 'Bọc răng sứ thẩm mỹ cao cấp', 2500000.00, 'Răng', 1, '2025-11-18 17:22:53', NULL),
(9, 'Niềng răng', 'Niềng răng chỉnh nha theo phác đồ', 30000000.00, 'Lần', 1, '2025-11-18 17:22:53', NULL),
(10, 'Cấy Implant', 'Cấy ghép Implant và phục hình', 15000000.00, 'Trụ', 1, '2025-11-18 17:22:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','receptionist','doctor','patient') NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `full_name`, `phone`, `email`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', '123456', 'Quản trị hệ thống', '0901000001', 'admin@clinic.com', 'admin', 1, '2025-11-18 17:14:12', '2025-11-18 19:03:41'),
(2, 'reception', '123456', 'Nguyễn Thị Lễ Tân', '0901000002', 'reception@clinic.com', 'receptionist', 1, '2025-11-18 17:14:12', '2025-11-18 19:03:58'),
(3, 'bs1', '123456', 'BS. Trần Minh Hoàng', '0902000001', 'bs1@clinic.com', 'doctor', 1, '2025-11-18 17:14:12', '2025-11-18 19:03:58'),
(4, 'bs2', '123456', 'BS. Lê Hữu Phúc', '0902000002', 'bs2@clinic.com', 'doctor', 1, '2025-11-18 17:14:12', '2025-11-18 19:03:58'),
(5, 'bs3', '123456', 'BS. Nguyễn Văn An', '0902000003', 'bs3@clinic.com', 'doctor', 1, '2025-11-18 17:14:12', '2025-11-18 19:00:30'),
(6, 'bs4', '123456', 'BS. Võ Thành Đạt', '0902000004', 'bs4@clinic.com', 'doctor', 1, '2025-11-18 17:14:12', '2025-11-18 18:56:27'),
(7, 'bs5', '123456', 'BS. Bùi Hải Yến', '0902000005', 'bs5@clinic.com', 'doctor', 1, '2025-11-18 17:14:12', '2025-11-18 18:56:17'),
(8, 'patient1', '123456', 'Phạm Đức Long', '0910000001', 'pat1@clinic.com', 'patient', 1, '2025-11-18 17:14:12', '2025-11-18 18:56:12'),
(9, 'patient2', '123456', 'Trần Thị Hương', '0934567890', 'huong@gmail.com', 'patient', 1, '2025-11-18 17:14:12', '2025-11-18 18:56:10'),
(10, 'patient3', '123456', 'Trần Văn Huy', '0910000003', 'pat3@clinic.com', 'patient', 1, '2025-11-18 17:14:12', '2025-11-18 18:56:07'),
(11, 'patient4', '123456', 'Đỗ Quang Minh', '0910000004', 'pat4@clinic.com', 'patient', 1, '2025-11-18 17:14:12', '2025-11-18 18:56:05'),
(12, 'patient5', '123456', 'Võ Thị Kim Ngân', '0910000005', 'pat5@clinic.com', 'patient', 1, '2025-11-18 17:14:12', '2025-11-18 18:56:03'),
(13, 'patient6', '123456', 'Lê Gia Bảo', '0910000006', 'pat6@clinic.com', 'patient', 1, '2025-11-18 17:14:12', '2025-11-18 18:56:00'),
(14, 'patient7', '123456', 'Nguyễn Phương Trinh', '0910000007', 'pat7@clinic.com', 'patient', 1, '2025-11-18 17:14:12', '2025-11-18 18:55:59'),
(15, 'patient8', '123456', 'Hoàng Ngọc Hân', '0910000008', 'pat8@clinic.com', 'patient', 1, '2025-11-18 17:14:12', '2025-11-18 18:55:57'),
(16, 'patient9', '123456', 'Vũ Đình Nam', '0910000009', 'pat9@clinic.com', 'patient', 1, '2025-11-18 17:14:12', '2025-11-18 18:55:56'),
(17, 'patient10', '123456', 'Đặng Quỳnh Chi', '0910000010', 'pat10@clinic.com', 'patient', 0, '2025-11-18 17:14:12', '2025-11-18 19:06:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `fk_appointments_patient` (`patient_id`),
  ADD KEY `fk_appointments_doctor` (`doctor_id`),
  ADD KEY `fk_appointments_cancelled_by` (`cancelled_by`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`doctor_id`),
  ADD KEY `fk_doctors_user` (`user_id`);

--
-- Indexes for table `doctor_schedule`
--
ALTER TABLE `doctor_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `fk_sched_doctor` (`doctor_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `fk_invoices_record` (`record_id`),
  ADD KEY `fk_invoices_patient` (`patient_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `fk_invoice_items_invoice` (`invoice_id`),
  ADD KEY `fk_invoice_items_service` (`service_id`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `fk_medrec_appointment` (`appointment_id`),
  ADD KEY `fk_medrec_patient` (`patient_id`),
  ADD KEY `fk_medrec_doctor` (`doctor_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD KEY `fk_patients_user` (`user_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `doctor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `doctor_schedule`
--
ALTER TABLE `doctor_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointments_cancelled_by` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appointments_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appointments_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `fk_doctors_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `doctor_schedule`
--
ALTER TABLE `doctor_schedule`
  ADD CONSTRAINT `fk_sched_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invoices_record` FOREIGN KEY (`record_id`) REFERENCES `medical_records` (`record_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `fk_invoice_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invoice_items_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON UPDATE CASCADE;

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `fk_medrec_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medrec_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medrec_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `fk_patients_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

<?php
// controllers/AdminController.php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/database.php';

class AdminController
{
    private function requireAdminLogin()
    {
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }

    public function dashboard()
    {
        $this->requireAdminLogin();

        $pdo = getPDO();

        // Đếm tổng bệnh nhân
        $totalPatients = (int)$pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();

        // Đếm tổng bác sĩ
        $totalDoctors  = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'doctor' AND status = 1")->fetchColumn();

        // Đếm lịch hẹn (tất cả)
        $totalAppointments = (int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();

        // Tổng hóa đơn và doanh thu đã thanh toán
        $totalInvoices = (int)$pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn();

        $stmt = $pdo->query("SELECT COALESCE(SUM(final_amount),0) 
                             FROM invoices 
                             WHERE payment_status = 'PAID'");
        $totalRevenue = (float)$stmt->fetchColumn();

        // Lấy 5 lịch hẹn mới nhất để hiển thị bảng nhỏ
        $recentAppointments = $pdo->query("
            SELECT a.appointment_id, a.appointment_date, a.time_block, a.status,
                   p.full_name AS patient_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            ORDER BY a.appointment_date DESC, a.appointment_id DESC
            LIMIT 5
        ")->fetchAll();

        // Lịch hẹn hôm nay
        $todayAppointments = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date) = CURDATE()")->fetchColumn();

        // Lịch hẹn sắp tới (7 ngày)
        $upcomingAppointments = (int)$pdo->query("
            SELECT COUNT(*) FROM appointments 
            WHERE appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ")->fetchColumn();

        // Hóa đơn chưa thanh toán
        $unpaidInvoices = (int)$pdo->query("
            SELECT COUNT(*) FROM invoices 
            WHERE payment_status IS NULL OR payment_status <> 'PAID'
        ")->fetchColumn();

        // Doanh thu theo tháng (12 tháng gần nhất)
        $monthlyRevenue = $pdo->query("
            SELECT DATE_FORMAT(created_at, '%m/%Y') as month, COALESCE(SUM(final_amount), 0) as revenue
            FROM invoices
            WHERE payment_status = 'PAID' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY YEAR(created_at), MONTH(created_at)
            ORDER BY YEAR(created_at), MONTH(created_at)
        ")->fetchAll();

        // Top doctors (lịch hẹn nhiều nhất)
        $topDoctors = $pdo->query("
            SELECT u.full_name, COUNT(a.appointment_id) as count
            FROM users u
            LEFT JOIN appointments a ON u.user_id = a.doctor_id
            WHERE u.role = 'doctor' AND u.status = 1
            GROUP BY u.user_id
            ORDER BY count DESC
            LIMIT 5
        ")->fetchAll();

        // Trạng thái lịch hẹn (pie chart data)
        $appointmentStatus = $pdo->query("
            SELECT status, COUNT(*) as count
            FROM appointments
            GROUP BY status
        ")->fetchAll();

        // Bệnh nhân mới (7 ngày gần đây)
        $newPatients = $pdo->query("
            SELECT p.full_name, p.created_at
            FROM patients p
            WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY p.created_at DESC
            LIMIT 5
        ")->fetchAll();

        $pageTitle = 'Bảng điều khiển Admin';
        $view      = __DIR__ . '/../views/admin/dashboard.php';

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }
    public function users()
    {
        $this->requireAdminLogin();

        $pdo = getPDO();

        // Lấy filter từ GET
        $keyword = trim($_GET['q'] ?? '');
        $role    = $_GET['role'] ?? '';
        $status  = $_GET['status'] ?? '';
        $page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $pageSize = 10;

        // Xây WHERE + params
        $where  = "1=1";
        $params = [];

        if ($keyword !== '') {
            $where .= " AND (username LIKE :kw1 OR full_name LIKE :kw2 OR email LIKE :kw3)";
            $like = '%' . $keyword . '%';
            $params['kw1'] = $like;
            $params['kw2'] = $like;
            $params['kw3'] = $like;
        }

        if ($role !== '') {
            $where .= " AND role = :role";
            $params['role'] = $role;
        }

        if ($status !== '') {
            $where .= " AND status = :status";
            $params['status'] = (int)$status;  // 0 hoặc 1
        }

        // ---------- ĐẾM TỔNG ROWS ----------
        $sqlCount = "SELECT COUNT(*) FROM users WHERE $where";
        $stmt = $pdo->prepare($sqlCount);
        $stmt->execute($params);   // chỉ truyền đúng $params ở trên
        $totalRows  = (int)$stmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalRows / $pageSize));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $pageSize;

        // ---------- LẤY DATA ----------
        $sqlData = "
        SELECT user_id, username, full_name, email, phone, role, status, created_at
        FROM users
        WHERE $where
        ORDER BY created_at DESC, user_id DESC
        LIMIT :limit OFFSET :offset
    ";

        $stmt = $pdo->prepare($sqlData);

        // bind các param filter
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }

        // bind limit/offset (số nguyên)
        $stmt->bindValue(':limit',  $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);

        $stmt->execute();
        $users = $stmt->fetchAll();

        // ---------- ĐẨY DỮ LIỆU RA VIEW ----------
        $pageTitle   = 'Quản lý tài khoản';
        $view        = __DIR__ . '/../views/admin/users.php';

        $currentPage = $page;
        $keywordView = $keyword;
        $roleView    = $role;
        $statusView  = $status;

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }

    public function createUser()
    {
        $this->requireAdminLogin();

        $pdo   = getPDO();
        $error = '';
        $old   = [
            'username'   => '',
            'full_name'  => '',
            'email'      => '',
            'phone'      => '',
            'role'       => 'patient',
            'status'     => 1,
            'password'   => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username  = trim($_POST['username'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $email     = trim($_POST['email'] ?? '');
            $phone     = trim($_POST['phone'] ?? '');
            $role      = $_POST['role'] ?? 'patient';
            $status    = isset($_POST['status']) ? (int)$_POST['status'] : 1;
            $password  = $_POST['password'] ?? '';

            // Lưu lại dữ liệu cũ để fill vào form nếu có lỗi
            $old = compact('username', 'full_name', 'email', 'phone', 'role', 'status', 'password');

            // Validate đơn giản
            if ($username === '' || $full_name === '' || $password === '') {
                $error = 'Vui lòng nhập đầy đủ Tên đăng nhập, Họ tên và Mật khẩu.';
            } else {
                // Kiểm tra trùng username
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :u");
                $stmt->execute(['u' => $username]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Tên đăng nhập đã tồn tại.';
                } else {
                    // TẠM THỜI lưu mật khẩu thô
                    $stmt = $pdo->prepare("
                    INSERT INTO users (username, password_hash, full_name, phone, email, role, status, created_at)
                    VALUES (:username, :password_hash, :full_name, :phone, :email, :role, :status, NOW())
                ");
                    $stmt->execute([
                        'username'      => $username,
                        'password_hash' => $password,  // về sau đổi thành password_hash(...)
                        'full_name'     => $full_name,
                        'phone'         => $phone,
                        'email'         => $email,
                        'role'          => $role,
                        'status'        => $status,
                    ]);

                    // Xong thì quay về danh sách user
                    header('Location: index.php?controller=admin&action=users');
                    exit;
                }
            }
        }

        $pageTitle = 'Thêm tài khoản mới';
        $view      = __DIR__ . '/../views/admin/user_form.php';

        // Truyền $error, $old cho view qua scope include
        include __DIR__ . '/../views/layouts/admin_layout.php';
    }
    public function editUser()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo "ID không hợp lệ.";
            exit;
        }

        // Lấy dữ liệu user hiện tại
        $stmt = $pdo->prepare("
        SELECT user_id, username, full_name, email, phone, role, status
        FROM users
        WHERE user_id = :id
    ");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        if (!$user) {
            echo "Không tìm thấy tài khoản.";
            exit;
        }

        $error = '';
        $old   = $user; // dùng $old cho form như createUser

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username  = trim($_POST['username'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $email     = trim($_POST['email'] ?? '');
            $phone     = trim($_POST['phone'] ?? '');
            $role      = $_POST['role'] ?? $user['role'];
            $status    = isset($_POST['status']) ? (int)$_POST['status'] : $user['status'];

            $old = compact('username', 'full_name', 'email', 'phone', 'role', 'status');
            $old['user_id'] = $id;

            if ($username === '' || $full_name === '') {
                $error = 'Vui lòng nhập đầy đủ Tên đăng nhập và Họ tên.';
            } else {
                // Kiểm tra trùng username với người khác
                $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM users
                WHERE username = :u AND user_id <> :id
            ");
                $stmt->execute(['u' => $username, 'id' => $id]);

                if ($stmt->fetchColumn() > 0) {
                    $error = 'Tên đăng nhập đã được dùng bởi tài khoản khác.';
                } else {
                    // Cập nhật
                    $stmt = $pdo->prepare("
                    UPDATE users
                    SET username = :username,
                        full_name = :full_name,
                        email = :email,
                        phone = :phone,
                        role = :role,
                        status = :status
                    WHERE user_id = :id
                ");
                    $stmt->execute([
                        'username'  => $username,
                        'full_name' => $full_name,
                        'email'     => $email,
                        'phone'     => $phone,
                        'role'      => $role,
                        'status'    => $status,
                        'id'        => $id,
                    ]);

                    header('Location: index.php?controller=admin&action=users');
                    exit;
                }
            }
        }

        $pageTitle = 'Chỉnh sửa tài khoản';
        $view      = __DIR__ . '/../views/admin/user_form.php';
        $mode      = 'edit';  // báo cho view biết đang edit

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }
    public function toggleUserStatus()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=admin&action=users');
            exit;
        }

        // Không nên tự khóa chính mình (optional)
        if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            echo "Không thể tự khóa tài khoản của chính mình.";
            exit;
        }

        $stmt = $pdo->prepare("SELECT status FROM users WHERE user_id = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        if (!$user) {
            header('Location: index.php?controller=admin&action=users');
            exit;
        }

        $newStatus = ($user['status'] == 1) ? 0 : 1;

        $stmt = $pdo->prepare("UPDATE users SET status = :s WHERE user_id = :id");
        $stmt->execute(['s' => $newStatus, 'id' => $id]);

        header('Location: index.php?controller=admin&action=users');
        exit;
    }
    public function importUsers()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=admin&action=users');
            exit;
        }

        if (empty($_FILES['csv_file']['tmp_name'])) {
            echo "Chưa chọn file CSV.";
            exit;
        }

        $filePath = $_FILES['csv_file']['tmp_name'];

        if (!is_uploaded_file($filePath)) {
            echo "File upload không hợp lệ.";
            exit;
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            echo "Không mở được file.";
            exit;
        }

        $total   = 0;
        $importOk = 0;
        $skipped = 0;
        $errors  = [];

        // Đọc dòng đầu tiên, giả sử là header
        $header = fgetcsv($handle, 1000, ",");

        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
            $total++;

            // Kỳ vọng: username, full_name, email, phone, role, status, password
            if (count($row) < 7) {
                $skipped++;
                $errors[] = "Dòng $total: không đủ cột.";
                continue;
            }

            $username  = trim($row[0]);
            $full_name = trim($row[1]);
            $email     = trim($row[2]);
            $phone     = trim($row[3]);
            $role      = trim($row[4]);
            $status    = (int)trim($row[5]);
            $password  = trim($row[6]);

            if ($username === '' || $full_name === '' || $password === '') {
                $skipped++;
                $errors[] = "Dòng $total: thiếu username/full_name/password.";
                continue;
            }

            // Role hợp lệ
            $validRoles = ['admin', 'receptionist', 'doctor', 'patient'];
            if (!in_array($role, $validRoles, true)) {
                $skipped++;
                $errors[] = "Dòng $total: role không hợp lệ ($role).";
                continue;
            }

            // Check trùng username
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :u");
            $stmt->execute(['u' => $username]);
            if ($stmt->fetchColumn() > 0) {
                $skipped++;
                $errors[] = "Dòng $total: username đã tồn tại ($username).";
                continue;
            }

            // Insert
            $stmt = $pdo->prepare("
            INSERT INTO users (username, password_hash, full_name, phone, email, role, status, created_at)
            VALUES (:username, :password_hash, :full_name, :phone, :email, :role, :status, NOW())
        ");
            $stmt->execute([
                'username'      => $username,
                'password_hash' => $password, // tạm thời lưu thô
                'full_name'     => $full_name,
                'phone'         => $phone,
                'email'         => $email,
                'role'          => $role,
                'status'        => $status,
            ]);

            $importOk++;
        }

        fclose($handle);

        // Hiển thị kết quả đơn giản
        echo "<h2>Kết quả import</h2>";
        echo "<p>Tổng dòng đọc: $total</p>";
        echo "<p>Thêm thành công: $importOk</p>";
        echo "<p>Bỏ qua: $skipped</p>";

        if (!empty($errors)) {
            echo "<h3>Chi tiết lỗi:</h3><ul>";
            foreach ($errors as $e) {
                echo "<li>" . htmlspecialchars($e) . "</li>";
            }
            echo "</ul>";
        }

        echo '<p><a href="index.php?controller=admin&action=users">Quay lại danh sách tài khoản</a></p>';
    }
    public function doctors()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $keyword    = trim($_GET['q'] ?? '');
        $speciality = trim($_GET['speciality'] ?? '');
        $status     = $_GET['status'] ?? '';
        $page       = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $pageSize   = 10;

        $where  = "u.role = 'doctor'";
        $params = [];

        // Tìm theo tên bác sĩ (users.full_name)
        if ($keyword !== '') {
            $where .= " AND u.full_name LIKE :kw";
            $params['kw'] = '%' . $keyword . '%';
        }

        // Lọc theo chuyên khoa (doctors.specialization)
        if ($speciality !== '') {
            $where .= " AND d.specialization LIKE :spec";
            $params['spec'] = '%' . $speciality . '%';
        }

        // Lọc theo trạng thái (users.status)
        if ($status !== '') {
            $where .= " AND u.status = :st";
            $params['st'] = (int)$status;
        }

        // Đếm tổng
        $sqlCount = "
        SELECT COUNT(*)
        FROM users u
        LEFT JOIN doctors d ON d.user_id = u.user_id
        WHERE $where
    ";
        $stmt = $pdo->prepare($sqlCount);
        $stmt->execute($params);
        $totalRows  = (int)$stmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalRows / $pageSize));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $pageSize;

        // Lấy dữ liệu
        $sqlData = "
        SELECT
            u.user_id,
            u.username,
            u.full_name AS doctor_name,
            u.email,
            u.phone,
            u.status,
            u.created_at,

            d.doctor_id,
            d.specialization,
            d.experience_years,
            d.note
        FROM users u
        LEFT JOIN doctors d ON d.user_id = u.user_id
        WHERE $where
        ORDER BY u.created_at DESC, u.user_id DESC
        LIMIT :limit OFFSET :offset
    ";

        $stmt = $pdo->prepare($sqlData);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit',  $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
        $stmt->execute();

        $doctors = $stmt->fetchAll();

        $pageTitle      = 'Quản lý bác sĩ';
        $view           = __DIR__ . '/../views/admin/doctors.php';
        $currentPage    = $page;
        $keywordView    = $keyword;
        $specialityView = $speciality;
        $statusView     = $status;
        $totalPagesView = $totalPages;

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }


    public function editDoctor()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        // Lấy từ uid (user_id) trên URL
        $userId = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
        if ($userId <= 0) {
            echo "User ID không hợp lệ.";
            exit;
        }

        // Lấy info user + doctors (LEFT JOIN)
        $stmt = $pdo->prepare("
        SELECT
            u.user_id,
            u.username,
            u.full_name AS doctor_name,
            u.email,
            u.phone,

            d.doctor_id,
            d.specialization,
            d.experience_years,
            d.note
        FROM users u
        LEFT JOIN doctors d ON d.user_id = u.user_id
        WHERE u.user_id = :uid
          AND u.role = 'doctor'
    ");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch();

        if (!$row) {
            echo "Không tìm thấy tài khoản bác sĩ.";
            exit;
        }

        $error = '';
        $old   = [
            'user_id'          => $row['user_id'],
            'username'         => $row['username'],
            'doctor_name'      => $row['doctor_name'],
            'email'            => $row['email'],
            'phone'            => $row['phone'],
            'doctor_id'        => $row['doctor_id'],
            'specialization'   => $row['specialization'],
            'experience_years' => $row['experience_years'],
            'note'             => $row['note'],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $specialization   = trim($_POST['specialization'] ?? '');
            $experience_years = trim($_POST['experience_years'] ?? '');
            $note             = trim($_POST['note'] ?? '');

            $old['specialization']   = $specialization;
            $old['experience_years'] = $experience_years;
            $old['note']             = $note;

            $exp = ($experience_years === '') ? null : (int)$experience_years;

            if ($row['doctor_id']) {
                // UPDATE
                $stmt = $pdo->prepare("
                UPDATE doctors
                SET specialization   = :spec,
                    experience_years = :exp,
                    note            = :note
                WHERE doctor_id = :id
            ");
                $stmt->execute([
                    'spec' => $specialization ?: null,
                    'exp'  => $exp,
                    'note' => $note ?: null,
                    'id'   => $row['doctor_id'],
                ]);
            } else {
                // INSERT mới
                $stmt = $pdo->prepare("
                INSERT INTO doctors (user_id, specialization, experience_years, note)
                VALUES (:uid, :spec, :exp, :note)
            ");
                $stmt->execute([
                    'uid'  => $userId,
                    'spec' => $specialization ?: null,
                    'exp'  => $exp,
                    'note' => $note ?: null,
                ]);
            }

            header('Location: index.php?controller=admin&action=doctors');
            exit;
        }

        $pageTitle = 'Cập nhật thông tin bác sĩ';
        $view      = __DIR__ . '/../views/admin/doctor_form.php';
        include __DIR__ . '/../views/layouts/admin_layout.php';
    }

    public function doctorSchedule()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $doctorId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($doctorId <= 0) {
            echo "ID bác sĩ không hợp lệ.";
            exit;
        }

        // Lấy thông tin bác sĩ + user
        $stmt = $pdo->prepare("
        SELECT d.doctor_id, u.full_name AS doctor_name
        FROM doctors d
        JOIN users u ON d.user_id = u.user_id
        WHERE d.doctor_id = :id
    ");
        $stmt->execute(['id' => $doctorId]);
        $doctor = $stmt->fetch();

        if (!$doctor) {
            echo "Không tìm thấy bác sĩ.";
            exit;
        }

        $error  = '';
        $notice = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // form gửi về 2 mảng: start_time[1..7], end_time[1..7]
            $startTimes = $_POST['start_time'] ?? [];
            $endTimes   = $_POST['end_time'] ?? [];
            $notes      = $_POST['note'] ?? [];

            try {
                $pdo->beginTransaction();

                // xử lý 7 ngày trong tuần
                for ($day = 1; $day <= 7; $day++) {
                    $st = trim($startTimes[$day] ?? '');
                    $et = trim($endTimes[$day] ?? '');
                    $nt = trim($notes[$day] ?? '');

                    if ($st === '' || $et === '') {
                        // nếu để trống => xóa lịch ngày đó (nếu có)
                        $del = $pdo->prepare("
                        DELETE FROM doctor_schedule
                        WHERE doctor_id = :doc AND weekday = :w
                    ");
                        $del->execute(['doc' => $doctorId, 'w' => $day]);
                    } else {
                        // kiểm tra đã có record chưa
                        $check = $pdo->prepare("
                        SELECT schedule_id
                        FROM doctor_schedule
                        WHERE doctor_id = :doc AND weekday = :w
                    ");
                        $check->execute(['doc' => $doctorId, 'w' => $day]);
                        $row = $check->fetch();

                        if ($row) {
                            // update
                            $upd = $pdo->prepare("
                            UPDATE doctor_schedule
                            SET start_time = :st,
                                end_time   = :et,
                                note       = :nt
                            WHERE schedule_id = :id
                        ");
                            $upd->execute([
                                'st' => $st,
                                'et' => $et,
                                'nt' => $nt ?: null,
                                'id' => $row['schedule_id'],
                            ]);
                        } else {
                            // insert
                            $ins = $pdo->prepare("
                            INSERT INTO doctor_schedule (doctor_id, weekday, start_time, end_time, note)
                            VALUES (:doc, :w, :st, :et, :nt)
                        ");
                            $ins->execute([
                                'doc' => $doctorId,
                                'w'   => $day,
                                'st'  => $st,
                                'et'  => $et,
                                'nt'  => $nt ?: null,
                            ]);
                        }
                    }
                }

                $pdo->commit();
                $notice = 'Đã lưu lịch làm việc.';
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Lỗi lưu lịch: ' . $e->getMessage();
            }
        }

        // Lấy lịch hiện tại
        $stmt = $pdo->prepare("
        SELECT weekday, start_time, end_time, note
        FROM doctor_schedule
        WHERE doctor_id = :doc
    ");
        $stmt->execute(['doc' => $doctorId]);
        $rows = $stmt->fetchAll();

        $schedule = [];
        foreach ($rows as $r) {
            $schedule[$r['weekday']] = $r;
        }

        $pageTitle = 'Lịch làm việc bác sĩ';
        $view      = __DIR__ . '/../views/admin/doctor_schedule.php';

        $doctorView   = $doctor;
        $scheduleView = $schedule;
        $errorView    = $error;
        $noticeView   = $notice;

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }
    public function doctorAppointments()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $doctorId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($doctorId <= 0) {
            echo "ID bác sĩ không hợp lệ.";
            exit;
        }

        // Thông tin bác sĩ
        $stmt = $pdo->prepare("
        SELECT d.doctor_id, u.full_name AS doctor_name
        FROM doctors d
        JOIN users u ON d.user_id = u.user_id
        WHERE d.doctor_id = :id
    ");
        $stmt->execute(['id' => $doctorId]);
        $doctor = $stmt->fetch();
        if (!$doctor) {
            echo "Không tìm thấy bác sĩ.";
            exit;
        }

        // Bộ lọc ngày & trạng thái
        $dateFrom = $_GET['from'] ?? '';
        $dateTo   = $_GET['to'] ?? '';
        $status   = $_GET['status'] ?? '';

        $where  = "a.doctor_id = :doc";
        $params = ['doc' => $doctorId];

        if ($dateFrom !== '') {
            $where .= " AND DATE(a.appointment_date) >= :from";
            $params['from'] = $dateFrom;
        }
        if ($dateTo !== '') {
            $where .= " AND DATE(a.appointment_date) <= :to";
            $params['to'] = $dateTo;
        }
        if ($status !== '') {
            $where .= " AND a.status = :st";
            $params['st'] = $status;
        }

        $sql = "
        SELECT
            a.appointment_id,
            a.appointment_date,
            a.status,
            p.full_name AS patient_name,
            p.phone     AS patient_phone
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        WHERE $where
        ORDER BY a.appointment_date ASC
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $appointments = $stmt->fetchAll();

        $pageTitle = 'Lịch hẹn bác sĩ';
        $view      = __DIR__ . '/../views/admin/doctor_appointments.php';

        $doctorView      = $doctor;
        $appointmentsView = $appointments;
        $fromView        = $dateFrom;
        $toView          = $dateTo;
        $statusView      = $status;

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }
    public function doctorStats()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $doctorId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($doctorId <= 0) {
            echo "ID bác sĩ không hợp lệ.";
            exit;
        }

        // Info bác sĩ
        $stmt = $pdo->prepare("
        SELECT d.doctor_id, u.full_name AS doctor_name
        FROM doctors d
        JOIN users u ON d.user_id = u.user_id
        WHERE d.doctor_id = :id
    ");
        $stmt->execute(['id' => $doctorId]);
        $doctor = $stmt->fetch();
        if (!$doctor) {
            echo "Không tìm thấy bác sĩ.";
            exit;
        }

        $dateFrom = $_GET['from'] ?? '';
        $dateTo   = $_GET['to'] ?? '';

        if ($dateFrom === '') {
            $dateFrom = date('Y-m-01'); // đầu tháng hiện tại
        }
        if ($dateTo === '') {
            $dateTo = date('Y-m-d');    // hôm nay
        }

        $paramsRange = [
            'doc'  => $doctorId,
            'from' => $dateFrom . ' 00:00:00',
            'to'   => $dateTo . ' 23:59:59',
        ];

        // 1) Tổng số lịch hẹn
        $sqlApp = "
        SELECT COUNT(*) FROM appointments
        WHERE doctor_id = :doc
          AND appointment_date BETWEEN :from AND :to
    ";
        $stmt = $pdo->prepare($sqlApp);
        $stmt->execute($paramsRange);
        $totalAppointments = (int)$stmt->fetchColumn();

        // 2) Số hồ sơ y tế (ca khám hoàn thành)
        $sqlMed = "
        SELECT COUNT(*) FROM medical_records
        WHERE doctor_id = :doc
          AND visit_date BETWEEN :from AND :to
    ";
        $stmt = $pdo->prepare($sqlMed);
        $stmt->execute($paramsRange);
        $totalVisits = (int)$stmt->fetchColumn();

        // 3) Doanh thu (sum invoices.final_amount theo bác sĩ)
        $sqlRevenue = "
        SELECT COALESCE(SUM(i.final_amount), 0)
        FROM invoices i
        JOIN medical_records mr ON i.record_id = mr.record_id
        WHERE mr.doctor_id = :doc
          AND mr.visit_date BETWEEN :from AND :to
    ";
        $stmt = $pdo->prepare($sqlRevenue);
        $stmt->execute($paramsRange);
        $totalRevenue = (float)$stmt->fetchColumn();

        // 4) Top dịch vụ / ghi chú (tuỳ bạn có bảng services hay không, tạm bỏ qua)

        $pageTitle = 'Thống kê bác sĩ';
        $view      = __DIR__ . '/../views/admin/doctor_stats.php';

        $doctorView        = $doctor;
        $fromView          = $dateFrom;
        $toView            = $dateTo;
        $totalAppointmentsView = $totalAppointments;
        $totalVisitsView   = $totalVisits;
        $totalRevenueView  = $totalRevenue;

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }
    public function patients()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        // Lọc & phân trang
        $keyword = trim($_GET['q'] ?? '');
        $gender  = $_GET['gender'] ?? '';
        $page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $pageSize = 10;

        $where  = "1=1";
        $params = [];

        // Tìm theo tên bệnh nhân (patients.full_name)
        if ($keyword !== '') {
            $where .= " AND p.full_name LIKE :kw";
            $params['kw'] = '%' . $keyword . '%';
        }

        // Lọc theo giới tính
        if ($gender !== '') {
            $where .= " AND p.gender = :gender";
            $params['gender'] = $gender;
        }

        // Đếm tổng
        $sqlCount = "
        SELECT COUNT(*)
        FROM patients p
        LEFT JOIN users u ON p.user_id = u.user_id
        WHERE $where
    ";
        $stmt = $pdo->prepare($sqlCount);
        $stmt->execute($params);
        $totalRows  = (int)$stmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalRows / $pageSize));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $pageSize;

        // Lấy dữ liệu
        $sqlData = "
        SELECT
            p.patient_id,
            p.full_name,
            p.gender,
            p.date_of_birth,
            p.phone,
            p.email,
            p.address,
            p.note,

            u.username,
            u.status,
            u.created_at
        FROM patients p
        LEFT JOIN users u ON p.user_id = u.user_id
        WHERE $where
        ORDER BY u.created_at DESC, p.patient_id DESC
        LIMIT :limit OFFSET :offset
    ";
        $stmt = $pdo->prepare($sqlData);

        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit',  $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);

        $stmt->execute();
        $patients = $stmt->fetchAll();

        // Biến truyền ra view
        $pageTitle      = 'Quản lý bệnh nhân';
        $view           = __DIR__ . '/../views/admin/patients.php';
        $currentPage    = $page;
        $keywordView    = $keyword;
        $genderView     = $gender;
        $totalPagesView = $totalPages;

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }
    public function deletePatient()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $patientId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($patientId <= 0) {
            echo "ID bệnh nhân không hợp lệ.";
            exit;
        }

        // Lấy patient + user_id
        $stmt = $pdo->prepare("
        SELECT p.patient_id, p.user_id, p.full_name, u.username
        FROM patients p
        LEFT JOIN users u ON p.user_id = u.user_id
        WHERE p.patient_id = :id
    ");
        $stmt->execute(['id' => $patientId]);
        $row = $stmt->fetch();

        if (!$row) {
            echo "Không tìm thấy bệnh nhân.";
            exit;
        }

        $userId = $row['user_id'];

        try {
            $pdo->beginTransaction();

            // Xóa bệnh nhân
            $stmt = $pdo->prepare("DELETE FROM patients WHERE patient_id = :id");
            $stmt->execute(['id' => $patientId]);

            // Xóa user nếu tồn tại và role = patient
            if ($userId) {
                $stmt = $pdo->prepare("
                DELETE FROM users
                WHERE user_id = :uid
                  AND role = 'patient'
            ");
                $stmt->execute(['uid' => $userId]);
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Lỗi khi xóa bệnh nhân: " . htmlspecialchars($e->getMessage());
            exit;
        }

        header('Location: index.php?controller=admin&action=patients');
        exit;
    }
    public function patientHistory()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $patientId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($patientId <= 0) {
            echo "ID bệnh nhân không hợp lệ.";
            exit;
        }

        // Lấy thông tin bệnh nhân
        $stmt = $pdo->prepare("
        SELECT p.patient_id, p.full_name, p.phone, p.email, p.date_of_birth, p.address,
               u.username
        FROM patients p
        LEFT JOIN users u ON p.user_id = u.user_id
        WHERE p.patient_id = :id
    ");
        $stmt->execute(['id' => $patientId]);
        $patient = $stmt->fetch();

        if (!$patient) {
            echo "Không tìm thấy bệnh nhân.";
            exit;
        }

        // Lấy lịch sử khám: medical_records + bác sĩ + lịch hẹn + hóa đơn
        $sql = "
        SELECT
            mr.record_id,
            mr.visit_date,
            mr.chief_complaint,
            mr.diagnosis,
            mr.treatment_plan,
            mr.extra_note,
            mr.suggested_next_visit,

            d.doctor_id,
            udoc.full_name AS doctor_name,

            a.appointment_date,

            i.invoice_id,
            i.final_amount,
            i.payment_status
        FROM medical_records mr
        LEFT JOIN doctors d       ON mr.doctor_id = d.doctor_id
        LEFT JOIN users udoc      ON d.user_id = udoc.user_id
        LEFT JOIN appointments a  ON mr.appointment_id = a.appointment_id
        LEFT JOIN invoices i      ON i.record_id = mr.record_id
        WHERE mr.patient_id = :pid
        ORDER BY mr.visit_date DESC, mr.record_id DESC
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['pid' => $patientId]);
        $records = $stmt->fetchAll();

        $pageTitle    = 'Lịch sử khám bệnh nhân';
        $view         = __DIR__ . '/../views/admin/patient_history.php';
        $patientView  = $patient;
        $recordsView  = $records;

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }
    public function services()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $keyword = trim($_GET['q'] ?? '');
        $status  = $_GET['status'] ?? '';
        $page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $pageSize = 10;

        $where  = "1=1";
        $params = [];

        if ($keyword !== '') {
            $where .= " AND (s.service_name LIKE :kw OR s.description LIKE :kw)";
            $params['kw'] = '%' . $keyword . '%';
        }

        if ($status !== '') {
            // status: '1' hoặc '0'
            $where .= " AND s.is_active = :st";
            $params['st'] = (int)$status;
        }

        // Đếm tổng
        $sqlCount = "
        SELECT COUNT(*)
        FROM services s
        WHERE $where
    ";
        $stmt = $pdo->prepare($sqlCount);
        $stmt->execute($params);
        $totalRows  = (int)$stmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalRows / $pageSize));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $pageSize;

        // Lấy dữ liệu
        $sqlData = "
        SELECT
            s.service_id,
            s.service_name,
            s.description,
            s.unit_price,
            s.unit,
            s.is_active,
            s.created_at,
            s.updated_at
        FROM services s
        WHERE $where
        ORDER BY s.created_at DESC, s.service_id DESC
        LIMIT :limit OFFSET :offset
    ";
        $stmt = $pdo->prepare($sqlData);

        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit',  $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
        $stmt->execute();

        $services = $stmt->fetchAll();

        $pageTitle      = 'Quản lý dịch vụ / Giá';
        $view           = __DIR__ . '/../views/admin/services.php';
        $currentPage    = $page;
        $keywordView    = $keyword;
        $statusView     = $status;
        $totalPagesView = $totalPages;

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }
    public function createService()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $error = '';
        $old = [
            'service_name' => '',
            'description'  => '',
            'unit_price'   => '',
            'unit'         => '',
            'is_active'    => 1,
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $service_name = trim($_POST['service_name'] ?? '');
            $description  = trim($_POST['description'] ?? '');
            $unit_price   = trim($_POST['unit_price'] ?? '');
            $unit         = trim($_POST['unit'] ?? '');
            $is_active    = isset($_POST['is_active']) ? 1 : 0;

            $old = compact('service_name', 'description', 'unit_price', 'unit', 'is_active');

            if ($service_name === '') {
                $error = 'Tên dịch vụ không được để trống.';
            } elseif ($unit_price === '' || !is_numeric($unit_price) || (float)$unit_price < 0) {
                $error = 'Đơn giá phải là số không âm.';
            } else {
                $priceVal = (float)$unit_price;

                $stmt = $pdo->prepare("
                INSERT INTO services
                    (service_name, description, unit_price, unit, is_active, created_at)
                VALUES
                    (:name, :desc, :price, :unit, :active, NOW())
            ");
                $stmt->execute([
                    'name'   => $service_name,
                    'desc'   => $description ?: null,
                    'price'  => $priceVal,
                    'unit'   => $unit ?: null,
                    'active' => $is_active,
                ]);

                header('Location: index.php?controller=admin&action=services');
                exit;
            }
        }

        $pageTitle = 'Thêm dịch vụ';
        $view      = __DIR__ . '/../views/admin/service_form.php';
        $mode      = 'create';
        $oldData   = $old;
        $errorView = $error;

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }
    public function editService()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo "ID dịch vụ không hợp lệ.";
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM services WHERE service_id = :id");
        $stmt->execute(['id' => $id]);
        $service = $stmt->fetch();

        if (!$service) {
            echo "Không tìm thấy dịch vụ.";
            exit;
        }

        $error = '';
        $old = [
            'service_name' => $service['service_name'],
            'description'  => $service['description'],
            'unit_price'   => $service['unit_price'],
            'unit'         => $service['unit'],
            'is_active'    => $service['is_active'],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $service_name = trim($_POST['service_name'] ?? '');
            $description  = trim($_POST['description'] ?? '');
            $unit_price   = trim($_POST['unit_price'] ?? '');
            $unit         = trim($_POST['unit'] ?? '');
            $is_active    = isset($_POST['is_active']) ? 1 : 0;

            $old = compact('service_name', 'description', 'unit_price', 'unit', 'is_active');

            if ($service_name === '') {
                $error = 'Tên dịch vụ không được để trống.';
            } elseif ($unit_price === '' || !is_numeric($unit_price) || (float)$unit_price < 0) {
                $error = 'Đơn giá phải là số không âm.';
            } else {
                $priceVal = (float)$unit_price;

                $stmt = $pdo->prepare("
                UPDATE services
                SET service_name = :name,
                    description  = :desc,
                    unit_price   = :price,
                    unit         = :unit,
                    is_active    = :active,
                    updated_at   = NOW()
                WHERE service_id = :id
            ");
                $stmt->execute([
                    'name'   => $service_name,
                    'desc'   => $description ?: null,
                    'price'  => $priceVal,
                    'unit'   => $unit ?: null,
                    'active' => $is_active,
                    'id'     => $id,
                ]);

                header('Location: index.php?controller=admin&action=services');
                exit;
            }
        }

        $pageTitle = 'Chỉnh sửa dịch vụ';
        $view      = __DIR__ . '/../views/admin/service_form.php';
        $mode      = 'edit';
        $oldData   = $old;
        $errorView = $error;

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }
    public function toggleServiceStatus()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo "ID dịch vụ không hợp lệ.";
            exit;
        }

        $stmt = $pdo->prepare("SELECT is_active FROM services WHERE service_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            echo "Không tìm thấy dịch vụ.";
            exit;
        }

        $new = $row['is_active'] ? 0 : 1;

        $stmt = $pdo->prepare("
        UPDATE services
        SET is_active = :st, updated_at = NOW()
        WHERE service_id = :id
    ");
        $stmt->execute([
            'st' => $new,
            'id' => $id,
        ]);

        header('Location: index.php?controller=admin&action=services');
        exit;
    }
    public function importServices()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $error = '';
        $report = null;

        // Mặc định ví dụ header CSV:
        // service_name,description,unit_price,unit,is_active
        //
        // is_active: 1 = đang dùng, 0 = ngừng dùng (có thể bỏ trống → mặc định 1)

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (
                !isset($_FILES['csv_file']) ||
                $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK
            ) {
                $error = 'Vui lòng chọn file CSV hợp lệ.';
            } else {
                $tmpName = $_FILES['csv_file']['tmp_name'];

                if (!is_uploaded_file($tmpName)) {
                    $error = 'File upload không hợp lệ.';
                } else {
                    $handle = fopen($tmpName, 'r');
                    if ($handle === false) {
                        $error = 'Không thể đọc file CSV.';
                    } else {
                        $total = 0;
                        $inserted = 0;
                        $updated = 0;
                        $skipped = 0;

                        // Đọc dòng đầu tiên: giả sử là header, bỏ qua
                        $header = fgetcsv($handle, 2000, ",");

                        // Chuẩn bị statement dùng lại cho nhanh
                        $stmtSelect = $pdo->prepare("
                        SELECT service_id FROM services
                        WHERE service_name = :name
                        LIMIT 1
                    ");

                        $stmtInsert = $pdo->prepare("
                        INSERT INTO services
                            (service_name, description, unit_price, unit, is_active, created_at)
                        VALUES
                            (:name, :desc, :price, :unit, :active, NOW())
                    ");

                        $stmtUpdate = $pdo->prepare("
                        UPDATE services
                        SET description = :desc,
                            unit_price   = :price,
                            unit         = :unit,
                            is_active    = :active,
                            updated_at   = NOW()
                        WHERE service_id = :id
                    ");

                        while (($row = fgetcsv($handle, 5000, ",")) !== false) {
                            // Bỏ qua dòng rỗng
                            if (count($row) === 0) {
                                continue;
                            }

                            $total++;

                            // Lấy dữ liệu theo cột:
                            // 0: service_name
                            // 1: description
                            // 2: unit_price
                            // 3: unit
                            // 4: is_active
                            $service_name = trim($row[0] ?? '');
                            $description  = trim($row[1] ?? '');
                            $unit_price   = trim($row[2] ?? '');
                            $unit         = trim($row[3] ?? '');
                            $is_active    = trim($row[4] ?? '');

                            // Validate tối thiểu
                            if ($service_name === '' || $unit_price === '' || !is_numeric($unit_price)) {
                                $skipped++;
                                continue;
                            }

                            $priceVal = (float)$unit_price;
                            $activeVal = ($is_active === '' ? 1 : (int)$is_active);

                            // Kiểm tra đã tồn tại dịch vụ trùng tên chưa
                            $stmtSelect->execute(['name' => $service_name]);
                            $existing = $stmtSelect->fetch();

                            if ($existing) {
                                // UPDATE
                                $stmtUpdate->execute([
                                    'desc'   => $description ?: null,
                                    'price'  => $priceVal,
                                    'unit'   => $unit ?: null,
                                    'active' => $activeVal ? 1 : 0,
                                    'id'     => $existing['service_id'],
                                ]);
                                $updated++;
                            } else {
                                // INSERT mới
                                $stmtInsert->execute([
                                    'name'   => $service_name,
                                    'desc'   => $description ?: null,
                                    'price'  => $priceVal,
                                    'unit'   => $unit ?: null,
                                    'active' => $activeVal ? 1 : 0,
                                ]);
                                $inserted++;
                            }
                        }

                        fclose($handle);

                        $report = [
                            'total'    => $total,
                            'inserted' => $inserted,
                            'updated'  => $updated,
                            'skipped'  => $skipped,
                        ];
                    }
                }
            }
        }

        $pageTitle = 'Import dịch vụ (CSV)';
        $view      = __DIR__ . '/../views/admin/service_import.php';
        $errorView = $error;
        $reportView = $report;

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }
    public function invoices()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $keyword = trim($_GET['q'] ?? '');
        $status  = $_GET['status'] ?? '';
        $dateFrom = $_GET['from'] ?? '';
        $dateTo   = $_GET['to'] ?? '';
        $page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $pageSize = 10;

        $where  = "1=1";
        $params = [];

        // Tìm theo tên bệnh nhân / sđt / mã hóa đơn
        if ($keyword !== '') {
            $where .= " AND (
            p.full_name LIKE :kw
            OR p.phone LIKE :kw
            OR i.invoice_id = :kw_int
        )";
            $params['kw'] = '%' . $keyword . '%';
            $params['kw_int'] = (int)$keyword;
        }

        // Lọc trạng thái thanh toán
        if ($status !== '') {
            $where .= " AND i.payment_status = :st";
            $params['st'] = $status;
        }

        // Lọc khoảng ngày lập hóa đơn
        if ($dateFrom !== '') {
            $where .= " AND i.created_at >= :from";
            $params['from'] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== '') {
            $where .= " AND i.created_at <= :to";
            $params['to'] = $dateTo . ' 23:59:59';
        }

        // Đếm tổng
        $sqlCount = "
        SELECT COUNT(*)
        FROM invoices i
        JOIN patients p ON i.patient_id = p.patient_id
        LEFT JOIN medical_records mr ON i.record_id = mr.record_id
        LEFT JOIN doctors d ON mr.doctor_id = d.doctor_id
        LEFT JOIN users udoc ON d.user_id = udoc.user_id
        WHERE $where
    ";
        $stmt = $pdo->prepare($sqlCount);
        $stmt->execute($params);
        $totalRows  = (int)$stmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalRows / $pageSize));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $pageSize;

        // Lấy dữ liệu
        $sqlData = "
        SELECT
            i.invoice_id,
            i.created_at,
            i.total_amount,
            i.discount,
            i.final_amount,
            i.payment_status,
            i.payment_method,

            p.patient_id,
            p.full_name AS patient_name,
            p.phone     AS patient_phone,

            udoc.full_name AS doctor_name
        FROM invoices i
        JOIN patients p ON i.patient_id = p.patient_id
        LEFT JOIN medical_records mr ON i.record_id = mr.record_id
        LEFT JOIN doctors d ON mr.doctor_id = d.doctor_id
        LEFT JOIN users udoc ON d.user_id = udoc.user_id
        WHERE $where
        ORDER BY i.created_at DESC, i.invoice_id DESC
        LIMIT :limit OFFSET :offset
    ";
        $stmt = $pdo->prepare($sqlData);

        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit',  $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
        $stmt->execute();
        $invoices = $stmt->fetchAll();

        $pageTitle      = 'Quản lý hóa đơn';
        $view           = __DIR__ . '/../views/admin/invoices.php';
        $currentPage    = $page;
        $keywordView    = $keyword;
        $statusView     = $status;
        $fromView       = $dateFrom;
        $toView         = $dateTo;
        $totalPagesView = $totalPages;

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }
    public function invoiceDetail()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $invoiceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($invoiceId <= 0) {
            echo "Mã hóa đơn không hợp lệ.";
            exit;
        }

        // Lấy thông tin hóa đơn + bệnh nhân + bác sĩ + hồ sơ khám
        $sql = "
        SELECT
            i.invoice_id,
            i.created_at,
            i.total_amount,
            i.discount,
            i.final_amount,
            i.payment_status,
            i.payment_method,
            i.note               AS invoice_note,

            p.patient_id,
            p.full_name          AS patient_name,
            p.phone              AS patient_phone,
            p.email              AS patient_email,
            p.address            AS patient_address,

            mr.record_id,
            mr.visit_date,
            mr.chief_complaint,
            mr.clinical_note,
            mr.diagnosis,
            mr.treatment_plan,
            mr.extra_note,
            mr.suggested_next_visit,

            udoc.full_name       AS doctor_name
        FROM invoices i
        JOIN patients p       ON i.patient_id = p.patient_id
        JOIN medical_records mr ON i.record_id = mr.record_id
        LEFT JOIN doctors d      ON mr.doctor_id = d.doctor_id
        LEFT JOIN users udoc     ON d.user_id = udoc.user_id
        WHERE i.invoice_id = :id
        LIMIT 1
    ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $invoiceId]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            echo "Không tìm thấy hóa đơn.";
            exit;
        }

        $error = '';

        // Cập nhật trạng thái thanh toán / phương thức
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payment_status = $_POST['payment_status'] ?? $invoice['payment_status'];
            $payment_method = trim($_POST['payment_method'] ?? '');
            $note           = trim($_POST['invoice_note'] ?? $invoice['invoice_note']);

            if (!in_array($payment_status, ['UNPAID', 'PARTIAL', 'PAID'], true)) {
                $error = 'Trạng thái thanh toán không hợp lệ.';
            } else {
                $stmtUp = $pdo->prepare("
                UPDATE invoices
                SET payment_status = :st,
                    payment_method = :pm,
                    note           = :note
                WHERE invoice_id  = :id
            ");
                $stmtUp->execute([
                    'st'   => $payment_status,
                    'pm'   => $payment_method ?: null,
                    'note' => $note ?: null,
                    'id'   => $invoiceId,
                ]);

                // Reload dữ liệu mới
                header('Location: index.php?controller=admin&action=invoiceDetail&id=' . $invoiceId);
                exit;
            }

            // nếu có lỗi thì update tạm vào $invoice để giữ lại trên form
            $invoice['payment_status'] = $payment_status;
            $invoice['payment_method'] = $payment_method;
            $invoice['invoice_note']   = $note;
        }

        $pageTitle    = 'Chi tiết hóa đơn #' . $invoiceId;
        $view         = __DIR__ . '/../views/admin/invoice_detail.php';
        $invoiceView  = $invoice;
        $errorView    = $error;

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }
    public function backup()
    {
        $this->requireAdminLogin();

        $pageTitle = 'Sao lưu / Import dữ liệu';
        $view      = __DIR__ . '/../views/admin/backup.php';

        include __DIR__ . '/../views/layouts/admin_layout.php';
    }
    public function exportCsv()
    {
        $this->requireAdminLogin();
        $pdo = getPDO();

        $table = $_GET['table'] ?? '';

        if ($table === 'services') {
            $stmt = $pdo->query("
            SELECT service_name, description, unit_price, unit, is_active, created_at, updated_at
            FROM services
            ORDER BY service_id ASC
        ");
            $rows = $stmt->fetchAll();
            $filename = 'services_' . date('Ymd_His') . '.csv';
            $header = ['service_name', 'description', 'unit_price', 'unit', 'is_active', 'created_at', 'updated_at'];
        } elseif ($table === 'invoices') {
            $stmt = $pdo->query("
            SELECT
                invoice_id,
                record_id,
                patient_id,
                created_at,
                total_amount,
                discount,
                final_amount,
                payment_status,
                payment_method,
                note
            FROM invoices
            ORDER BY invoice_id ASC
        ");
            $rows = $stmt->fetchAll();
            $filename = 'invoices_' . date('Ymd_His') . '.csv';
            $header = [
                'invoice_id',
                'record_id',
                'patient_id',
                'created_at',
                'total_amount',
                'discount',
                'final_amount',
                'payment_status',
                'payment_method',
                'note'
            ];
        } elseif ($table === 'users') {
            $stmt = $pdo->query("
            SELECT user_id, username, full_name, email, phone, role, status, created_at
            FROM users
            ORDER BY user_id ASC
        ");
            $rows = $stmt->fetchAll();
            $filename = 'users_' . date('Ymd_His') . '.csv';
            $header = ['user_id', 'username', 'full_name', 'email', 'phone', 'role', 'status', 'created_at'];
        } elseif ($table === 'doctors') {
            $stmt = $pdo->query("
            SELECT d.doctor_id, u.username, u.full_name, d.specialization, d.experience_years, d.note, u.phone, u.email
            FROM doctors d
            JOIN users u ON d.user_id = u.user_id
            ORDER BY d.doctor_id ASC
        ");
            $rows = $stmt->fetchAll();
            $filename = 'doctors_' . date('Ymd_His') . '.csv';
            $header = ['doctor_id', 'username', 'full_name', 'specialization', 'experience_years', 'note', 'phone', 'email'];
        } elseif ($table === 'patients') {
            $stmt = $pdo->query("
            SELECT p.patient_id, u.username, p.full_name, p.gender, p.date_of_birth, p.phone, p.email, p.address, p.note
            FROM patients p
            LEFT JOIN users u ON p.user_id = u.user_id
            ORDER BY p.patient_id ASC
        ");
            $rows = $stmt->fetchAll();
            $filename = 'patients_' . date('Ymd_His') . '.csv';
            $header = ['patient_id', 'username', 'full_name', 'gender', 'date_of_birth', 'phone', 'email', 'address', 'note'];
        } elseif ($table === 'appointments') {
            $stmt = $pdo->query("
            SELECT a.appointment_id, a.appointment_date, a.time_block, a.status, 
                   p.full_name AS patient_name, u.full_name AS doctor_name, a.note
            FROM appointments a
            LEFT JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN users u ON a.doctor_id = u.user_id
            ORDER BY a.appointment_id ASC
        ");
            $rows = $stmt->fetchAll();
            $filename = 'appointments_' . date('Ymd_His') . '.csv';
            $header = ['appointment_id', 'appointment_date', 'time_block', 'status', 'patient_name', 'doctor_name', 'note'];
        } elseif ($table === 'medical_records') {
            $stmt = $pdo->query("
            SELECT mr.record_id, mr.patient_id, p.full_name AS patient_name, mr.doctor_id, u.full_name AS doctor_name,
                   mr.visit_date, mr.chief_complaint, mr.diagnosis, mr.treatment_plan, mr.extra_note, mr.suggested_next_visit
            FROM medical_records mr
            LEFT JOIN patients p ON mr.patient_id = p.patient_id
            LEFT JOIN users u ON mr.doctor_id = u.user_id
            ORDER BY mr.record_id ASC
        ");
            $rows = $stmt->fetchAll();
            $filename = 'medical_records_' . date('Ymd_His') . '.csv';
            $header = ['record_id', 'patient_id', 'patient_name', 'doctor_id', 'doctor_name', 'visit_date', 'chief_complaint', 'diagnosis', 'treatment_plan', 'extra_note', 'suggested_next_visit'];
        } else {
            echo "Loại dữ liệu không hỗ trợ sao lưu.";
            exit;
        }

        // Xuất CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Ghi header
        fputcsv($output, $header);

        // Ghi dữ liệu
        foreach ($rows as $row) {
            $line = [];
            foreach ($header as $col) {
                $line[] = $row[$col] ?? '';
            }
            fputcsv($output, $line);
        }

        fclose($output);
        exit;
    }
}

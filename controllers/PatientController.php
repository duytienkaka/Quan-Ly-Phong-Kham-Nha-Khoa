<?php
// controllers/PatientController.php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../models/MedicalRecord.php';
require_once __DIR__ . '/../models/Invoice.php';

class PatientController
{
    private function requirePatientLogin()
    {
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'patient') {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }

    public function dashboard()
    {
        $this->requirePatientLogin();

        $userId  = $_SESSION['user_id'];
        $user    = User::findById($userId);
        $patient = Patient::findByUserId($userId);

        $needProfile = false;

        if (!$patient) {
            $needProfile = true;
        } else {
            if (empty($patient['phone']) || empty($patient['address']) || empty($patient['date_of_birth'])) {
                $needProfile = true;
            }
        }

        $isEditing = false; // đang xem bình thường

        // Thống kê nhanh (Quick stats)
        $upcomingCount = 0;
        $recordsCount = 0;
        $unpaidInvoicesCount = 0;

        if ($patient) {
            $pdo = getPDO();
            $patientId = $patient['patient_id'];
            $today = date('Y-m-d');

            // upcoming appointments (today or later, not canceled)
            $sql = "SELECT COUNT(*) FROM appointments WHERE patient_id = :pid AND appointment_date >= :today";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':pid', $patientId);
            $stmt->bindValue(':today', $today);
            $stmt->execute();
            $upcomingCount = (int)$stmt->fetchColumn();

            // medical records count
            $sql = "SELECT COUNT(*) FROM medical_records WHERE patient_id = :pid";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':pid', $patientId);
            $stmt->execute();
            $recordsCount = (int)$stmt->fetchColumn();

            // unpaid invoices (not PAID)
            $sql = "SELECT COUNT(*) FROM invoices WHERE patient_id = :pid AND (payment_status IS NULL OR payment_status <> 'PAID')";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':pid', $patientId);
            $stmt->execute();
            $unpaidInvoicesCount = (int)$stmt->fetchColumn();
        }

        $pageTitle = 'Tài khoản bệnh nhân';
        $view      = __DIR__ . '/../views/patient/dashboard.php';
        include __DIR__ . '/../views/layouts/patient_layout.php';
    }
    public function edit()
    {
        $this->requirePatientLogin();

        $userId  = $_SESSION['user_id'];
        $user    = User::findById($userId);
        $patient = Patient::findByUserId($userId);

        $needProfile = true;
        $isEditing   = true;

        $pageTitle = 'Chỉnh sửa thông tin cá nhân';
        $view      = __DIR__ . '/../views/patient/dashboard.php';

        include __DIR__ . '/../views/layouts/patient_layout.php';
    }


    // Lưu thông tin cá nhân
    public function saveProfile()
    {
        $this->requirePatientLogin();
        $pdo    = getPDO();
        $userId = $_SESSION['user_id'];

        // Lấy dữ liệu từ form
        $full_name     = trim($_POST['full_name'] ?? '');
        $gender        = $_POST['gender'] ?? null;
        $date_of_birth = $_POST['date_of_birth'] ?? null;
        $phone         = trim($_POST['phone'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $address       = trim($_POST['address'] ?? '');
        $note          = trim($_POST['note'] ?? '');

        if ($full_name === '') {
            // xử lý lỗi đơn giản, bạn có thể redirect lại và hiển thị message cho đẹp hơn
            die('Vui lòng nhập họ tên.');
        }

        try {
            $pdo->beginTransaction();

            // 1) Update bảng users với thông tin chung
            $stmt = $pdo->prepare("
            UPDATE users
            SET full_name = :full_name,
                phone     = :phone,
                email     = :email,
                updated_at = NOW()
            WHERE user_id = :uid
        ");
            $stmt->execute([
                'full_name' => $full_name,
                'phone'     => $phone,
                'email'     => $email,
                'uid'       => $userId,
            ]);

            // 2) Insert/Update bảng patients
            $stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE user_id = :uid");
            $stmt->execute(['uid' => $userId]);
            $patient = $stmt->fetch();

            if ($patient) {
                // update
                $stmt = $pdo->prepare("
                UPDATE patients
                SET full_name     = :full_name,
                    gender        = :gender,
                    date_of_birth = :dob,
                    phone         = :phone,
                    email         = :email,
                    address       = :address,
                    note          = :note,
                    updated_at    = NOW()
                WHERE user_id = :uid
            ");
                $stmt->execute([
                    'full_name' => $full_name,
                    'gender'    => $gender ?: null,
                    'dob'       => $date_of_birth ?: null,
                    'phone'     => $phone,
                    'email'     => $email,
                    'address'   => $address,
                    'note'      => $note,
                    'uid'       => $userId,
                ]);
            } else {
                // insert mới
                $stmt = $pdo->prepare("
                INSERT INTO patients
                    (user_id, full_name, gender, date_of_birth, phone, email, address, note, created_at)
                VALUES
                    (:uid, :full_name, :gender, :dob, :phone, :email, :address, :note, NOW())
            ");
                $stmt->execute([
                    'uid'       => $userId,
                    'full_name' => $full_name,
                    'gender'    => $gender ?: null,
                    'dob'       => $date_of_birth ?: null,
                    'phone'     => $phone,
                    'email'     => $email,
                    'address'   => $address,
                    'note'      => $note,
                ]);
            }

            $pdo->commit();

            // quay lại dashboard
            header('Location: index.php?controller=patient&action=dashboard');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Lỗi lưu thông tin: " . $e->getMessage());
        }
    }

    public function appointments()
    {
        $this->requirePatientLogin();

        $userId  = $_SESSION['user_id'];
        $user    = User::findById($userId);
        $patient = Patient::findByUserId($userId);

        if (!$patient) {
            header('Location: index.php?controller=patient&action=dashboard');
            exit;
        }

        $patientId = $patient['patient_id'];

        // Lấy filter từ GET
        $fromDate = $_GET['from_date'] ?? '';
        $toDate   = $_GET['to_date'] ?? '';
        $page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $pageSize = 10;

        $pdo = getPDO();

        // Xây WHERE động
        $where  = "patient_id = :pid";
        $params = ['pid' => $patientId];

        if ($fromDate !== '') {
            $where .= " AND appointment_date >= :from_date";
            $params['from_date'] = $fromDate;
        }
        if ($toDate !== '') {
            $where .= " AND appointment_date <= :to_date";
            $params['to_date'] = $toDate;
        }

        // Đếm tổng để phân trang
        $sqlCount = "SELECT COUNT(*) FROM appointments WHERE $where";
        $stmt = $pdo->prepare($sqlCount);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->execute();
        $totalRows  = (int)$stmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalRows / $pageSize));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $pageSize;

        // Lấy dữ liệu trang hiện tại
        $sqlData = "SELECT *
                FROM appointments
                WHERE $where
                ORDER BY appointment_date DESC, time_block DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sqlData);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit',  $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
        $stmt->execute();
        $appointments = $stmt->fetchAll();
        $pageTitle = 'Lịch hẹn của tôi';
        $view      = __DIR__ . '/../views/patient/appointments.php';

        // Truyền thêm biến cho view (pagination meta)
        $fromDateView = $fromDate;
        $toDateView   = $toDate;
        $currentPage  = $page;
        $totalItems   = $totalRows;
        $perPage      = $pageSize;
        $startItem    = $totalRows > 0 ? ($offset + 1) : 0;
        $endItem      = $totalRows > 0 ? min($totalRows, $offset + $pageSize) : 0;

        include __DIR__ . '/../views/layouts/patient_layout.php';
    }

    public function booking()
    {
        $this->requirePatientLogin();

        $userId  = $_SESSION['user_id'];
        $user    = User::findById($userId);
        $patient = Patient::findByUserId($userId);

        // Nếu chưa có hồ sơ bệnh nhân thì bắt điền info trước
        if (!$patient) {
            header('Location: index.php?controller=patient&action=dashboard');
            exit;
        }

        // Có thể truyền thêm $error từ query string sau này, trước mắt để rỗng
        $error   = $_GET['error'] ?? '';
        $success = $_GET['success'] ?? '';

        $pageTitle = 'Đặt lịch khám';
        $view      = __DIR__ . '/../views/patient/booking.php';
        include __DIR__ . '/../views/layouts/patient_layout.php';
    }

    public function saveBooking()
    {
        $this->requirePatientLogin();

        $userId  = $_SESSION['user_id'];
        $user    = User::findById($userId);
        $patient = Patient::findByUserId($userId);

        if (!$patient) {
            header('Location: index.php?controller=patient&action=dashboard');
            exit;
        }

        // Lấy dữ liệu từ form
        $appointmentDate = $_POST['appointment_date'] ?? '';
        $timeBlock       = $_POST['time_block'] ?? '';
        $note            = trim($_POST['note'] ?? '');

        $error = '';

        // Validate đơn giản
        if (empty($appointmentDate) || empty($timeBlock)) {
            $error = 'Vui lòng chọn ngày khám và buổi khám.';
        } else {
            $today = date('Y-m-d');
            if ($appointmentDate < $today) {
                $error = 'Ngày khám phải là hôm nay hoặc sau hôm nay.';
            }
        }

        if ($error !== '') {
            $pageTitle = 'Đặt lịch khám';
            $view      = __DIR__ . '/../views/patient/booking.php';
            $appointments = []; // nếu view có dùng
            // Biến cho view
            $appointmentDateOld = $appointmentDate;
            $timeBlockOld       = $timeBlock;
            $noteOld            = $note;
            include __DIR__ . '/../views/layouts/patient_layout.php';
            return;
        }

        // INSERT vào bảng appointments
        $pdo = getPDO();
        $sql = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, time_block, status, booking_source, note, created_at)
            VALUES (?, NULL, ?, ?, 'WAITING', 'PREBOOKED', ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $patient['patient_id'],
            $appointmentDate,
            $timeBlock,
            $note
        ]);

        header('Location: index.php?controller=patient&action=appointments');
        exit;
    }
    public function history()
    {
        $this->requirePatientLogin();

        $userId  = $_SESSION['user_id'];
        $user    = User::findById($userId);
        $patient = Patient::findByUserId($userId);

        if (!$patient) {
            header('Location: index.php?controller=patient&action=dashboard');
            exit;
        }

        $patientId = $patient['patient_id'];

        $fromDate = $_GET['from_date'] ?? '';
        $toDate   = $_GET['to_date'] ?? '';
        $page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $pageSize = 10;

        $pdo = getPDO();

        $where  = "patient_id = :pid";
        $params = ['pid' => $patientId];

        if ($fromDate !== '') {
            $where .= " AND DATE(visit_date) >= :from_date";
            $params['from_date'] = $fromDate;
        }
        if ($toDate !== '') {
            $where .= " AND DATE(visit_date) <= :to_date";
            $params['to_date'] = $toDate;
        }

        $sqlCount = "SELECT COUNT(*) FROM medical_records WHERE $where";
        $stmt = $pdo->prepare($sqlCount);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->execute();
        $totalRows  = (int)$stmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalRows / $pageSize));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $pageSize;

        $sqlData = "SELECT *
                FROM medical_records
                WHERE $where
                ORDER BY visit_date DESC, record_id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sqlData);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit',  $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
        $stmt->execute();
        $records = $stmt->fetchAll();

        $pageTitle = 'Lịch sử khám';
        $view      = __DIR__ . '/../views/patient/history.php';

        $fromDateView = $fromDate;
        $toDateView   = $toDate;
        $currentPage  = $page;
        $totalItems   = $totalRows;
        $perPage      = $pageSize;
        $startItem    = $totalRows > 0 ? ($offset + 1) : 0;
        $endItem      = $totalRows > 0 ? min($totalRows, $offset + $pageSize) : 0;

        include __DIR__ . '/../views/layouts/patient_layout.php';
    }

    public function invoices()
    {
        $this->requirePatientLogin();

        $userId  = $_SESSION['user_id'];
        $user    = User::findById($userId);
        $patient = Patient::findByUserId($userId);

        if (!$patient) {
            header('Location: index.php?controller=patient&action=dashboard');
            exit;
        }

        $patientId = $patient['patient_id'];

        $fromDate = $_GET['from_date'] ?? '';
        $toDate   = $_GET['to_date'] ?? '';
        $page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $pageSize = 10;

        $pdo = getPDO();

        $where  = "patient_id = :pid";
        $params = ['pid' => $patientId];

        if ($fromDate !== '') {
            $where .= " AND DATE(created_at) >= :from_date";
            $params['from_date'] = $fromDate;
        }
        if ($toDate !== '') {
            $where .= " AND DATE(created_at) <= :to_date";
            $params['to_date'] = $toDate;
        }

        $sqlCount = "SELECT COUNT(*) FROM invoices WHERE $where";
        $stmt = $pdo->prepare($sqlCount);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->execute();
        $totalRows  = (int)$stmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalRows / $pageSize));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $pageSize;

        $sqlData = "SELECT *
                FROM invoices
                WHERE $where
                ORDER BY created_at DESC, invoice_id DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sqlData);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit',  $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
        $stmt->execute();
        $invoices = $stmt->fetchAll();

        $pageTitle = 'Hóa đơn của tôi';
        $view      = __DIR__ . '/../views/patient/invoices.php';

        $fromDateView = $fromDate;
        $toDateView   = $toDate;
        $currentPage  = $page;
        $totalItems   = $totalRows;
        $perPage      = $pageSize;
        $startItem    = $totalRows > 0 ? ($offset + 1) : 0;
        $endItem      = $totalRows > 0 ? min($totalRows, $offset + $pageSize) : 0;

        include __DIR__ . '/../views/layouts/patient_layout.php';
    }
    public function invoiceDetail()
    {
        // giống các hàm khác: bắt buộc patient đã login
        if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $invoiceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($invoiceId <= 0) {
            echo "Mã hóa đơn không hợp lệ.";
            exit;
        }

        $pdo = getPDO();

        // Lấy patient_id từ user_id (như các chỗ khác bạn đã dùng)
        $stmt = $pdo->prepare("
        SELECT patient_id
        FROM patients
        WHERE user_id = :uid
    ");
        $stmt->execute(['uid' => $userId]);
        $patient = $stmt->fetch();

        if (!$patient) {
            echo "Không tìm thấy hồ sơ bệnh nhân.";
            exit;
        }

        $patientId = (int)$patient['patient_id'];

        // Lấy thông tin hóa đơn + lần khám + bác sĩ
        $sql = "
        SELECT
            i.invoice_id,
            i.created_at,
            i.total_amount,
            i.discount,
            i.final_amount,
            i.payment_status,
            i.payment_method,
            i.note         AS invoice_note,

            mr.record_id,
            mr.visit_date,
            mr.chief_complaint,
            mr.clinical_note,
            mr.diagnosis,
            mr.treatment_plan,
            mr.extra_note,
            mr.suggested_next_visit,

            udoc.full_name AS doctor_name
        FROM invoices i
        JOIN medical_records mr ON i.record_id = mr.record_id
        LEFT JOIN doctors d      ON mr.doctor_id = d.doctor_id
        LEFT JOIN users udoc     ON d.user_id = udoc.user_id
        WHERE i.invoice_id = :iid
          AND i.patient_id = :pid
        LIMIT 1
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'iid' => $invoiceId,
            'pid' => $patientId,
        ]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            echo "Không tìm thấy hóa đơn hoặc hóa đơn không thuộc về bạn.";
            exit;
        }
        $user = User::findById($userId);
        $pageTitle = 'Chi tiết hóa đơn';
        $view      = __DIR__ . '/../views/patient/invoice_detail.php';
        $invoiceView = $invoice;

        include __DIR__ . '/../views/layouts/patient_layout.php';
    }
}

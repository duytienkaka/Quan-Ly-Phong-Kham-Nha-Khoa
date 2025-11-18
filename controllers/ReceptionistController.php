<?php

require_once __DIR__ . '/../models/User.php';

class ReceptionistController
{
    private function requireReceptionistLogin()
    {
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'receptionist') {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }

    /* ================== DASHBOARD ================== */

    public function dashboard()
    {
        $this->requireReceptionistLogin();
        $pdo = getPDO();

        $userId = (int)$_SESSION['user_id'];
        $user   = User::findById($userId);

        // Handle quick approve action (AJAX)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $actionType = $_POST['action_type'] ?? '';
            if ($actionType === 'approve') {
                $aid = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
                if ($aid > 0) {
                    try {
                        $stmt = $pdo->prepare("UPDATE appointments SET status = 'IN_PROGRESS' WHERE appointment_id = :id");
                        $stmt->execute(['id' => $aid]);
                        header('Content-Type: application/json');
                        echo json_encode(['ok' => true]);
                        exit;
                    } catch (PDOException $e) {
                        header('Content-Type: application/json');
                        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
                        exit;
                    }
                }
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'error' => 'Invalid appointment id']);
                exit;
            }
        }

        // Thống kê lịch hẹn hôm nay theo status
        $sqlStats = "
            SELECT status, COUNT(*) AS cnt
            FROM appointments
            WHERE DATE(appointment_date) = CURDATE()
            GROUP BY status
        ";
        $rows = $pdo->query($sqlStats)->fetchAll();

        $totalToday      = 0;
        $countWaiting    = 0;
        $countInProgress = 0;
        $countCompleted  = 0;
        $countCancelled  = 0;
        $countNoShow     = 0;

        foreach ($rows as $r) {
            $status = $r['status'];
            $cnt    = (int)$r['cnt'];
            $totalToday += $cnt;

            switch ($status) {
                case 'WAITING':
                    $countWaiting = $cnt;
                    break;
                case 'IN_PROGRESS':
                    $countInProgress = $cnt;
                    break;
                case 'COMPLETED':
                    $countCompleted = $cnt;
                    break;
                case 'CANCELLED':
                    $countCancelled = $cnt;
                    break;
                case 'NO_SHOW':
                    $countNoShow = $cnt;
                    break;
            }
        }
        $sqlFreeDoc = "
            SELECT d.doctor_id, u.full_name
            FROM doctors d
            JOIN users u ON d.user_id = u.user_id
            LEFT JOIN appointments a2
                ON a2.doctor_id = d.doctor_id
                AND a2.status = 'IN_PROGRESS'
            WHERE a2.appointment_id IS NULL
            ORDER BY u.full_name ASC
        ";
        $freeDoctors = $pdo->query($sqlFreeDoc)->fetchAll();
        $freeDoctorsCount = count($freeDoctors);

        // Danh sách lịch hẹn hôm nay (tối đa 20) – CÓ queue_number
        $sqlList = "
            SELECT
                a.appointment_id,
                a.appointment_date,
                a.queue_number,
                a.status,
                a.time_block,
                a.note,
                p.full_name AS patient_name,
                p.phone     AS patient_phone,
                udoc.full_name AS doctor_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN doctors d  ON a.doctor_id = d.doctor_id
            LEFT JOIN users   udoc ON d.user_id = udoc.user_id
            WHERE DATE(a.appointment_date) = CURDATE()
            ORDER BY a.queue_number ASC, a.appointment_date ASC, a.appointment_id ASC
            LIMIT 20
        ";
        $appointmentsToday = $pdo->query($sqlList)->fetchAll();

        $stats = [
            'total'        => $totalToday,
            'waiting'      => $countWaiting,
            'in_progress'  => $countInProgress,
            'completed'    => $countCompleted,
            'cancelled'    => $countCancelled,
            'no_show'      => $countNoShow,
        ];

        // Upcoming appointments (future) - next 6
        $sqlUpcoming = "
            SELECT a.appointment_id, a.appointment_date, p.full_name AS patient_name, udoc.full_name AS doctor_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN doctors d  ON a.doctor_id = d.doctor_id
            LEFT JOIN users   udoc ON d.user_id = udoc.user_id
            WHERE a.appointment_date > NOW()
            ORDER BY a.appointment_date ASC
            LIMIT 6
        ";
        $upcomingAppointments = $pdo->query($sqlUpcoming)->fetchAll();

        // Recent patients
        $sqlRecentPatients = "
            SELECT patient_id, full_name, phone
            FROM patients
            ORDER BY created_at DESC
            LIMIT 6
        ";
        $recentPatients = $pdo->query($sqlRecentPatients)->fetchAll();

        // Pending approvals (today's WAITING)
        $sqlPending = "
            SELECT a.appointment_id, a.appointment_date, p.full_name AS patient_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            WHERE DATE(a.appointment_date) = CURDATE() AND a.status = 'WAITING'
            ORDER BY a.queue_number ASC, a.appointment_date ASC, a.appointment_id ASC
            LIMIT 6
        ";
        $pendingApprovals = $pdo->query($sqlPending)->fetchAll();
        $freeDoctors      = $freeDoctors ?? [];
        $freeDoctorsCount = $freeDoctorsCount ?? 0;

        $pageTitle = 'Dashboard lễ tân';
        $view      = __DIR__ . '/../views/receptionist/dashboard.php';

        $upcomingAppointments = $upcomingAppointments ?? [];
        $recentPatients      = $recentPatients      ?? [];
        $pendingApprovals    = $pendingApprovals    ?? [];

        include __DIR__ . '/../views/layouts/receptionist_layout.php';
    }

    /* ================== DANH SÁCH LỊCH HẸN ================== */

    public function appointments()
    {
        $this->requireReceptionistLogin();
        $pdo = getPDO();

        $userId = (int)$_SESSION['user_id'];
        $user   = User::findById($userId);

        // Bộ lọc
        $date    = $_GET['date']   ?? date('Y-m-d');
        $status  = $_GET['status'] ?? '';
        $keyword = trim($_GET['q'] ?? '');

        // phân trang
        $page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $pageSize = 10;
        $offset   = ($page - 1) * $pageSize;

        // WHERE động
        $where  = "DATE(a.appointment_date) = :date";
        $params = ['date' => $date];

        if ($status !== '') {
            $where .= " AND a.status = :status";
            $params['status'] = $status;
        }

        if ($keyword !== '') {
            $where .= " AND (p.full_name LIKE :kw OR p.phone LIKE :kw)";
            $params['kw'] = '%' . $keyword . '%';
        }

        // Đếm tổng
        $sqlCount = "
            SELECT COUNT(*)
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            WHERE $where
        ";
        $stmt = $pdo->prepare($sqlCount);
        $stmt->execute($params);
        $totalRows  = (int)$stmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalRows / $pageSize));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $pageSize;

        // Lấy danh sách (CÓ queue_number)
        $sql = "
            SELECT
                a.appointment_id,
                a.appointment_date,
                a.queue_number,
                a.time_block,
                a.status,
                a.note,
                p.full_name AS patient_name,
                p.phone     AS patient_phone,
                udoc.full_name AS doctor_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN doctors d  ON a.doctor_id = d.doctor_id
            LEFT JOIN users   udoc ON d.user_id = udoc.user_id
            WHERE $where
            ORDER BY a.queue_number ASC, a.appointment_date ASC, a.appointment_id ASC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit',  $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
        $stmt->execute();
        $appointments = $stmt->fetchAll();

        $pageTitle        = 'Quản lý lịch hẹn';
        $view             = __DIR__ . '/../views/receptionist/appointments.php';
        $userView         = $user;
        $dateView         = $date;
        $statusView       = $status;
        $keywordView      = $keyword;
        $currentPage      = $page;
        $totalPagesView   = $totalPages;
        $totalRowsView    = $totalRows;
        $appointmentsView = $appointments;

        include __DIR__ . '/../views/layouts/receptionist_layout.php';
    }

    /* ================== TẠO LỊCH HẸN ================== */

    public function createAppointment()
    {
        $this->requireReceptionistLogin();
        $pdo = getPDO();

        $userId = (int)$_SESSION['user_id'];
        $user   = User::findById($userId);

        $error   = '';
        $success = '';

        // danh sách bệnh nhân có sẵn
        $stmt = $pdo->query("
            SELECT patient_id, full_name, phone
            FROM patients
            ORDER BY created_at DESC
            LIMIT 100
        ");
        $patients = $stmt->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mode       = $_POST['patient_mode'] ?? 'existing';
            $patient_id = 0;

            // bệnh nhân có sẵn
            if ($mode === 'existing') {
                $patient_id = (int)($_POST['patient_id'] ?? 0);
                if ($patient_id <= 0) {
                    $error = 'Vui lòng chọn bệnh nhân có sẵn hoặc nhập bệnh nhân mới.';
                }
            }

            // bệnh nhân mới
            if ($mode === 'new') {
                $full_name = trim($_POST['new_full_name'] ?? '');
                $phone     = trim($_POST['new_phone'] ?? '');
                $email     = trim($_POST['new_email'] ?? '');
                $address   = trim($_POST['new_address'] ?? '');
                $notePat   = trim($_POST['new_note'] ?? '');

                if ($full_name === '' || $phone === '') {
                    $error = 'Với bệnh nhân mới, cần nhập ít nhất họ tên và số điện thoại.';
                } else {
                    $stmtInsPat = $pdo->prepare("
                        INSERT INTO patients
                            (user_id, full_name, gender, date_of_birth, phone, email, address, note, created_at)
                        VALUES
                            (NULL, :full_name, NULL, NULL, :phone, :email, :address, :note, NOW())
                    ");
                    $stmtInsPat->execute([
                        'full_name' => $full_name,
                        'phone'     => $phone,
                        'email'     => $email ?: null,
                        'address'   => $address ?: null,
                        'note'      => $notePat ?: null,
                    ]);

                    $patient_id = (int)$pdo->lastInsertId();
                }
            }

            // thông tin lịch hẹn
            $date   = trim($_POST['date'] ?? '');
            $time   = trim($_POST['time'] ?? '');
            $note   = trim($_POST['note'] ?? '');
            $status = $_POST['status'] ?? 'WAITING';

            if ($error === '') {
                if ($patient_id <= 0 || $date === '' || $time === '') {
                    $error = 'Vui lòng chọn/nhập bệnh nhân và nhập ngày/giờ khám.';
                } else {
                    $appointmentDateTime = $date . ' ' . $time . ':00';

                    // Tính queue_number tiếp theo cho ngày đó
                    $stmtQ = $pdo->prepare("
                        SELECT MAX(queue_number)
                        FROM appointments
                        WHERE DATE(appointment_date) = :d
                    ");
                    $stmtQ->execute(['d' => $date]);
                    $maxQueue  = (int)$stmtQ->fetchColumn();
                    $nextQueue = $maxQueue + 1;

                    $stmtIns = $pdo->prepare("
                        INSERT INTO appointments
                            (patient_id, doctor_id, appointment_date, queue_number, status, note, created_at)
                        VALUES
                            (:pid, NULL, :dt, :qnum, :st, :note, NOW())
                    ");
                    $stmtIns->execute([
                        'pid'  => $patient_id,
                        'dt'   => $appointmentDateTime,
                        'qnum' => $nextQueue,
                        'st'   => $status,
                        'note' => $note ?: null,
                    ]);

                    $success = 'Đã tạo lịch hẹn mới. Số thứ tự: #' . $nextQueue;
                    $_POST   = []; // reset form
                }
            }
        }

        $pageTitle     = 'Tạo lịch hẹn mới';
        $view          = __DIR__ . '/../views/receptionist/appointment_form.php';
        $patientsView  = $patients;
        $errorView     = $error;
        $successView   = $success;

        include __DIR__ . '/../views/layouts/receptionist_layout.php';
    }

    /* ================== CHI TIẾT LỊCH HẸN ================== */

    public function appointmentDetail()
    {
        $this->requireReceptionistLogin();
        $pdo = getPDO();

        $userId = (int)$_SESSION['user_id'];
        $user   = User::findById($userId);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo "Mã lịch hẹn không hợp lệ.";
            exit;
        }

        $error   = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $actionType = $_POST['action_type'] ?? '';

            try {
                if ($actionType === 'update_main') {

                    $doctor_id = (int)($_POST['doctor_id'] ?? 0);
                    $status    = trim($_POST['status'] ?? '');

                    $allowedStatus = ['WAITING', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED', 'NO_SHOW'];

                    if (!in_array($status, $allowedStatus, true)) {
                        $error = 'Trạng thái không hợp lệ.';
                    } else {
                        $stmt = $pdo->prepare("
                            UPDATE appointments
                            SET doctor_id = :did,
                                status    = :st
                            WHERE appointment_id = :id
                        ");
                        $stmt->execute([
                            'did' => ($doctor_id > 0 ? $doctor_id : null),
                            'st'  => $status,
                            'id'  => $id,
                        ]);

                        header('Location: index.php?controller=receptionist&action=appointments');
                        exit;
                    }
                } elseif ($actionType === 'cancel') {

                    $reason = trim($_POST['cancel_reason'] ?? '');
                    if ($reason === '') {
                        $error = 'Vui lòng nhập lý do hủy lịch.';
                    } else {
                        $stmtUp = $pdo->prepare("
                            UPDATE appointments
                            SET status = 'CANCELLED',
                                note   = :reason
                            WHERE appointment_id = :id
                        ");
                        $stmtUp->execute([
                            'reason' => '[Hủy]: ' . $reason,
                            'id'     => $id,
                        ]);

                        header('Location: index.php?controller=receptionist&action=appointments');
                        exit;
                    }
                }
            } catch (PDOException $e) {
                $error = 'Lỗi khi cập nhật lịch hẹn: ' . $e->getMessage();
            }
        }

        $sql = "
            SELECT
                a.appointment_id,
                a.appointment_date,
                a.queue_number,
                a.status,
                a.note,
                p.patient_id,
                p.full_name AS patient_name,
                p.phone     AS patient_phone,
                p.email     AS patient_email,
                p.address   AS patient_address,
                d.doctor_id,
                udoc.full_name AS doctor_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN doctors d  ON a.doctor_id = d.doctor_id
            LEFT JOIN users   udoc ON d.user_id = udoc.user_id
            WHERE a.appointment_id = :id
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $appointment = $stmt->fetch();

        if (!$appointment) {
            echo "Không tìm thấy lịch hẹn.";
            exit;
        }

        $currentDoctorId = $appointment['doctor_id'] ?? null;

        $sqlDoc = "
            SELECT d.doctor_id, u.full_name
            FROM doctors d
            JOIN users u ON d.user_id = u.user_id
            LEFT JOIN appointments a2
                ON a2.doctor_id = d.doctor_id
                AND a2.status = 'IN_PROGRESS'
            WHERE a2.appointment_id IS NULL
        ";

        if ($currentDoctorId) {
            $sqlDoc .= " OR d.doctor_id = :curDoc ";
        }

        $sqlDoc .= "
            GROUP BY d.doctor_id, u.full_name
            ORDER BY u.full_name ASC
        ";

        $stmtDoc = $pdo->prepare($sqlDoc);
        if ($currentDoctorId) {
            $stmtDoc->bindValue(':curDoc', $currentDoctorId, PDO::PARAM_INT);
        }
        $stmtDoc->execute();
        $doctors = $stmtDoc->fetchAll();

        $pageTitle       = 'Chi tiết lịch hẹn #' . $id;
        $view            = __DIR__ . '/../views/receptionist/appointment_detail.php';
        $userView        = $user;
        $appointmentView = $appointment;
        $doctorsView     = $doctors;
        $errorView       = $error;
        $successView     = $success;

        include __DIR__ . '/../views/layouts/receptionist_layout.php';
    }

    /* ================== DANH SÁCH HÓA ĐƠN (LỄ TÂN) ================== */

    public function invoices()
    {
        $this->requireReceptionistLogin();
        $pdo = getPDO();

        $userId = (int)$_SESSION['user_id'];
        $user   = User::findById($userId);

        $error   = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $invoiceId      = (int)($_POST['invoice_id'] ?? 0);
            $paymentStatus  = $_POST['payment_status'] ?? '';
            $paymentMethod  = trim($_POST['payment_method'] ?? '');

            $allowedStatus = ['UNPAID', 'PAID', 'PARTIAL'];

            if ($invoiceId <= 0) {
                $error = 'Mã hóa đơn không hợp lệ.';
            } elseif (!in_array($paymentStatus, $allowedStatus, true)) {
                $error = 'Trạng thái thanh toán không hợp lệ.';
            } else {
                try {
                    $sqlUp = "
                        UPDATE invoices
                        SET payment_status = :st,
                            payment_method = :pm
                        WHERE invoice_id = :id
                    ";
                    $stmtUp = $pdo->prepare($sqlUp);
                    $stmtUp->execute([
                        'st' => $paymentStatus,
                        'pm' => $paymentMethod ?: null,
                        'id' => $invoiceId,
                    ]);

                    $success = 'Đã cập nhật thanh toán cho hóa đơn #' . $invoiceId;
                } catch (PDOException $e) {
                    $error = 'Lỗi khi cập nhật hóa đơn: ' . $e->getMessage();
                }
            }
        }

        $status  = $_GET['status'] ?? '';
        $keyword = trim($_GET['q'] ?? '');
        $date    = $_GET['date'] ?? '';

        $page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $pageSize = 10;
        $offset   = ($page - 1) * $pageSize;

        $where  = "1=1";
        $params = [];

        if ($status !== '') {
            $where .= " AND i.payment_status = :status";
            $params['status'] = $status;
        }
        if ($date !== '') {
            $where .= " AND DATE(i.created_at) = :date";
            $params['date'] = $date;
        }
        if ($keyword !== '') {
            $where .= " AND (p.full_name LIKE :kw OR p.phone LIKE :kw OR i.invoice_id = :id_kw)";
            $params['kw']    = '%' . $keyword . '%';
            $params['id_kw'] = (int)$keyword ?: 0;
        }

        $sqlCount = "
            SELECT COUNT(*)
            FROM invoices i
            JOIN patients p ON i.patient_id = p.patient_id
            LEFT JOIN medical_records mr ON i.record_id = mr.record_id
            LEFT JOIN doctors d         ON mr.doctor_id = d.doctor_id
            LEFT JOIN users   udoc      ON d.user_id = udoc.user_id
            WHERE $where
        ";
        $stmt = $pdo->prepare($sqlCount);
        $stmt->execute($params);
        $totalRows  = (int)$stmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalRows / $pageSize));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $pageSize;

        $sql = "
            SELECT
                i.invoice_id,
                i.created_at,
                i.total_amount,
                i.discount,
                i.final_amount,
                i.payment_status,
                i.payment_method,
                i.note,
                p.full_name AS patient_name,
                p.phone     AS patient_phone,
                udoc.full_name AS doctor_name
            FROM invoices i
            JOIN patients p ON i.patient_id = p.patient_id
            LEFT JOIN medical_records mr ON i.record_id = mr.record_id
            LEFT JOIN doctors d         ON mr.doctor_id = d.doctor_id
            LEFT JOIN users   udoc      ON d.user_id = udoc.user_id
            WHERE $where
            ORDER BY i.created_at DESC, i.invoice_id DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit',  $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
        $stmt->execute();
        $invoices = $stmt->fetchAll();

        $pageTitle        = 'Quản lý hóa đơn';
        $view             = __DIR__ . '/../views/receptionist/invoices.php';
        $userView         = $user;
        $statusView       = $status;
        $keywordView      = $keyword;
        $dateView         = $date;
        $currentPage      = $page;
        $totalPagesView   = $totalPages;
        $totalRowsView    = $totalRows;
        $invoicesView     = $invoices;
        $errorView        = $error;
        $successView      = $success;

        include __DIR__ . '/../views/layouts/receptionist_layout.php';
    }

    public function invoiceDetail()
    {
        $this->requireReceptionistLogin();
        $pdo = getPDO();

        $userId = (int)$_SESSION['user_id'];
        $user   = User::findById($userId);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo "Mã hóa đơn không hợp lệ.";
            exit;
        }

        $error   = '';
        $success = '';

        // Cho phép cập nhật thanh toán ngay tại màn chi tiết
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentStatus = $_POST['payment_status'] ?? '';
            $paymentMethod = trim($_POST['payment_method'] ?? '');
            $allowedStatus = ['UNPAID', 'PAID', 'PARTIAL'];

            if (!in_array($paymentStatus, $allowedStatus, true)) {
                $error = 'Trạng thái thanh toán không hợp lệ.';
            } else {
                try {
                    $stmtUp = $pdo->prepare("
                    UPDATE invoices
                    SET payment_status = :st,
                        payment_method = :pm
                    WHERE invoice_id = :id
                ");
                    $stmtUp->execute([
                        'st' => $paymentStatus,
                        'pm' => $paymentMethod ?: null,
                        'id' => $id,
                    ]);
                    $success = 'Đã cập nhật thanh toán cho hóa đơn #' . $id;
                } catch (PDOException $e) {
                    $error = 'Lỗi khi cập nhật hóa đơn: ' . $e->getMessage();
                }
            }
        }

        // Lấy thông tin hóa đơn
        $sql = "
        SELECT
            i.invoice_id,
            i.record_id,
            i.patient_id,
            i.created_at,
            i.total_amount,
            i.discount,
            i.final_amount,
            i.payment_status,
            i.payment_method,
            i.note,

            p.full_name  AS patient_name,
            p.phone      AS patient_phone,
            p.email      AS patient_email,
            p.address    AS patient_address,

            mr.visit_date,
            mr.chief_complaint,
            mr.diagnosis,
            mr.treatment_plan,

            udoc.full_name AS doctor_name
        FROM invoices i
        JOIN patients p ON i.patient_id = p.patient_id
        LEFT JOIN medical_records mr ON i.record_id = mr.record_id
        LEFT JOIN doctors d         ON mr.doctor_id = d.doctor_id
        LEFT JOIN users   udoc      ON d.user_id = udoc.user_id
        WHERE i.invoice_id = :id
        LIMIT 1
    ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            echo "Không tìm thấy hóa đơn.";
            exit;
        }

        // ✅ LẤY CHI TIẾT invoice_items
        $sqlItems = "
        SELECT
            ii.quantity,
            ii.unit_price,
            ii.line_total,
            s.service_name,
            s.unit
        FROM invoice_items ii
        JOIN services s ON ii.service_id = s.service_id
        WHERE ii.invoice_id = :iid
        ORDER BY ii.item_id ASC
    ";
        $stmtItems = $pdo->prepare($sqlItems);
        $stmtItems->execute(['iid' => $id]);
        $items = $stmtItems->fetchAll();

        $pageTitle      = 'Chi tiết hóa đơn #' . $id;
        $view           = __DIR__ . '/../views/receptionist/invoice_detail.php';
        $invoiceView    = $invoice;
        $invoiceItemsView = $items;
        $userView       = $user;
        $errorView      = $error;
        $successView    = $success;

        include __DIR__ . '/../views/layouts/receptionist_layout.php';
    }
}

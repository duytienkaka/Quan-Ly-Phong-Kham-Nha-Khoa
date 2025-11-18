<?php
require_once __DIR__ . '/../models/User.php';

class DoctorController
{
    private function requireDoctorLogin()
    {
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'doctor') {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }

    private function getCurrentDoctor(PDO $pdo)
    {
        $userId = (int)$_SESSION['user_id'];

        $stmt = $pdo->prepare("
            SELECT d.*
            FROM doctors d
            WHERE d.user_id = :uid
            LIMIT 1
        ");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetch();
    }

    /* ================= DASHBOARD BÁC SĨ ================= */

    public function dashboard()
    {
        $this->requireDoctorLogin();
        $pdo = getPDO();

        $userId = (int)$_SESSION['user_id'];
        $user   = User::findById($userId);

        // Lấy thông tin doctor
        $doctor = $this->getCurrentDoctor($pdo);
        if (!$doctor) {
            // Chưa có record trong bảng doctors -> báo admin cấu hình
            $pageTitle = 'Dashboard bác sĩ';
            $view      = __DIR__ . '/../views/doctor/dashboard_no_doctor.php';
            $userView  = $user;

            include __DIR__ . '/../views/layouts/doctor_layout.php';
            return;
        }

        $doctorId = (int)$doctor['doctor_id'];

        // Hôm nay
        $today = date('Y-m-d');

        // Thống kê số lượng lịch hẹn theo trạng thái
        $sqlStats = "
            SELECT status, COUNT(*) AS total
            FROM appointments
            WHERE doctor_id = :did
              AND DATE(appointment_date) = :today
            GROUP BY status
        ";
        $stmt = $pdo->prepare($sqlStats);
        $stmt->execute([
            'did'   => $doctorId,
            'today' => $today,
        ]);
        $statsRaw = $stmt->fetchAll();

        // Chuẩn hóa thống kê cho đủ key
        $statuses = ['WAITING', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED', 'NO_SHOW'];
        $stats = array_fill_keys($statuses, 0);
        foreach ($statsRaw as $row) {
            $st = $row['status'];
            if (isset($stats[$st])) {
                $stats[$st] = (int)$row['total'];
            }
        }

        // Danh sách lịch hẹn hôm nay của bác sĩ
        $sqlList = "
            SELECT
                a.appointment_id,
                a.appointment_date,
                a.status,
                a.note,
                p.full_name AS patient_name,
                p.phone     AS patient_phone
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            WHERE a.doctor_id = :did
              AND DATE(a.appointment_date) = :today
            ORDER BY a.appointment_date ASC
        ";
        $stmt = $pdo->prepare($sqlList);
        $stmt->execute([
            'did'   => $doctorId,
            'today' => $today,
        ]);
        $appointmentsToday = $stmt->fetchAll();

        $pageTitle           = 'Dashboard bác sĩ';
        $view                = __DIR__ . '/../views/doctor/dashboard.php';
        $userView            = $user;
        $doctorView          = $doctor;
        $statsView           = $stats;
        $appointmentsTodayView = $appointmentsToday;

        include __DIR__ . '/../views/layouts/doctor_layout.php';
    }

    /* =============== DANH SÁCH LỊCH HẸN CỦA BÁC SĨ =============== */

    public function appointments()
    {
        $this->requireDoctorLogin();
        $pdo = getPDO();

        $userId = (int)$_SESSION['user_id'];
        $user   = User::findById($userId);

        $doctor = $this->getCurrentDoctor($pdo);
        if (!$doctor) {
            $pageTitle = 'Lịch hẹn của bác sĩ';
            $view      = __DIR__ . '/../views/doctor/dashboard_no_doctor.php';
            $userView  = $user;

            include __DIR__ . '/../views/layouts/doctor_layout.php';
            return;
        }
        $doctorId = (int)$doctor['doctor_id'];

        // Bộ lọc
        $date    = $_GET['date'] ?? '';
        $status  = $_GET['status'] ?? '';
        $keyword = trim($_GET['q'] ?? '');

        if ($date === '') {
            $date = date('Y-m-d'); // mặc định hôm nay
        }

        $page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $pageSize = 10;
        $offset   = ($page - 1) * $pageSize;

        $where  = "a.doctor_id = :did";
        $params = ['did' => $doctorId];

        if ($date !== '') {
            $where .= " AND DATE(a.appointment_date) = :date";
            $params['date'] = $date;
        }
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

        // Lấy danh sách
        $sql = "
            SELECT
                a.appointment_id,
                a.appointment_date,
                a.status,
                a.note,
                p.patient_id,
                p.full_name AS patient_name,
                p.phone     AS patient_phone
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            WHERE $where
            ORDER BY a.appointment_date ASC
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

        $pageTitle       = 'Lịch hẹn của tôi';
        $view            = __DIR__ . '/../views/doctor/appointments.php';
        $userView        = $user;
        $doctorView      = $doctor;
        $dateView        = $date;
        $statusView      = $status;
        $keywordView     = $keyword;
        $appointmentsView = $appointments;
        $currentPage     = $page;
        $totalPagesView  = $totalPages;
        $totalRowsView   = $totalRows;

        include __DIR__ . '/../views/layouts/doctor_layout.php';
    }

    public function appointmentDetail()
    {
        $this->requireDoctorLogin();
        $pdo = getPDO();

        $userId = (int)$_SESSION['user_id'];
        $user   = User::findById($userId);

        // Lấy doctor hiện tại
        $doctor = $this->getCurrentDoctor($pdo);
        if (!$doctor) {
            echo "Tài khoản bác sĩ chưa được cấu hình trong bảng doctors.";
            exit;
        }
        $doctorId = (int)$doctor['doctor_id'];

        // Lấy id lịch hẹn
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo "Mã lịch hẹn không hợp lệ.";
            exit;
        }

        $error   = '';
        $success = '';


        $sql = "
            SELECT
                a.appointment_id,
                a.appointment_date,
                a.status       AS appointment_status,
                a.note         AS appointment_note,

                p.patient_id,
                p.full_name    AS patient_name,
                p.phone        AS patient_phone,
                p.email        AS patient_email,
                p.address      AS patient_address,

                mr.record_id,
                mr.visit_date,
                mr.chief_complaint,
                mr.clinical_note,
                mr.diagnosis,
                mr.treatment_plan,
                mr.extra_note,
                mr.suggested_next_visit
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN medical_records mr ON mr.appointment_id = a.appointment_id
            WHERE a.appointment_id = :id
              AND a.doctor_id = :did
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id'  => $id,
            'did' => $doctorId,
        ]);
        $appointment = $stmt->fetch();

        if (!$appointment) {
            echo "Không tìm thấy lịch hẹn hoặc lịch hẹn không thuộc bác sĩ này.";
            exit;
        }

        $patientId = (int)$appointment['patient_id'];
        $recordId  = $appointment['record_id'] ?? null;


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $actionType = $_POST['action_type'] ?? 'save_record';

            if ($actionType === 'save_record') {
                $chief_complaint = trim($_POST['chief_complaint'] ?? '');
                $clinical_note   = trim($_POST['clinical_note'] ?? '');
                $diagnosis       = trim($_POST['diagnosis'] ?? '');
                $treatment_plan  = trim($_POST['treatment_plan'] ?? '');
                $extra_note      = trim($_POST['extra_note'] ?? '');
                $next_visit      = $_POST['suggested_next_visit'] ?? null;
                if ($next_visit === '') {
                    $next_visit = null;
                }

                // trạng thái lịch hẹn bác sĩ muốn set
                $newAppStatus = $_POST['appointment_status'] ?? '';
                $allowedStatus = ['WAITING', 'IN_PROGRESS', 'COMPLETED'];

                try {
                    $pdo->beginTransaction();

                    if ($recordId) {
                        // Cập nhật hồ sơ
                        $stmtUp = $pdo->prepare("
                            UPDATE medical_records
                            SET chief_complaint      = :cc,
                                clinical_note        = :cn,
                                diagnosis            = :dg,
                                treatment_plan       = :tp,
                                extra_note           = :en,
                                suggested_next_visit = :nv,
                                updated_at           = NOW()
                            WHERE record_id = :rid
                        ");
                        $stmtUp->execute([
                            'cc'  => $chief_complaint,
                            'cn'  => $clinical_note,
                            'dg'  => $diagnosis,
                            'tp'  => $treatment_plan,
                            'en'  => $extra_note,
                            'nv'  => $next_visit,
                            'rid' => $recordId,
                        ]);
                    } else {
                        // Tạo hồ sơ mới
                        $stmtIns = $pdo->prepare("
                            INSERT INTO medical_records
                                (appointment_id, patient_id, doctor_id, visit_date,
                                 chief_complaint, clinical_note, diagnosis, treatment_plan,
                                 extra_note, suggested_next_visit, created_at)
                            VALUES
                                (:aid, :pid, :did, NOW(),
                                 :cc, :cn, :dg, :tp,
                                 :en, :nv, NOW())
                        ");
                        $stmtIns->execute([
                            'aid' => $id,
                            'pid' => $patientId,
                            'did' => $doctorId,
                            'cc'  => $chief_complaint,
                            'cn'  => $clinical_note,
                            'dg'  => $diagnosis,
                            'tp'  => $treatment_plan,
                            'en'  => $extra_note,
                            'nv'  => $next_visit,
                        ]);
                        $recordId = (int)$pdo->lastInsertId();
                    }

                    // Cập nhật trạng thái lịch hẹn nếu hợp lệ
                    if (in_array($newAppStatus, $allowedStatus, true)) {
                        $stmtStatus = $pdo->prepare("
                            UPDATE appointments
                            SET status = :st
                            WHERE appointment_id = :id
                        ");
                        $stmtStatus->execute([
                            'st' => $newAppStatus,
                            'id' => $id,
                        ]);
                        $appointment['appointment_status'] = $newAppStatus;
                    }

                    $pdo->commit();
                    $success = 'Đã lưu hồ sơ khám / cập nhật lịch hẹn.';
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $error = 'Lỗi khi lưu hồ sơ khám: ' . $e->getMessage();
                }
            }
        }

        $sqlRecord = "
            SELECT *
            FROM medical_records
            WHERE appointment_id = :aid
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sqlRecord);
        $stmt->execute(['aid' => $id]);
        $record = $stmt->fetch();
        if ($record) {
            $appointment['record_id']            = $record['record_id'];
            $appointment['visit_date']           = $record['visit_date'];
            $appointment['chief_complaint']      = $record['chief_complaint'];
            $appointment['clinical_note']        = $record['clinical_note'];
            $appointment['diagnosis']            = $record['diagnosis'];
            $appointment['treatment_plan']       = $record['treatment_plan'];
            $appointment['extra_note']           = $record['extra_note'];
            $appointment['suggested_next_visit'] = $record['suggested_next_visit'];
        }

        $sqlHistory = "
            SELECT
                mr.record_id,
                mr.visit_date,
                mr.chief_complaint,
                mr.diagnosis,
                mr.treatment_plan
            FROM medical_records mr
            WHERE mr.patient_id = :pid
            ORDER BY mr.visit_date DESC
            LIMIT 10
        ";
        $stmt = $pdo->prepare($sqlHistory);
        $stmt->execute(['pid' => $patientId]);
        $history = $stmt->fetchAll();

        $pageTitle       = 'Khám bệnh - lịch hẹn #' . $id;
        $view            = __DIR__ . '/../views/doctor/appointment_detail.php';
        $userView        = $user;
        $doctorView      = $doctor;
        $appointmentView = $appointment;
        $historyView     = $history;
        $errorView       = $error;
        $successView     = $success;

        include __DIR__ . '/../views/layouts/doctor_layout.php';
    }
}

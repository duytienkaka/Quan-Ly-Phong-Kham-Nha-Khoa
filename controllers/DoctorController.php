<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Invoice.php';
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

        $doctor = $this->getCurrentDoctor($pdo);
        if (!$doctor) {
            $pageTitle = 'Dashboard bác sĩ';
            $view      = __DIR__ . '/../views/doctor/dashboard_no_doctor.php';
            $userView  = $user;

            include __DIR__ . '/../views/layouts/doctor_layout.php';
            return;
        }

        $doctorId = (int)$doctor['doctor_id'];
        $today    = date('Y-m-d');
        // Thống kê lịch hẹn hôm nay
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

        $statuses = ['WAITING', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED', 'NO_SHOW'];
        $stats    = array_fill_keys($statuses, 0);
        foreach ($statsRaw as $row) {
            $st = $row['status'];
            if (isset($stats[$st])) {
                $stats[$st] = (int)$row['total'];
            }
        }

        // Danh sách lịch hẹn hôm nay
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

        $pageTitle             = 'Dashboard bác sĩ';
        $view                  = __DIR__ . '/../views/doctor/dashboard.php';
        $userView              = $user;
        $doctorView            = $doctor;
        $statsView             = $stats;
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

        if ($page > $totalPages) {
            $page = $totalPages;
        }
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

        $pageTitle        = 'Lịch hẹn của tôi';
        $view             = __DIR__ . '/../views/doctor/appointments.php';
        $userView         = $user;
        $doctorView       = $doctor;
        $dateView         = $date;
        $statusView       = $status;
        $keywordView      = $keyword;
        $appointmentsView = $appointments;
        $currentPage      = $page;
        $totalPagesView   = $totalPages;
        $totalRowsView    = $totalRows;

        include __DIR__ . '/../views/layouts/doctor_layout.php';
    }

    /* =============== CHI TIẾT LỊCH HẸN + KHÁM BỆNH =============== */

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

        /* ---------- LOAD lịch hẹn + hồ sơ ban đầu ---------- */
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

        /* ---------- XỬ LÝ POST ---------- */

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

                $newAppStatus  = $_POST['appointment_status'] ?? '';
                $allowedStatus = ['WAITING', 'IN_PROGRESS', 'COMPLETED'];

                try {
                    $pdo->beginTransaction();

                    if ($recordId) {
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

                    // Cập nhật trạng thái lịch hẹn
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
            } elseif ($actionType === 'save_invoice') {
                if (!$recordId) {
                    $error = 'Vui lòng lưu hồ sơ khám trước khi tạo hóa đơn.';
                } else {
                    // quantities[service_id] = số lượng
                    $quantitiesRaw = $_POST['quantities'] ?? [];
                    $selected = [];

                    if (is_array($quantitiesRaw)) {
                        foreach ($quantitiesRaw as $sidStr => $qtyRaw) {
                            $sid = (int)$sidStr;
                            $qty = (int)$qtyRaw;
                            if ($sid > 0 && $qty > 0) {
                                $selected[$sid] = $qty;  // chỉ giữ dịch vụ có số lượng > 0
                            }
                        }
                    }

                    if (empty($selected)) {
                        $error = 'Vui lòng nhập số lượng > 0 cho ít nhất một dịch vụ.';
                    } else {
                        $discountRaw = $_POST['discount'] ?? '0';
                        $invoiceNote = trim($_POST['invoice_note'] ?? '');

                        try {
                            $serviceIds = array_keys($selected);
                            $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));

                            $sqlSrv = "
                                SELECT 
                                    service_id, 
                                    service_name, 
                                    unit_price AS price,
                                    unit
                                FROM services
                                WHERE service_id IN ($placeholders)
                                AND is_active = 1
                            ";
                            $stmtSrv = $pdo->prepare($sqlSrv);
                            $stmtSrv->execute($serviceIds);
                            $rows = $stmtSrv->fetchAll();

                            if (empty($rows)) {
                                $error = 'Không tìm thấy dịch vụ tương ứng trong hệ thống.';
                            } else {
                                $total = 0;
                                $names = [];

                                foreach ($rows as $r) {
                                    $sid   = (int)$r['service_id'];
                                    $qty   = $selected[$sid] ?? 0;
                                    if ($qty <= 0) continue;

                                    $price      = (float)$r['price'];
                                    $lineTotal  = $price * $qty;
                                    $total     += $lineTotal;

                                    $unitLabel  = $r['unit'] ? (' ' . $r['unit']) : '';
                                    $names[]    = $r['service_name'] . ' x' . $qty . $unitLabel;
                                }

                                $discount = (float)$discountRaw;
                                if ($discount < 0) $discount = 0;
                                if ($discount > $total) $discount = $total;
                                $final = $total - $discount;

                                $servicesText = 'Dịch vụ: ' . implode(', ', $names);
                                if ($invoiceNote !== '') {
                                    $fullNote = $servicesText . "\nGhi chú: " . $invoiceNote;
                                } else {
                                    $fullNote = $servicesText;
                                }

                                // kiểm tra invoice đã tồn tại chưa
                                $stmtCheck = $pdo->prepare("
                                    SELECT *
                                    FROM invoices
                                    WHERE record_id = :rid
                                    LIMIT 1
                                ");
                                $stmtCheck->execute(['rid' => $recordId]);
                                $invoice = $stmtCheck->fetch();

                                if ($invoice) {
                                    $stmtUp = $pdo->prepare("
                                        UPDATE invoices
                                        SET total_amount = :total,
                                            discount     = :disc,
                                            final_amount = :final,
                                            note         = :note
                                        WHERE invoice_id = :id
                                    ");
                                    $stmtUp->execute([
                                        'total' => $total,
                                        'disc'  => $discount,
                                        'final' => $final,
                                        'note'  => $fullNote,
                                        'id'    => $invoice['invoice_id'],
                                    ]);
                                    $success = 'Đã cập nhật hóa đơn cho lần khám này.';
                                } else {
                                    $stmtIns = $pdo->prepare("
                                        INSERT INTO invoices
                                            (record_id, patient_id, total_amount, discount, final_amount,
                                            payment_status, payment_method, note, created_at)
                                        VALUES
                                            (:rid, :pid, :total, :disc, :final,
                                            'UNPAID', NULL, :note, NOW())
                                    ");
                                    $stmtIns->execute([
                                        'rid'   => $recordId,
                                        'pid'   => $patientId,
                                        'total' => $total,
                                        'disc'  => $discount,
                                        'final' => $final,
                                        'note'  => $fullNote,
                                    ]);
                                    $success = 'Đã tạo hóa đơn cho lần khám này.';
                                }
                            }
                        } catch (PDOException $e) {
                            $error = 'Lỗi khi xử lý hóa đơn: ' . $e->getMessage();
                        }
                    }
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
            $recordId = (int)$record['record_id'];
            $appointment['record_id']            = $record['record_id'];
            $appointment['visit_date']           = $record['visit_date'];
            $appointment['chief_complaint']      = $record['chief_complaint'];
            $appointment['clinical_note']        = $record['clinical_note'];
            $appointment['diagnosis']            = $record['diagnosis'];
            $appointment['treatment_plan']       = $record['treatment_plan'];
            $appointment['extra_note']           = $record['extra_note'];
            $appointment['suggested_next_visit'] = $record['suggested_next_visit'];
        }

        // Lịch sử khám
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

        // Hóa đơn hiện tại
        $invoice = null;
        if ($recordId) {
            $stmt = $pdo->prepare("
                SELECT *
                FROM invoices
                WHERE record_id = :rid
                LIMIT 1
            ");
            $stmt->execute(['rid' => $recordId]);
            $invoice = $stmt->fetch();
        }

        $services = [];
        try {
            $stmt = $pdo->query("
                SELECT 
                    service_id,
                    service_name,
                    unit_price AS price,
                    unit
                FROM services
                WHERE is_active = 1
                ORDER BY service_name ASC
            ");
            $services = $stmt->fetchAll();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        $pageTitle       = 'Khám bệnh - lịch hẹn #' . $id;
        $view            = __DIR__ . '/../views/doctor/appointment_detail.php';
        $userView        = $user;
        $doctorView      = $doctor;
        $appointmentView = $appointment;
        $historyView     = $history;
        $invoiceView     = $invoice;
        $servicesView    = $services;
        $errorView       = $error;
        $successView     = $success;

        include __DIR__ . '/../views/layouts/doctor_layout.php';
    }
    public function saveInvoice()
    {
        $this->requireDoctorLogin();
        $pdo = getPDO();

        // Lấy user hiện tại
        $doctorUserId = (int)$_SESSION['user_id'];
        $doctorUser   = User::findById($doctorUserId);

        // Lấy appointment_id từ form (hidden)
        $appointmentId = isset($_POST['appointment_id'])
            ? (int)$_POST['appointment_id']
            : (int)($_GET['id'] ?? 0);

        if ($appointmentId <= 0) {
            die('Mã lịch hẹn không hợp lệ khi lưu hóa đơn.');
        }

        // Lấy record khám (medical_record) tương ứng với appointment này
        $sqlRec = "
        SELECT record_id, patient_id
        FROM medical_records
        WHERE appointment_id = :aid
        LIMIT 1
    ";
        $stmtRec = $pdo->prepare($sqlRec);
        $stmtRec->execute(['aid' => $appointmentId]);
        $rec = $stmtRec->fetch();

        if (!$rec) {
            // Chưa có hồ sơ khám => không cho tạo hóa đơn
            die('Chưa có hồ sơ khám cho lịch hẹn này. Vui lòng lưu hồ sơ khám trước.');
        }

        $recordId  = (int)$rec['record_id'];
        $patientId = (int)$rec['patient_id'];

        // Dữ liệu từ form
        $quantities = $_POST['quantities'] ?? [];        // mảng service_id => qty
        $discount   = (float)($_POST['discount'] ?? 0);  // giảm giá
        $note       = trim($_POST['invoice_note'] ?? '');

        try {
            $pdo->beginTransaction();

            // 1) Tìm hóa đơn hiện có (nếu có)
            $sqlInv = "
            SELECT invoice_id
            FROM invoices
            WHERE record_id = :rid
              AND patient_id = :pid
            LIMIT 1
        ";
            $stmtInv = $pdo->prepare($sqlInv);
            $stmtInv->execute([
                'rid' => $recordId,
                'pid' => $patientId,
            ]);
            $invoice = $stmtInv->fetch();

            if ($invoice) {
                // Đã có hóa đơn: xoá item cũ để insert lại theo form mới
                $invoiceId = (int)$invoice['invoice_id'];

                $stmtDelItems = $pdo->prepare("
                DELETE FROM invoice_items
                WHERE invoice_id = :iid
            ");
                $stmtDelItems->execute(['iid' => $invoiceId]);
            } else {
                // Chưa có hóa đơn: tạo mới với total = 0, final = 0
                $stmtInsInv = $pdo->prepare("
                INSERT INTO invoices
                    (record_id, patient_id,
                     total_amount, discount, final_amount,
                     payment_status, payment_method,
                     note, created_at)
                VALUES
                    (:rid, :pid,
                     0, :disc, 0,
                     'UNPAID', NULL,
                     :note, NOW())
            ");
                $stmtInsInv->execute([
                    'rid'  => $recordId,
                    'pid'  => $patientId,
                    'disc' => $discount,
                    'note' => $note,
                ]);
                $invoiceId = (int)$pdo->lastInsertId();
            }

            // 2) Insert lại các dòng invoice_items theo dịch vụ đã chọn
            $total = 0;

            foreach ($quantities as $serviceId => $qty) {
                $serviceId = (int)$serviceId;
                $qty       = (int)$qty;

                if ($serviceId <= 0 || $qty <= 0) {
                    continue;
                }

                // Lấy thông tin dịch vụ
                $stmtSvc = $pdo->prepare("
                SELECT service_id, price
                FROM services
                WHERE service_id = :sid
                LIMIT 1
            ");
                $stmtSvc->execute(['sid' => $serviceId]);
                $svc = $stmtSvc->fetch();

                if (!$svc) {
                    continue; // dịch vụ không tồn tại
                }

                $unitPrice = (float)$svc['price'];
                $lineTotal = $unitPrice * $qty;
                $total    += $lineTotal;

                // Thêm dòng chi tiết hóa đơn
                $stmtItem = $pdo->prepare("
                INSERT INTO invoice_items
                    (invoice_id, service_id, quantity,
                     unit_price, line_total, created_at)
                VALUES
                    (:iid, :sid, :qty,
                     :uprice, :ltotal, NOW())
            ");
                $stmtItem->execute([
                    'iid'    => $invoiceId,
                    'sid'    => $serviceId,
                    'qty'    => $qty,
                    'uprice' => $unitPrice,
                    'ltotal' => $lineTotal,
                ]);
            }

            // 3) Cập nhật lại tổng tiền trong bảng invoices
            $final = max(0, $total - $discount);

            $stmtUpInv = $pdo->prepare("
            UPDATE invoices
            SET total_amount = :total,
                discount     = :disc,
                final_amount = :final,
                note         = :note
            WHERE invoice_id = :iid
        ");
            $stmtUpInv->execute([
                'total' => $total,
                'disc'  => $discount,
                'final' => $final,
                'note'  => $note,
                'iid'   => $invoiceId,
            ]);

            $pdo->commit();

            // Quay lại màn chi tiết lịch hẹn bác sĩ
            header('Location: index.php?controller=doctor&action=appointmentDetail&id=' . $appointmentId);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            die('Lỗi khi xử lý hóa đơn: ' . $e->getMessage());
        }
    }
}

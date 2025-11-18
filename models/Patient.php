<?php
// models/Patient.php
require_once __DIR__ . '/../config/database.php';

class Patient
{
    public static function findByUserId($userId)
    {
        $pdo = getPDO();
        $sql = "SELECT * FROM patients WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public static function createForUser($userId, $data)
    {
        $pdo = getPDO();
        $sql = "INSERT INTO patients (user_id, full_name, gender, date_of_birth, phone, email, address, note, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $userId,
            $data['full_name'],
            $data['gender'],
            $data['date_of_birth'],
            $data['phone'],
            $data['email'],
            $data['address'],
            $data['note'],
        ]);
    }

    public static function updateForUser($userId, $data)
    {
        $pdo = getPDO();
        $sql = "UPDATE patients
                   SET full_name = ?, gender = ?, date_of_birth = ?, phone = ?, email = ?, address = ?, note = ?, updated_at = NOW()
                 WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['full_name'],
            $data['gender'],
            $data['date_of_birth'],
            $data['phone'],
            $data['email'],
            $data['address'],
            $data['note'],
            $userId,
        ]);
    }
    public static function getAppointments($patientId)
    {
        $pdo = getPDO();
        $sql = "SELECT *
            FROM appointments
            WHERE patient_id = ?
            ORDER BY appointment_date DESC, time_block DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }
}

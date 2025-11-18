<?php
// models/Doctor.php
require_once __DIR__ . '/../config/database.php';

class Doctor
{
    public static function getAllDoctors()
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE role = 'doctor' AND status = 1 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getDoctorById($doctorId)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE user_id = ? AND role = 'doctor' AND status = 1
        ");
        $stmt->execute([$doctorId]);
        return $stmt->fetch();
    }

    public static function createDoctor($fullName, $specialization, $phone, $email)
    {
        $pdo = getPDO();
        $username = strtolower(str_replace(' ', '_', $fullName)) . '_' . time();
        $password = 'Doctor@' . rand(1000, 9999);

        $stmt = $pdo->prepare("
            INSERT INTO users (username, password_hash, full_name, role, status, created_at)
            VALUES (?, ?, ?, 'doctor', 1, NOW())
        ");
        $stmt->execute([$username, $password, $fullName]);

        return $pdo->lastInsertId();
    }
}

<?php
// models/User.php
require_once __DIR__ . '/../config/database.php';

class User
{
    public static function findByUsername($username)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public static function createPatientUser($username, $password)
    {
        $pdo = getPDO();
        $hash = $password;

        $stmt = $pdo->prepare("
            INSERT INTO users (username, password_hash, full_name, role, status, created_at)
            VALUES (?, ?, ?, 'patient', 1, NOW())
        ");
        $stmt->execute([$username, $hash, $username]);

        return $pdo->lastInsertId();
    }
    public static function findById($userId)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
}

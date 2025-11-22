<?php
function getPDO() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host = 'localhost';
    $db   = 'dental_clinic';
    $user = 'root';
    $pass = '';           
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (PDOException $e) {
        die('Lỗi kết nối database: ' . $e->getMessage());
    }
}

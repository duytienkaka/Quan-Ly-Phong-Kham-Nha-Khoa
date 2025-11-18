<?php
// models/Invoice.php
require_once __DIR__ . '/../config/database.php';

class Invoice
{
    public static function getByPatientId($patientId)
    {
        $pdo = getPDO();
        $sql = "SELECT *
                FROM invoices
                WHERE patient_id = ?
                ORDER BY created_at DESC, invoice_id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }
}

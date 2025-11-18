<?php
// models/MedicalRecord.php
require_once __DIR__ . '/../config/database.php';

class MedicalRecord
{
    public static function getByPatientId($patientId)
    {
        $pdo = getPDO();
        $sql = "SELECT *
                FROM medical_records
                WHERE patient_id = ?
                ORDER BY visit_date DESC, record_id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }
}

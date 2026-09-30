<?php
require_once __DIR__ . "/CRUD.php";

class Appointment extends CRUD
{
    public function selectWithDetails()
    {
        $sql = "SELECT
                    appointments.*,
                    patients.first_name AS patient_first_name,
                    patients.last_name AS patient_last_name,
                    doctors.first_name AS doctor_first_name,
                    doctors.last_name AS doctor_last_name
                FROM appointments
                INNER JOIN patients
                    ON appointments.patient_id = patients.id
                INNER JOIN doctors
                    ON appointments.doctor_id = doctors.id
                ORDER BY appointments.starts_at ASC";

        $stmt = $this->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
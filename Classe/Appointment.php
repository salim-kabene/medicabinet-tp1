<?php

require_once __DIR__ . "/CRUD.php";

class Appointment extends CRUD
{
    public function selectWithDetails($date = "", $status = "")
    {
        $conditions = [];

        if ($date !== "") {
            $conditions[] = "DATE(appointments.starts_at) = :date";
        }

        if ($status !== "") {
            $conditions[] = "appointments.status = :status";
        }

        $where = "";

        if (!empty($conditions)) {
            $where = "WHERE " . implode(" AND ", $conditions);
        }

        $sql = "SELECT
                    appointments.id,
                    appointments.starts_at,
                    appointments.status,
                    patients.first_name AS patient_first_name,
                    patients.last_name AS patient_last_name,
                    doctors.first_name AS doctor_first_name,
                    doctors.last_name AS doctor_last_name,
                    GROUP_CONCAT(
                        services.name
                        ORDER BY services.name
                        SEPARATOR ' + '
                    ) AS service_names
                FROM appointments
                INNER JOIN patients
                    ON appointments.patient_id = patients.id
                INNER JOIN doctors
                    ON appointments.doctor_id = doctors.id
                LEFT JOIN appointment_service
                    ON appointments.id = appointment_service.appointment_id
                LEFT JOIN services
                    ON appointment_service.service_id = services.id
                $where
                GROUP BY
                    appointments.id,
                    appointments.starts_at,
                    appointments.status,
                    patients.first_name,
                    patients.last_name,
                    doctors.first_name,
                    doctors.last_name
                ORDER BY appointments.starts_at ASC";

        $stmt = $this->prepare($sql);

        if ($date !== "") {
            $stmt->bindValue(":date", $date);
        }

        if ($status !== "") {
            $stmt->bindValue(":status", $status);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
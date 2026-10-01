<?php

require_once __DIR__ . "/CRUD.php";

class Appointment extends CRUD
{
    // Table principale utilisée par ce modèle.
    protected string $table = "appointments";

    public function selectWithDetails($date = "", $status = "", $patientId = null, $appointmentId = null)
    {
        $conditions = [];
        if ($date !== "") {
            $conditions[] = "DATE(appointments.starts_at) = :date";
        }
        if ($status !== "") {
            $conditions[] = "appointments.status = :status";
        }
        if ($patientId !== null) {
            $conditions[] = "appointments.patient_id = :patient_id";
        }
        if ($appointmentId !== null) {
            $conditions[] = "appointments.id = :appointment_id";
        }
        $where = "";
        if (!empty($conditions)) {
            $where = "WHERE " . implode(" AND ", $conditions);
        }
        $sql = "SELECT appointments.id, appointments.starts_at, appointments.status,
                    patients.first_name AS patient_first_name,
                    patients.last_name AS patient_last_name,
                    doctors.first_name AS doctor_first_name,
                    doctors.last_name AS doctor_last_name,
                    GROUP_CONCAT(services.name ORDER BY services.name SEPARATOR ' + ') AS service_names
                FROM {$this->table} AS appointments
                INNER JOIN patients ON appointments.patient_id = patients.id
                INNER JOIN doctors ON appointments.doctor_id = doctors.id
                LEFT JOIN appointment_service ON appointments.id = appointment_service.appointment_id
                LEFT JOIN services ON appointment_service.service_id = services.id
                $where
                GROUP BY appointments.id, appointments.starts_at, appointments.status,
                    patients.first_name, patients.last_name, doctors.first_name, doctors.last_name
                ORDER BY appointments.starts_at ASC";
        $stmt = $this->prepare($sql);
        if ($date !== "") {
            $stmt->bindValue(":date", $date);
        }
        if ($status !== "") {
            $stmt->bindValue(":status", $status);
        }
        if ($patientId !== null) {
            $stmt->bindValue(":patient_id", $patientId, PDO::PARAM_INT);
        }
        if ($appointmentId !== null) {
            $stmt->bindValue(":appointment_id", $appointmentId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectByPatient($patientId)
    {
        return array_reverse($this->selectWithDetails("", "", $patientId));
    }

    public function findWithDetails($id)
    {
        $rows = $this->selectWithDetails("", "", null, $id);
        return $rows[0] ?? false;
    }

    public function countToday()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE DATE(starts_at) = :today";
        $stmt = $this->prepare($sql);
        $stmt->bindValue(":today", date("Y-m-d"));
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function countUpcoming()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} AS appointments
                WHERE starts_at > :now AND status IN ('planned', 'confirmed')";
        $stmt = $this->prepare($sql);
        $stmt->bindValue(":now", date("Y-m-d H:i:s"));
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    // Charger les valeurs d'un rendez-vous pour le formulaire de modification.
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Charger les cases de services à cocher pour ce rendez-vous.
    public function selectServiceIds($id)
    {
        $sql = "SELECT service_id FROM appointment_service WHERE appointment_id = :id";
        $stmt = $this->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function createWithServices($data, $serviceIds)
    {
        $this->beginTransaction();
        try {
            $appointmentId = $this->insert($this->table, $data);
            foreach ($serviceIds as $serviceId) {
                $this->insert("appointment_service", [
                    "appointment_id" => $appointmentId,
                    "service_id" => $serviceId,
                ]);
            }
            $this->commit();
            return $appointmentId;
        } catch (Throwable $exception) {
            if ($this->inTransaction()) {
                $this->rollBack();
            }
            throw $exception;
        }
    }

    public function updateWithServices($id, $data, $serviceIds)
    {
        $this->beginTransaction();
        try {
            $stmt = $this->prepare("SELECT id FROM {$this->table} WHERE id = :id FOR UPDATE");
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetchColumn() === false) {
                $this->rollBack();
                return false;
            }
            $data["id"] = $id;
            $this->update($this->table, $data);
            $stmt = $this->prepare("DELETE FROM appointment_service WHERE appointment_id = :appointment_id");
            $stmt->bindValue(":appointment_id", $id, PDO::PARAM_INT);
            $stmt->execute();
            foreach ($serviceIds as $serviceId) {
                $this->insert("appointment_service", [
                    "appointment_id" => $id,
                    "service_id" => $serviceId,
                ]);
            }
            $this->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->inTransaction()) {
                $this->rollBack();
            }
            throw $exception;
        }
    }

    public function deleteById($id)
    {
        return $this->delete($this->table, $id);
    }
}

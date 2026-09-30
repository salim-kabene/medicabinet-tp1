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
        $sql = "SELECT appointments.id, appointments.starts_at, appointments.status,
                    patients.first_name AS patient_first_name,
                    patients.last_name AS patient_last_name,
                    doctors.first_name AS doctor_first_name,
                    doctors.last_name AS doctor_last_name,
                    GROUP_CONCAT(services.name ORDER BY services.name SEPARATOR ' + ') AS service_names
                FROM appointments
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
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countToday()
    {
        $sql = "SELECT COUNT(*) FROM appointments WHERE DATE(starts_at) = :today";
        $stmt = $this->prepare($sql);
        $stmt->bindValue(":today", date("Y-m-d"));
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function countUpcoming()
    {
        $sql = "SELECT COUNT(*) FROM appointments
                WHERE starts_at > :now AND status IN ('planned', 'confirmed')";
        $stmt = $this->prepare($sql);
        $stmt->bindValue(":now", date("Y-m-d H:i:s"));
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    // Charger les valeurs d'un rendez-vous pour le formulaire de modification.
    public function findById($id)
    {
        $sql = "SELECT * FROM appointments WHERE id = :id";
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
            $appointmentId = $this->insert("appointments", $data);

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
            // Vérifier et verrouiller le rendez-vous pendant la transaction.
            $sql = "SELECT id FROM appointments WHERE id = :id FOR UPDATE";

            $stmt = $this->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->fetchColumn() === false) {
                $this->rollBack();
                return false;
            }

            // Modifier les informations du rendez-vous.
            $data["id"] = $id;
            $this->update("appointments", $data);

            // Retirer uniquement ses anciennes associations de services.
            $sql = "DELETE FROM appointment_service
                WHERE appointment_id = :appointment_id";

            $stmt = $this->prepare($sql);
            $stmt->bindValue(":appointment_id", $id, PDO::PARAM_INT);
            $stmt->execute();

            // Enregistrer la nouvelle sélection.
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
        return $this->delete("appointments", $id);
    }
}
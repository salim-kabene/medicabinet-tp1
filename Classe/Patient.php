<?php

require_once __DIR__ . "/CRUD.php";

class Patient extends CRUD
{
    public function selectAll()
    {
        return $this->select("patients", "last_name");
    }

    public function countAll()
    {
        $stmt = $this->query("SELECT COUNT(*) FROM patients");
        return (int) $stmt->fetchColumn();
    }
}

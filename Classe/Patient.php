<?php

require_once __DIR__ . "/CRUD.php";

class Patient extends CRUD
{
    public function countAll()
    {
        $sql = "SELECT COUNT(*) FROM patients";

        $stmt = $this->query($sql);

        return (int) $stmt->fetchColumn();
    }
}
<?php

require_once __DIR__ . "/CRUD.php";

class Patient extends CRUD
{
    // Table principale utilisée par ce modèle.
    protected string $table = "patients";

    public function selectAll()
    {
        return $this->select($this->table, "last_name");
    }

    public function countAll()
    {
        $stmt = $this->query("SELECT COUNT(*) FROM {$this->table}");
        return (int) $stmt->fetchColumn();
    }

    public function findById($id)
    {
        $stmt = $this->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

<?php

require_once __DIR__ . "/CRUD.php";

class Doctor extends CRUD
{
    // Table principale utilisée par ce modèle.
    protected string $table = "doctors";

    public function selectAll()
    {
        return $this->select($this->table, "last_name");
    }
}

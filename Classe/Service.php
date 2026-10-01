<?php

require_once __DIR__ . "/CRUD.php";

class Service extends CRUD
{
    // Table principale utilisée par ce modèle.
    protected string $table = "services";

    public function selectAll()
    {
        return $this->select($this->table, "name");
    }
}
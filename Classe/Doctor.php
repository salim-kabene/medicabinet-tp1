<?php

require_once __DIR__ . "/CRUD.php";

class Doctor extends CRUD
{
    public function selectAll()
    {
        return $this->select("doctors", "last_name");
    }
}

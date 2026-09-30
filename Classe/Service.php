<?php

require_once __DIR__ . "/CRUD.php";

class Service extends CRUD
{
    public function selectAll()
    {
        return $this->select("services", "name");
    }
}

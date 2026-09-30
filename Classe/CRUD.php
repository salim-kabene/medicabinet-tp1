<?php

class CRUD extends PDO
{
    public function __construct()
    {
        [
            "host" => $host,
            "port" => $port,
            "dbname" => $dbname,
            "username" => $username,
            "password" => $password,
            "charset" => $charset
        ] = require __DIR__ . "/../config/database.php";

        parent::__construct(
            "mysql:host=$host;dbname=$dbname;port=$port;charset=$charset",
            $username,
            $password
        );
    }
}
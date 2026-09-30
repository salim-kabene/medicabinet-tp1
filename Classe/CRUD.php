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

    public function select(string $table, string $field = "id", string $order = "ASC"): array
    {
        $sql = "SELECT * FROM $table ORDER BY $field $order";
        $stmt = $this->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
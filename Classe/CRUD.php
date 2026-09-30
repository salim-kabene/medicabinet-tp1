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
    public function insert($table, $data)
    {
        $fieldNames = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $sql = "INSERT INTO $table ($fieldNames)
            VALUES ($placeholders)";

        $stmt = $this->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();

        return $this->lastInsertId();
    }

    public function update($table, $data, $field = "id")
    {
        $assignments = [];

        foreach ($data as $key => $value) {
            if ($key !== $field) {
                $assignments[] = "$key = :$key";
            }
        }

        $fields = implode(", ", $assignments);

        $sql = "UPDATE $table
            SET $fields
            WHERE $field = :$field";

        $stmt = $this->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        return $stmt->execute();
    }
    public function delete($table, $value, $field = "id")
    {
        $sql = "DELETE FROM $table WHERE $field = :value";

        $stmt = $this->prepare($sql);
        $stmt->bindValue(":value", $value);

        return $stmt->execute();
    }
}
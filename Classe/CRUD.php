<?php

class CRUD extends PDO
{
    // Paramètres chargés depuis le fichier local ignoré par Git.
    private array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . "/../config/database.php";
        [
            "host" => $host,
            "port" => $port,
            "dbname" => $dbname,
            "username" => $username,
            "password" => $password,
            "charset" => $charset,
        ] = $this->config;

        parent::__construct(
            "mysql:host=$host;dbname=$dbname;port=$port;charset=$charset",
            $username,
            $password
        );

        // Une erreur SQL doit déclencher le catch et le rollback.
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function select($table, $field = "id", $order = "ASC")
    {
        // Les identifiants SQL sont fournis par notre code, jamais par GET/POST.
        $sql = "SELECT * FROM $table ORDER BY $field $order";
        $stmt = $this->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($table, $data)
    {
        $fieldNames = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO $table ($fieldNames) VALUES ($placeholders)";
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
        $sql = "UPDATE $table SET $fields WHERE $field = :$field";
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

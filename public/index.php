<?php

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../Classe/CRUD.php";

try {
    $crud = new CRUD();
} catch (PDOException $exception) {
    http_response_code(500);
    exit("Le service est temporairement indisponible. Veuillez réessayer plus tard.");
}
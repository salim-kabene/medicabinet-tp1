<?php

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../Classe/Patient.php";
require_once __DIR__ . "/../Classe/Doctor.php";
require_once __DIR__ . "/../Classe/Service.php";

try {
    $patient = new Patient();
    $patients = $patient->selectAll();
    $doctor = new Doctor();
    $doctors = $doctor->selectAll();
    $service = new Service();
    $services = $service->selectAll();
} catch (PDOException $exception) {
    http_response_code(500);
    exit("Le service est temporairement indisponible.");
}
// Depuis une fiche patient, présélectionner uniquement un patient existant.
$preselectedPatient = "";
if (isset($_GET["patient_id"])) {
    $requestedPatient = filter_input(INPUT_GET, "patient_id", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    if ($requestedPatient === false || $requestedPatient === null || !in_array($requestedPatient, array_column($patients, "id"))) {
        http_response_code(400);
        exit("Patient invalide.");
    }
    $preselectedPatient = (string) $requestedPatient;
}
$heading = "Planifier un rendez-vous";
$pageTitle = $heading . " — MédiCabinet";
$activePage = "appointments";
$formAction = "appointment-store.php";
$submitLabel = "Enregistrer le rendez-vous";
$values = [
    "id" => "",
    "patient_id" => $preselectedPatient,
    "doctor_id" => "",
    "date" => "",
    "time" => "",
    "status" => "planned",
    "reason" => "",
];
$selectedServices = [];
$errors = [];
$statusLabels = [
    "planned" => "Planifié",
    "confirmed" => "Confirmé",
    "completed" => "Terminé",
    "cancelled" => "Annulé",
    "absent" => "Absent",
];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php require __DIR__ . "/../includes/head.php"; ?>
</head>

<body>
    <div class="app-layout">
        <?php require __DIR__ . "/../includes/sidebar.php"; ?>
        <div class="app-layout__content">
            <main class="appointment-editor">
                <header class="appointment-editor__header">
                    <a class="appointment-editor__back" href="index.php"><span aria-hidden="true">←</span> Retour aux
                        rendez-vous</a>
                    <h1 class="appointment-editor__title"><?= htmlspecialchars($heading) ?></h1>
                </header>
                <?php require __DIR__ . "/../includes/appointment-form.php"; ?>
            </main>
        </div>
    </div>
</body>

</html>
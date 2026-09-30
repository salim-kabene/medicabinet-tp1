<?php

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../Classe/Patient.php";
require_once __DIR__ . "/../Classe/Doctor.php";
require_once __DIR__ . "/../Classe/Service.php";
require_once __DIR__ . "/../Classe/Appointment.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if ($id === false || $id === null) {
    http_response_code(400);
    exit("Identifiant de rendez-vous invalide.");
}
try {
    $appointment = new Appointment();
    $record = $appointment->findById($id);
    if ($record === false) {
        http_response_code(404);
        exit("Rendez-vous introuvable.");
    }
    $selectedServices = $appointment->selectServiceIds($id);
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
$startsAt = new DateTimeImmutable($record["starts_at"]);
$values = [
    "id" => $record["id"],
    "patient_id" => $record["patient_id"],
    "doctor_id" => $record["doctor_id"],
    "date" => $startsAt->format("Y-m-d"),
    "time" => $startsAt->format("H:i"),
    "status" => $record["status"],
    "reason" => $record["reason"] ?? "",
];
$heading = "Modifier le rendez-vous";
$pageTitle = $heading . " — MédiCabinet";
$activePage = "appointments";
$formAction = "appointment-update.php";
$submitLabel = "Enregistrer les modifications";
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
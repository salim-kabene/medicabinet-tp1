<?php

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../Classe/Appointment.php";
require_once __DIR__ . "/../Classe/Patient.php";
require_once __DIR__ . "/../Classe/Doctor.php";
require_once __DIR__ . "/../Classe/Service.php";

// Cette page accepte uniquement une soumission de formulaire.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: appointment-create.php");
    exit;
}

// Vérifier le jeton du formulaire.
$token = $_POST["csrf_token"] ?? "";

if (
    !is_string($token)
    || !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(403);
    exit("Formulaire expiré ou invalide. Rechargez la page du formulaire.");
}

$statusLabels = [
    "planned" => "Planifié",
    "confirmed" => "Confirmé",
    "completed" => "Terminé",
    "cancelled" => "Annulé",
    "absent" => "Absent",
];

// Construire nous-mêmes les données autorisées.
$values = [
    "id" => "",
    "patient_id" => "",
    "doctor_id" => "",
    "date" => "",
    "time" => "",
    "status" => "",
    "reason" => "",
];

$errors = [];

foreach (["patient_id", "doctor_id", "date", "time", "status", "reason"] as $field) {
    $value = $_POST[$field] ?? "";

    if (!is_string($value)) {
        $errors[] = "Format invalide pour le champ : " . $field;
    } else {
        $values[$field] = trim($value);
    }
}

// Vérifier les identifiants des services reçus.
$submittedServices = $_POST["services"] ?? [];
$selectedServices = [];

if (!is_array($submittedServices)) {
    $errors[] = "La sélection des services est invalide.";
} else {
    foreach ($submittedServices as $serviceId) {
        if (!is_string($serviceId)) {
            $errors[] = "Un service sélectionné est invalide.";
            continue;
        }

        $validId = filter_var(
            $serviceId,
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 1]]
        );

        if ($validId === false) {
            $errors[] = "Un service sélectionné est invalide.";
        } else {
            $selectedServices[] = $validId;
        }
    }
}

// Éviter une association en double.
$selectedServices = array_values(array_unique($selectedServices));

if (empty($selectedServices)) {
    $errors[] = "Sélectionnez au moins un service.";
}

// Vérifier les identifiants du patient et du médecin.
$patientId = filter_var(
    $values["patient_id"],
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

$doctorId = filter_var(
    $values["doctor_id"],
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

// Vérifier le statut.
if (!array_key_exists($values["status"], $statusLabels)) {
    $errors[] = "Choisissez un statut valide.";
}

// Combiner puis vérifier la date et l'heure.
$dateTimeText = $values["date"] . " " . $values["time"];

$startsAt = DateTimeImmutable::createFromFormat(
    "!Y-m-d H:i",
    $dateTimeText
);

if (
    $startsAt === false
    || $startsAt->format("Y-m-d H:i") !== $dateTimeText
) {
    $errors[] = "Choisissez une date et une heure valides.";
}

// Compter les caractères, y compris les lettres accentuées.
$reasonLength = preg_match_all("/./us", $values["reason"]);

if ($reasonLength === false || $reasonLength > 500) {
    $errors[] = "Le motif doit contenir au maximum 500 caractères valides.";
}

try {
    // Ces listes servent à vérifier les choix et à réafficher le formulaire.
    $patient = new Patient();
    $patients = $patient->selectAll();

    $doctor = new Doctor();
    $doctors = $doctor->selectAll();

    $service = new Service();
    $services = $service->selectAll();

    if (
        $patientId === false
        || !in_array($patientId, array_column($patients, "id"))
    ) {
        $errors[] = "Choisissez un patient existant.";
    }

    if (
        $doctorId === false
        || !in_array($doctorId, array_column($doctors, "id"))
    ) {
        $errors[] = "Choisissez un médecin existant.";
    }

    $availableServiceIds = array_column($services, "id");

    foreach ($selectedServices as $serviceId) {
        if (!in_array($serviceId, $availableServiceIds)) {
            $errors[] = "Un service sélectionné n'existe pas.";
        }
    }

    if (empty($errors)) {
        $data = [
            "patient_id" => $patientId,
            "doctor_id" => $doctorId,
            "starts_at" => $startsAt->format("Y-m-d H:i:s"),
            "status" => $values["status"],
            "reason" => $values["reason"],
        ];

        $appointment = new Appointment();
        $appointment->createWithServices($data, $selectedServices);

        header("Location: index.php?success=created", true, 303);
        exit;
    }
} catch (PDOException $exception) {
    http_response_code(500);
    exit("Impossible d'enregistrer le rendez-vous pour le moment.");
}

// Les données sont invalides : réafficher le formulaire.
http_response_code(422);

$heading = "Planifier un rendez-vous";
$pageTitle = $heading . " — MédiCabinet";
$activePage = "appointments";
$formAction = "appointment-store.php";
$submitLabel = "Enregistrer le rendez-vous";
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
                    <a class="appointment-editor__back" href="index.php">
                        Retour aux rendez-vous
                    </a>

                    <h1 class="appointment-editor__title">
                        <?= htmlspecialchars($heading) ?>
                    </h1>
                </header>

                <?php require __DIR__ . "/../includes/appointment-form.php"; ?>
            </main>
        </div>
    </div>
</body>

</html>
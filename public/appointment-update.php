<?php

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../Classe/Appointment.php";
require_once __DIR__ . "/../Classe/Patient.php";
require_once __DIR__ . "/../Classe/Doctor.php";
require_once __DIR__ . "/../Classe/Service.php";

// Accepter uniquement une soumission POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
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

// Vérifier l'identifiant du rendez-vous.
$id = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if ($id === false || $id === null) {
    http_response_code(400);
    exit("Identifiant de rendez-vous invalide.");
}

$statusLabels = [
    "planned" => "Planifié",
    "confirmed" => "Confirmé",
    "completed" => "Terminé",
    "cancelled" => "Annulé",
    "absent" => "Absent",
];

// Conserver les valeurs pour réafficher le formulaire si nécessaire.
$values = [
    "id" => $id,
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

// Vérifier les services reçus.
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

// Vérifier la date et l'heure.
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

// Vérifier la longueur du motif.
$reasonLength = preg_match_all("/./us", $values["reason"]);

if ($reasonLength === false || $reasonLength > 500) {
    $errors[] = "Le motif doit contenir au maximum 500 caractères valides.";
}

try {
    $appointment = new Appointment();
    $record = $appointment->findById($id);

    if ($record === false) {
        http_response_code(404);
        exit("Rendez-vous introuvable.");
    }

    // Charger les choix disponibles.
    $patient = new Patient();
    $patients = $patient->selectAll();

    $doctor = new Doctor();
    $doctors = $doctor->selectAll();

    $service = new Service();
    $services = $service->selectAll();

    // Vérifier que les identifiants existent dans la base.
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

    // Enregistrer uniquement si toutes les validations réussissent.
    if (empty($errors)) {
        $data = [
            "patient_id" => $patientId,
            "doctor_id" => $doctorId,
            "starts_at" => $startsAt->format("Y-m-d H:i:s"),
            "status" => $values["status"],
            "reason" => $values["reason"],
        ];

        $updated = $appointment->updateWithServices(
            $id,
            $data,
            $selectedServices
        );

        if (!$updated) {
            http_response_code(404);
            exit("Ce rendez-vous n'existe plus.");
        }

        header("Location: index.php?success=updated", true, 303);
        exit;
    }
} catch (PDOException $exception) {
    http_response_code(500);
    exit("Impossible de modifier le rendez-vous pour le moment.");
}

// Réafficher le même formulaire avec les erreurs et les valeurs saisies.
http_response_code(422);

$heading = "Modifier le rendez-vous";
$pageTitle = $heading . " — MédiCabinet";
$activePage = "appointments";
$formAction = "appointment-update.php";
$submitLabel = "Enregistrer les modifications";
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
<?php

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../Classe/Appointment.php";

$method = $_SERVER["REQUEST_METHOD"];

if ($method !== "GET" && $method !== "POST") {
    http_response_code(405);
    header("Allow: GET, POST");
    exit("Méthode non autorisée.");
}

// L'identifiant vient du lien GET ou du formulaire POST.
$inputType = $method === "POST" ? INPUT_POST : INPUT_GET;

$id = filter_input(
    $inputType,
    "id",
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

if ($id === false || $id === null) {
    http_response_code(400);
    exit("Identifiant de rendez-vous invalide.");
}

// La protection du formulaire est vérifiée avant toute suppression.
if ($method === "POST") {
    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token)
        || !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        http_response_code(403);
        exit("Formulaire expiré ou invalide. Rechargez la page.");
    }
}

try {
    $appointment = new Appointment();
    $record = $appointment->findById($id);

    if ($record === false) {
        http_response_code(404);
        exit("Rendez-vous introuvable.");
    }

    if ($method === "POST") {
        $appointment->deleteById($id);

        header("Location: index.php?success=deleted", true, 303);
        exit;
    }
} catch (PDOException $exception) {
    http_response_code(500);
    exit("Impossible de traiter cette demande pour le moment.");
}

$appointmentDate = new DateTimeImmutable($record["starts_at"]);

$pageTitle = "Supprimer un rendez-vous — MédiCabinet";
$activePage = "appointments";
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
            <main class="appointment-delete">
                <a class="appointment-delete__back" href="index.php">
                    Retour aux rendez-vous
                </a>

                <section class="confirmation" aria-labelledby="confirmation-title">

                    <h1 class="confirmation__title" id="confirmation-title">
                        Supprimer ce rendez-vous ?
                    </h1>

                    <p class="confirmation__description">
                        Vous allez supprimer le rendez-vous
                        numéro <?= (int) $record["id"] ?>,
                        prévu le
                        <strong>
                            <?= htmlspecialchars(
                                $appointmentDate->format("d/m/Y à H:i")
                            ) ?>.
                        </strong>
                    </p>

                    <p class="confirmation__warning">
                        Cette action est définitive.
                        Le dossier du patient sera conservé.
                    </p>

                    <form class="confirmation__form" action="appointment-delete.php" method="post">

                        <input type="hidden" name="id" value="<?= (int) $record["id"] ?>">

                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

                        <div class="confirmation__actions">
                            <a class="button button--secondary" href="index.php">
                                Annuler
                            </a>

                            <button class="button button--danger" type="submit">
                                Supprimer le rendez-vous
                            </button>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </div>
</body>

</html>
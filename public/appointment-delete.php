<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../Classe/Appointment.php";
require_once __DIR__ . "/../includes/helpers.php";
$method = $_SERVER["REQUEST_METHOD"];
if ($method !== "GET" && $method !== "POST") {
    http_response_code(405);
    header("Allow: GET, POST");
    exit("Méthode non autorisée.");
}
$inputType = $method === "POST" ? INPUT_POST : INPUT_GET;
$id = filter_input($inputType, "id", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if ($id === false || $id === null) {
    http_response_code(400);
    exit("Identifiant de rendez-vous invalide.");
}
if ($method === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    if (!is_string($token) || !hash_equals($_SESSION["csrf_token"], $token)) {
        http_response_code(403);
        exit("Formulaire expiré ou invalide. Rechargez la page.");
    }
}
try {
    $appointment = new Appointment();
    $record = $appointment->findWithDetails($id);
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
                <a class="back-link" href="index.php"><span class="icon icon--arrow-left" aria-hidden="true"></span>
                    Retour aux rendez-vous</a>
                <section class="confirmation" aria-labelledby="confirmation-title">
                    <span class="confirmation__symbol" aria-hidden="true"><span class="icon icon--trash"></span></span>
                    <h1 class="confirmation__title" id="confirmation-title">Supprimer ce rendez-vous ?</h1>
                    <p class="confirmation__description">Cette action est définitive.</p>
                    <dl class="confirmation__details">
                        <div>
                            <dt><span class="icon icon--user" aria-hidden="true"></span> Patient</dt>
                            <dd><?= htmlspecialchars($record['patient_first_name'] . ' ' . $record['patient_last_name']) ?>
                            </dd>
                        </div>
                        <div>
                            <dt><span class="icon icon--users" aria-hidden="true"></span> Médecin</dt>
                            <dd><?= htmlspecialchars($record['doctor_first_name'] . ' ' . $record['doctor_last_name']) ?>
                            </dd>
                        </div>
                        <div>
                            <dt><span class="icon icon--calendar" aria-hidden="true"></span> Date et heure</dt>
                            <dd><?= htmlspecialchars(formatDateFr($record['starts_at'], true)) ?></dd>
                        </div>
                        <div>
                            <dt><span class="icon icon--list" aria-hidden="true"></span> Services</dt>
                            <dd><?= htmlspecialchars($record['service_names'] ?? 'Aucun service') ?></dd>
                        </div>
                    </dl>
                    <p class="confirmation__warning"><span class="icon icon--info" aria-hidden="true"></span> Le patient
                        et les services seront conservés.</p>
                    <form class="confirmation__form" action="appointment-delete.php" method="post">
                        <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <div class="confirmation__actions">
                            <a class="button button--secondary" href="index.php">Conserver le rendez-vous</a>
                            <button class="button button--danger" type="submit"><span class="icon icon--trash"
                                    aria-hidden="true"></span> Supprimer définitivement</button>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </div>
</body>

</html>
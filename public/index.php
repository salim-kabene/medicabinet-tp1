<?php

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../Classe/Appointment.php";

try {
    $appointment = new Appointment();
    $appointments = $appointment->selectWithDetails();
} catch (PDOException $exception) {
    http_response_code(500);
    exit("Le service est temporairement indisponible.");
}

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rendez-vous — MédiCabinet</title>

    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header class="site-header">
        <a class="site-header__brand" href="index.php">MédiCabinet</a>
    </header>

    <main class="appointments">
        <header class="appointments__header">
            <h1 class="appointments__title">Rendez-vous</h1>
            <p class="appointments__description">Consultez les rendez-vous du cabinet.</p>
        </header>

        <?php if (empty($appointments)): ?>
        <p class="appointments__empty">Aucun rendez-vous enregistré.</p>
        <?php else: ?>
        <div class="appointments__table-wrapper">
            <table class="data-table">
                <caption class="data-table__caption">Liste des rendez-vous</caption>
                <thead class="data-table__head">
                    <tr class="data-table__row">
                        <th scope="col">Date et heure</th>
                        <th scope="col">Patient</th>
                        <th scope="col">Médecin</th>
                        <th scope="col">Statut</th>
                    </tr>
                </thead>

                <tbody class="data-table__body">
                    <?php foreach ($appointments as $item): ?>
                    <tr class="data-table__row">
                        <td class="data-table__cell">
                            <?= htmlspecialchars($item["starts_at"]) ?>
                        </td>

                        <td class="data-table__cell">
                            <?= htmlspecialchars(
                                        $item["patient_first_name"]
                                            . " "
                                            . $item["patient_last_name"]
                                    ) ?>
                        </td>

                        <td class="data-table__cell">
                            <?= htmlspecialchars(
                                        $item["doctor_first_name"]
                                            . " "
                                            . $item["doctor_last_name"]
                                    ) ?>
                        </td>

                        <td class="data-table__cell">
                            <?= htmlspecialchars(
                                        $statusLabels[$item["status"]]
                                            ?? $item["status"]
                                    ) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>

    <footer class="site-footer">
        <p class="site-footer__text">MédiCabinet — Gestion des rendez-vous</p>
    </footer>
</body>

</html>
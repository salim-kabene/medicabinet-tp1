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

$pageTitle = "Rendez-vous — MédiCabinet";
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
            <main class="appointments">
                <header class="appointments__header">
                    <h1 class="appointments__title">Rendez-vous</h1>

                    <p class="appointments__description">
                        Organisez les consultations du cabinet.
                    </p>
                </header>

                <?php if (empty($appointments)): ?>
                <p class="appointments__empty">
                    Aucun rendez-vous enregistré.
                </p>
                <?php else: ?>
                <div class="appointments__table-wrapper">
                    <table class="data-table">
                        <caption class="data-table__caption">
                            Liste des rendez-vous
                        </caption>

                        <thead class="data-table__head">
                            <tr class="data-table__row">
                                <th scope="col">Date et heure</th>
                                <th scope="col">Patient</th>
                                <th scope="col">Médecin</th>
                                <th scope="col">Services</th>
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
                                                $item["service_names"] ?? "Aucun service"
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
        </div>
    </div>
</body>

</html>
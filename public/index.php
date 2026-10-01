<?php

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../Classe/Appointment.php";
require_once __DIR__ . "/../Classe/Patient.php";
require_once __DIR__ . "/../includes/helpers.php";

$statusLabels = [
    "planned" => "Planifié",
    "confirmed" => "Confirmé",
    "completed" => "Terminé",
    "cancelled" => "Annulé",
    "absent" => "Absent",
];
$date = $_GET["date"] ?? "";
$status = $_GET["status"] ?? "";
if (!is_string($date) || !is_string($status)) {
    http_response_code(400);
    exit("Filtres invalides.");
}
if ($status !== "" && !array_key_exists($status, $statusLabels)) {
    http_response_code(400);
    exit("Statut invalide.");
}
if ($date !== "") {
    $parsedDate = DateTimeImmutable::createFromFormat("!Y-m-d", $date);
    if ($parsedDate === false || $parsedDate->format("Y-m-d") !== $date) {
        http_response_code(400);
        exit("Date invalide.");
    }
}
try {
    $appointment = new Appointment();
    $appointments = $appointment->selectWithDetails($date, $status);
    $todayCount = $appointment->countToday();
    $upcomingCount = $appointment->countUpcoming();
    $patient = new Patient();
    $patientCount = $patient->countAll();
} catch (PDOException $exception) {
    http_response_code(500);
    exit("Le service est temporairement indisponible.");
}
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
                    <div class="appointments__heading">
                        <h1 class="appointments__title">Rendez-vous</h1>
                        <p class="appointments__description">Organisez les consultations du cabinet.</p>
                    </div>
                    <a class="button button--primary" href="appointment-create.php">
                        <span class="icon icon--plus" aria-hidden="true"></span> Planifier un rendez-vous
                    </a>
                </header>

                <?php if (($_GET['success'] ?? '') === 'created'): ?>
                <p class="alert alert--success" role="status">Le rendez-vous a été enregistré.</p>
                <?php elseif (($_GET['success'] ?? '') === 'updated'): ?>
                <p class="alert alert--success" role="status">Les modifications ont été enregistrées.</p>
                <?php elseif (($_GET['success'] ?? '') === 'deleted'): ?>
                <p class="alert alert--success" role="status">Le rendez-vous a été supprimé.</p>
                <?php endif; ?>
                <section class="stats" aria-label="Statistiques du cabinet">
                    <div class="stats__card">
                        <span class="stats__icon" aria-hidden="true"><span class="icon icon--calendar"></span></span>
                        <h2 class="stats__label">Aujourd’hui</h2>
                        <p class="stats__value"><?= $todayCount ?></p>
                    </div>
                    <div class="stats__card">
                        <span class="stats__icon" aria-hidden="true"><span class="icon icon--clock"></span></span>
                        <h2 class="stats__label">À venir</h2>
                        <p class="stats__value"><?= $upcomingCount ?></p>
                    </div>
                    <div class="stats__card">
                        <span class="stats__icon" aria-hidden="true"><span class="icon icon--users"></span></span>
                        <h2 class="stats__label">Patients</h2>
                        <p class="stats__value"><?= $patientCount ?></p>
                    </div>
                </section>

                <form class="filters" action="index.php" method="get">
                    <div class="filters__field">
                        <label class="filters__label" for="filter-date">Date</label>
                        <input class="filters__input" type="date" id="filter-date" name="date"
                            value="<?= htmlspecialchars($date) ?>">
                    </div>
                    <div class="filters__field">
                        <label class="filters__label" for="filter-status">Statut</label>
                        <select class="filters__select" id="filter-status" name="status">
                            <option value="">Tous les statuts</option>
                            <?php foreach ($statusLabels as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= $status === $value ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filters__actions">
                        <button class="button button--primary" type="submit"><span class="icon icon--filter"
                                aria-hidden="true"></span> Filtrer</button>
                        <a class="button button--secondary" href="index.php">Réinitialiser</a>
                    </div>
                </form>

                <?php if (empty($appointments)): ?>
                <p class="appointments__empty">Aucun rendez-vous ne correspond à votre sélection.</p>
                <?php else: ?>
                <div class="appointments__table-wrapper" role="region" aria-label="Liste des rendez-vous" tabindex="0">
                    <table class="data-table">
                        <caption class="data-table__caption">Liste des rendez-vous</caption>
                        <thead class="data-table__head">
                            <tr class="data-table__row">
                                <th scope="col">Date et heure</th>
                                <th scope="col">Patient</th>
                                <th scope="col">Médecin</th>
                                <th scope="col">Services</th>
                                <th scope="col">Statut</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="data-table__body">
                            <?php foreach ($appointments as $item): ?>
                            <tr class="data-table__row">
                                <td class="data-table__cell">
                                    <?= htmlspecialchars(formatDateFr($item['starts_at'], true)) ?></td>
                                <td class="data-table__cell">
                                    <?= htmlspecialchars($item['patient_first_name'] . ' ' . $item['patient_last_name']) ?>
                                </td>
                                <td class="data-table__cell">
                                    <?= htmlspecialchars($item['doctor_first_name'] . ' ' . $item['doctor_last_name']) ?>
                                </td>
                                <td class="data-table__cell">
                                    <?= htmlspecialchars($item['service_names'] ?? 'Aucun service') ?></td>
                                <td class="data-table__cell">
                                    <span
                                        class="status-badge status-badge--<?= htmlspecialchars($item['status']) ?>"><?= htmlspecialchars($statusLabels[$item['status']] ?? $item['status']) ?></span>
                                </td>
                                <td class="data-table__cell">
                                    <div class="data-table__actions">
                                        <a class="data-table__action"
                                            href="appointment-edit.php?id=<?= (int) $item['id'] ?>"
                                            aria-label="Modifier le rendez-vous numéro <?= (int) $item['id'] ?>">Modifier</a>
                                        <a class="data-table__action data-table__action--danger"
                                            href="appointment-delete.php?id=<?= (int) $item['id'] ?>"
                                            aria-label="Supprimer le rendez-vous numéro <?= (int) $item['id'] ?>">Supprimer</a>
                                    </div>
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
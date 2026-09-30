<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../Classe/Patient.php";
require_once __DIR__ . "/../Classe/Appointment.php";
require_once __DIR__ . "/../includes/helpers.php";
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if ($id === false || $id === null) {
    http_response_code(400);
    exit("Identifiant de patient invalide.");
}
try {
    $patient = new Patient();
    $record = $patient->findById($id);
    if ($record === false) {
        http_response_code(404);
        exit("Patient introuvable.");
    }
    $appointment = new Appointment();
    $appointments = $appointment->selectByPatient($id);
} catch (PDOException $exception) {
    http_response_code(500);
    exit("Le service est temporairement indisponible.");
}
$fullName = $record["first_name"] . " " . $record["last_name"];
$pageTitle = $fullName . " — MédiCabinet";
$activePage = "patients";
$statusLabels = appointmentStatusLabels();
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
            <main class="patient-profile">
                <a class="back-link" href="patients.php"><span class="icon icon--arrow-left" aria-hidden="true"></span> Retour aux patients</a>
                <header class="patient-profile__header">
                    <div class="patient-profile__identity">
                        <span class="avatar avatar--large avatar--<?= (int) $record['id'] % 6 ?>" aria-hidden="true"><?= htmlspecialchars(patientInitials($record['first_name'], $record['last_name'])) ?></span>
                        <div>
                            <h1><?= htmlspecialchars($fullName) ?></h1>
                            <ul class="patient-profile__contacts">
                                <li><span class="icon icon--calendar" aria-hidden="true"></span><span><span class="sr-only">Date de naissance : </span><?= htmlspecialchars(formatDateFr($record['birth_date'])) ?></span></li>
                                <li><span class="icon icon--phone" aria-hidden="true"></span><a class="contact-link" href="tel:<?= htmlspecialchars($record['phone']) ?>"><?= htmlspecialchars($record['phone']) ?></a></li>
                                <li><span class="icon icon--mail" aria-hidden="true"></span>
                                    <?php if (!empty($record['email'])): ?>
                                        <a class="contact-link" href="mailto:<?= htmlspecialchars($record['email']) ?>"><?= htmlspecialchars($record['email']) ?></a>
                                    <?php else: ?>Courriel non renseigné<?php endif; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <a class="button button--primary" href="appointment-create.php?patient_id=<?= (int) $record['id'] ?>"><span class="icon icon--plus" aria-hidden="true"></span> Planifier un rendez-vous</a>
                </header>
                <section class="patient-profile__history" aria-labelledby="history-title">
                    <h2 id="history-title">Historique des rendez-vous</h2>
                    <?php if (empty($appointments)): ?>
                        <p class="empty-state">Aucun rendez-vous enregistré pour ce patient.</p>
                    <?php else: ?>
                        <div class="table-wrapper" role="region" aria-label="Historique du patient" tabindex="0">
                            <table class="data-table">
                                <caption class="sr-only">Rendez-vous de <?= htmlspecialchars($fullName) ?></caption>
                                <thead><tr><th scope="col">Date et heure</th><th scope="col">Médecin</th><th scope="col">Services</th><th scope="col">Statut</th><th scope="col">Actions</th></tr></thead>
                                <tbody>
                                    <?php foreach ($appointments as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars(formatDateFr($item['starts_at'], true)) ?></td>
                                            <td><?= htmlspecialchars($item['doctor_first_name'] . ' ' . $item['doctor_last_name']) ?></td>
                                            <td><?= htmlspecialchars($item['service_names'] ?? 'Aucun service') ?></td>
                                            <td><span class="status-badge status-badge--<?= htmlspecialchars($item['status']) ?>"><?= htmlspecialchars($statusLabels[$item['status']] ?? $item['status']) ?></span></td>
                                            <td><div class="data-table__actions">
                                                <a class="data-table__action" href="appointment-edit.php?id=<?= (int) $item['id'] ?>" aria-label="Modifier le rendez-vous numéro <?= (int) $item['id'] ?>">Modifier</a>
                                                <a class="data-table__action data-table__action--danger" href="appointment-delete.php?id=<?= (int) $item['id'] ?>" aria-label="Supprimer le rendez-vous numéro <?= (int) $item['id'] ?>">Supprimer</a>
                                            </div></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            </main>
        </div>
    </div>
</body>
</html>

<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../Classe/Patient.php";
require_once __DIR__ . "/../includes/helpers.php";
try {
    $patient = new Patient();
    $patients = $patient->selectAll();
} catch (PDOException $exception) {
    http_response_code(500);
    exit("Le service est temporairement indisponible.");
}
$patientCount = count($patients);
$pageTitle = "Patients — MédiCabinet";
$activePage = "patients";
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
            <main class="patients">
                <header class="patients__header">
                    <div>
                        <h1>Patients</h1>
                        <p class="patients__description">Retrouvez les coordonnées et les rendez-vous de vos patients.
                        </p>
                    </div>
                    <span class="count-badge"><?= $patientCount ?>
                        <?= $patientCount > 1 ? 'patients' : 'patient' ?></span>
                </header>
                <?php if (empty($patients)): ?>
                <p class="empty-state">Aucun patient enregistré.</p>
                <?php else: ?>
                <div class="table-wrapper" role="region" aria-label="Liste des patients" tabindex="0">
                    <table class="data-table">
                        <caption class="sr-only">Coordonnées des patients</caption>
                        <thead>
                            <tr>
                                <th scope="col">Patient</th>
                                <th scope="col">Date de naissance</th>
                                <th scope="col">Téléphone</th>
                                <th scope="col">Courriel</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $item): ?>
                            <tr>
                                <th scope="row">
                                    <span class="patient-identity">
                                        <span class="avatar avatar--<?= (int) $item['id'] % 6 ?>"
                                            aria-hidden="true"><?= htmlspecialchars(patientInitials($item['first_name'], $item['last_name'])) ?></span>
                                        <span><?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?></span>
                                    </span>
                                </th>
                                <td><?= htmlspecialchars(formatDateFr($item['birth_date'])) ?></td>
                                <td><a class="contact-link"
                                        href="tel:<?= htmlspecialchars($item['phone']) ?>"><?= htmlspecialchars($item['phone']) ?></a>
                                </td>
                                <td>
                                    <?php if (!empty($item['email'])): ?>
                                    <a class="contact-link"
                                        href="mailto:<?= htmlspecialchars($item['email']) ?>"><?= htmlspecialchars($item['email']) ?></a>
                                    <?php else: ?>Non renseigné<?php endif; ?>
                                </td>
                                <td><a class="data-table__action" href="patient-show.php?id=<?= (int) $item['id'] ?>"
                                        aria-label="Voir la fiche de <?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?>">Voir
                                        la fiche</a></td>
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
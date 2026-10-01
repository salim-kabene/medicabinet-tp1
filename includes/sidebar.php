<aside class="sidebar">
    <a class="sidebar__brand" href="index.php">
        <img class="sidebar__logo" src="assets/icons/logo.svg" width="36" height="36" alt="">
        <span>MédiCabinet</span>
    </a>
    <nav class="sidebar__nav" aria-label="Navigation principale">
        <ul class="sidebar__list">
            <li class="sidebar__item">
                <a class="sidebar__link <?= $activePage === 'appointments' ? 'sidebar__link--active' : '' ?>"
                    href="index.php" <?= $activePage === 'appointments' ? 'aria-current="page"' : '' ?>>
                    <span class="icon icon--calendar" aria-hidden="true"></span> Rendez-vous
                </a>
            </li>
            <li class="sidebar__item">
                <a class="sidebar__link <?= $activePage === 'patients' ? 'sidebar__link--active' : '' ?>"
                    href="patients.php" <?= $activePage === 'patients' ? 'aria-current="page"' : '' ?>>
                    <span class="icon icon--users" aria-hidden="true"></span> Patients
                </a>
            </li>
        </ul>
    </nav>
</aside>
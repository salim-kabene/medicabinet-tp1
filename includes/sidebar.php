<aside class="sidebar">
    <a class="sidebar__brand" href="index.php">
        MédiCabinet
    </a>

    <nav class="sidebar__nav" aria-label="Navigation principale">
        <ul class="sidebar__list">
            <li class="sidebar__item">
                <a class="sidebar__link <?= $activePage === 'appointments' ? 'sidebar__link--active' : '' ?>"
                    href="index.php" <?= $activePage === 'appointments' ? 'aria-current="page"' : '' ?>>
                    Rendez-vous
                </a>
            </li>

            <li class="sidebar__item">
                <a class="sidebar__link <?= $activePage === 'patients' ? 'sidebar__link--active' : '' ?>"
                    href="patients.php" <?= $activePage === 'patients' ? 'aria-current="page"' : '' ?>>
                    Patients
                </a>
            </li>
        </ul>
    </nav>
</aside>
<form class="appointment-form" action="<?= htmlspecialchars($formAction) ?>" method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <?php if ($values['id'] !== ''): ?>
        <input type="hidden" name="id" value="<?= (int) $values['id'] ?>">
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="appointment-form__errors" role="alert">
            <p>Veuillez corriger les erreurs suivantes :</p>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="appointment-form__layout">
        <div class="appointment-form__fields">
            <p class="appointment-form__notice">Les champs marqués d’un * sont obligatoires.</p>
            <div class="appointment-form__row appointment-form__row--people">
                <div class="form-field">
                    <label class="form-field__label" for="patient-id">Patient <span aria-hidden="true">*</span></label>
                    <select class="form-field__control" id="patient-id" name="patient_id" required>
                        <option value="">Choisir un patient</option>
                        <?php foreach ($patients as $item): ?>
                            <option value="<?= (int) $item['id'] ?>" <?= (string) $values['patient_id'] === (string) $item['id'] ? 'selected' : '' ?>><?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label class="form-field__label" for="doctor-id">Médecin <span aria-hidden="true">*</span></label>
                    <select class="form-field__control" id="doctor-id" name="doctor_id" required>
                        <option value="">Choisir un médecin</option>
                        <?php foreach ($doctors as $item): ?>
                            <option value="<?= (int) $item['id'] ?>" <?= (string) $values['doctor_id'] === (string) $item['id'] ? 'selected' : '' ?>><?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="appointment-form__row appointment-form__row--schedule">
                <div class="form-field">
                    <label class="form-field__label" for="appointment-date">Date <span aria-hidden="true">*</span></label>
                    <input class="form-field__control" type="date" id="appointment-date" name="date" value="<?= htmlspecialchars($values['date']) ?>" required>
                </div>
                <div class="form-field">
                    <label class="form-field__label" for="appointment-time">Heure <span aria-hidden="true">*</span></label>
                    <input class="form-field__control" type="time" id="appointment-time" name="time" value="<?= htmlspecialchars($values['time']) ?>" required>
                </div>
                <div class="form-field">
                    <label class="form-field__label" for="status">Statut <span aria-hidden="true">*</span></label>
                    <select class="form-field__control" id="status" name="status" required>
                        <?php foreach ($statusLabels as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= $values['status'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-field">
                <label class="form-field__label" for="reason">Motif</label>
                <textarea class="form-field__control" id="reason" name="reason" rows="4" maxlength="500"><?= htmlspecialchars($values['reason']) ?></textarea>
            </div>

            <fieldset class="appointment-form__services" aria-describedby="services-help">
                <legend class="appointment-form__legend">Services <span aria-hidden="true">*</span></legend>
                <p class="appointment-form__help" id="services-help">Sélectionnez au moins un service.</p>
                <?php foreach ($services as $item): ?>
                    <label class="service-option">
                        <input class="service-option__input" type="checkbox" name="services[]"
                            value="<?= (int) $item['id'] ?>"
                            <?= in_array($item['id'], $selectedServices) ? 'checked' : '' ?>>
                        <span class="service-option__label"><?= htmlspecialchars($item['name']) ?> (<?= (int) $item['duration_minutes'] ?> min)</span>
                    </label>
                <?php endforeach; ?>
            </fieldset>
        </div>

        <div class="appointment-form__side">
            <div class="appointment-form__actions">
                <a class="button button--secondary" href="index.php">Annuler</a>
                <button class="button button--primary" type="submit"><?= htmlspecialchars($submitLabel) ?></button>
            </div>
        </div>
    </div>
</form>

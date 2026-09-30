-- MediCabinet - TP1 - MySQL 8.0.16 ou plus recent
-- ATTENTION : reimporter ce script efface les 5 tables du projet
-- et les remplace par leur structure et leurs donnees fictives initiales.
-- Sur un hebergeur qui impose le nom de la base : adapter le nom
-- ci-dessous ou selectionner la base fournie et retirer CREATE DATABASE / USE.
CREATE DATABASE IF NOT EXISTS medicabinet
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE medicabinet;
SET NAMES utf8mb4;

-- Suppression des tables enfants avant les tables parentes.
DROP TABLE IF EXISTS appointment_service;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS doctors;
DROP TABLE IF EXISTS patients;

CREATE TABLE patients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    birth_date DATE NOT NULL,
    phone VARCHAR(25) NOT NULL,
    email VARCHAR(254) NULL
);

CREATE TABLE doctors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    specialty VARCHAR(100) NOT NULL
);

CREATE TABLE services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    duration_minutes SMALLINT UNSIGNED NOT NULL,
    CONSTRAINT uq_services_name UNIQUE (name),
    CONSTRAINT chk_services_duration CHECK (duration_minutes > 0)
);

CREATE TABLE appointments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,
    doctor_id INT UNSIGNED NOT NULL,
    starts_at DATETIME NOT NULL,
    status ENUM('planned', 'confirmed', 'completed', 'cancelled', 'absent')
        NOT NULL DEFAULT 'planned',
    reason VARCHAR(500) NULL,
    INDEX idx_appointments_starts_at (starts_at),
    INDEX idx_appointments_patient (patient_id),
    INDEX idx_appointments_doctor (doctor_id),
    CONSTRAINT fk_appointments_patient FOREIGN KEY (patient_id)
        REFERENCES patients(id) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_appointments_doctor FOREIGN KEY (doctor_id)
        REFERENCES doctors(id) ON DELETE RESTRICT ON UPDATE RESTRICT
);

CREATE TABLE appointment_service (
    appointment_id INT UNSIGNED NOT NULL,
    service_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (appointment_id, service_id),
    INDEX idx_appointment_service_service (service_id),
    CONSTRAINT fk_appointment_service_appointment FOREIGN KEY (appointment_id)
        REFERENCES appointments(id) ON DELETE CASCADE ON UPDATE RESTRICT,
    CONSTRAINT fk_appointment_service_service FOREIGN KEY (service_id)
        REFERENCES services(id) ON DELETE RESTRICT ON UPDATE RESTRICT
);

-- Donnees fictives : aucune information medicale reelle.
-- START TRANSACTION;
INSERT INTO patients (id, first_name, last_name, birth_date, phone, email) VALUES
(1, 'Sophie', 'Tremblay', '1992-03-14', '514-555-0123', 'sophie@example.com'),
(2, 'Karim', 'Benali', '1985-07-22', '514-555-0145', 'karim@example.com'),
(3, 'Emma', 'Roy', '1990-01-03', '514-555-0167', 'emma@example.com'),
(4, 'Thomas', 'Gagnon', '1978-11-17', '514-555-0134', 'thomas@example.com'),
(5, 'Leïla', 'Amari', '1995-05-09', '514-555-0178', 'leila@example.com'),
(6, 'Daniel', 'Pelletier', '1988-02-28', '514-555-0190', 'daniel@example.com');

INSERT INTO doctors (id, first_name, last_name, specialty) VALUES
(1, 'Nadia', 'Roy', 'Médecine familiale'),
(2, 'Marc', 'Gagnon', 'Médecine familiale');

INSERT INTO services (id, name, duration_minutes) VALUES
(1, 'Consultation générale', 30),
(2, 'Bilan préventif', 15),
(3, 'Suivi médical', 20);

-- Dates et heures locales du cabinet : America/Toronto.
INSERT INTO appointments (id, patient_id, doctor_id, starts_at, status, reason) VALUES
(1, 1, 1, '2026-09-30 09:00:00', 'confirmed', 'Consultation et bilan de suivi.'),
(2, 2, 2, '2026-09-30 11:30:00', 'planned', 'Rendez-vous de suivi.'),
(3, 3, 1, '2026-09-29 10:30:00', 'completed', 'Consultation générale.'),
(4, 4, 2, '2026-09-28 14:00:00', 'absent', 'Bilan préventif.'),
(5, 5, 1, '2026-09-27 09:30:00', 'cancelled', 'Suivi médical.'),
(6, 1, 2, '2026-09-15 14:00:00', 'completed', 'Suivi médical.'),
(7, 1, 1, '2026-08-20 09:30:00', 'cancelled', 'Consultation générale.');

INSERT INTO appointment_service (appointment_id, service_id) VALUES
(1, 1), (1, 2), (2, 3), (3, 1), (4, 2), (5, 3), (6, 3), (7, 1);
COMMIT;

-- Le PHP devra enregistrer le rendez-vous ET ses services dans une transaction.
-- Il devra refuser un rendez-vous sans service et valider tous les champs.
-- Ce schema ne bloque pas les chevauchements horaires : fonctionnalite hors V1.

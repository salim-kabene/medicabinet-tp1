# MédiCabinet

TP1 — Programmation Web avancée.

Application de gestion des rendez-vous d’un cabinet médical,
développée en PHP orienté objet avec une base MySQL.

## Organisation

- Classe : classes et opérations sur les données.
- config : configuration de l’application et de MySQL.
- includes : parties HTML communes.
- public : pages accessibles et ressources CSS, images et icônes.
- database : script SQL de création et données fictives.
- docs : diagramme ERD et documentation.

## Configuration locale

Copier config/database.example.php vers config/database.php,
puis adapter les identifiants MySQL.

Importer database/medicabinet.sql.
Attention : ce script réinitialise les tables du projet.

## Serveur local

Depuis la racine du projet :

php -S localhost:8000 -t public

## État actuel

Structure initialisée. Les fonctionnalités PHP restent à développer.
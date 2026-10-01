<?php

// Fonctions d'affichage uniquement 
function formatDateFr($value, $withTime = false)
{
    $date = new DateTimeImmutable($value);
    $months = [
        1 => "janvier",
        "février",
        "mars",
        "avril",
        "mai",
        "juin",
        "juillet",
        "août",
        "septembre",
        "octobre",
        "novembre",
        "décembre"
    ];
    $label = $date->format("j") . " " . $months[(int) $date->format("n")] . " " . $date->format("Y");
    if ($withTime) {
        $label .= " à " . $date->format("H:i");
    }
    return $label;
}

function patientInitials($firstName, $lastName)
{
    // Extraire un caractère UTF-8, sans dépendre de l'extension mbstring.
    preg_match("/^./u", trim($firstName), $first);
    preg_match("/^./u", trim($lastName), $last);
    return strtoupper(($first[0] ?? "") . ($last[0] ?? ""));
}

function appointmentStatusLabels()
{
    return [
        "planned" => "Planifié",
        "confirmed" => "Confirmé",
        "completed" => "Terminé",
        "cancelled" => "Annulé",
        "absent" => "Absent"
    ];
}
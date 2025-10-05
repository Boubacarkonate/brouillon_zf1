<?php
// exportMarcheTravail.php

// Définir les en-têtes pour téléchargement CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="marche_travail.csv"');

// Créer un flux de sortie
$output = fopen('php://output', 'w');

// Ajouter l'entête du CSV
fputcsv($output, ['Indicateur', 'Valeur', 'Interprétation']);

// Exemple : récupérer les données depuis la session, base ou API
// Ici je reprends ton $this->dynamique, $this->tension, $this->dernierDemandeurs, etc.
$data = [
    'Dynamique' => $this->dynamique ?? null,
    'Tension' => $this->tension ?? null,
    'Demandeurs' => $this->dernierDemandeurs ?? null,
    'Embauches' => $this->dernieresEmbauches ?? null
];

// Ajouter les lignes CSV
foreach ($data as $key => $value) {
    if ($value) {
        $valeur = $value['valeur'] ?? ($value['nombre'] ?? '-');
        $interpretation = $value['interpretation'] ?? '-';
        fputcsv($output, [$key, $valeur, $interpretation]);
    }
}

fclose($output);
exit;

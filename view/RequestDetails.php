<?php
$title = "Détails de la demande";
$mainStaticTitle = "Demande";
$editLinkText = "Modifier ma demande";
$confirmText = "envoyer une proposition de trajet";
$promptButtonText = "Je propose des places";
$detailsCategory = "request";

$specificLines = [
    'Sièges nécessaires' => $request['neededSeats'],
    'Fumeur' => ($request['smoker'] ? 'Oui' : 'Non')
];

require_once('template/DetailsTemplate.php'); ?>

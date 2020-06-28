<?php
$title = "Détails de trajet";
$mainStaticTitle = "Trajet";
$editLinkText = "Modifier mon trajet";
$confirmText = "envoyer une demande de contact";
$promptButtonText = "Je suis intéressé";
$detailsCategory = "proposal";

$specificLines['Sièges disponibles'.($proposal['isReturn'] ? ' (aller)' : '')] = $proposal['availableSeats'].' / '.$proposal['maxSeats'];

if($proposal['isReturn']) {
    $specificLines['Sièges disponibles (retour)'] = $proposal['returnAvailableSeats'].' / '.$proposal['returnMaxSeats'];
}

$specificLines = array_merge($specificLines, [
    'Détour maximal' => $proposal['detourRadius'].' km',
    'Voiture fumeurs' => ($proposal['smokingAllowed'] ? 'Oui' : 'Non'),
    'Coût du trajet' => ($proposal['free'] ? '<abbr title="Aucune contrepartie monétaire n\'est demandée pour monter à bord, mais le conducteur et vous pouvez tout de même négocier une participation sous une autre forme (nourriture, hébergement sur place...)">Gratuit</abbr>' : '<abbr title="Le conducteur est susceptible de vous demander une participation financière, qui ne peut pas dépasser les frais d\'essence ou de péage. Vous pouvez également lui proposer une contrepartie sous une autre forme (nourriture, hébergement sur place...), qu\'il n\'est cependant pas tenu d\'accepter.">Payant</abbr>')
]);

require_once('template/DetailsTemplate.php'); ?>

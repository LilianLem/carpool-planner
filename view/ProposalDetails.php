<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Détails de trajet - Carpool Planner</title>
		<link rel="stylesheet" href="static/css/style.css"/>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
		<meta name="robots" content="noindex"/>
	</head>

	<body>
		<header>
			<div id="nav-mobile-open">
				<img src="static/img/icons/mobile-menu.png" alt="Menu mobile" />
			</div>

			<div id="logo">
				<img src="static/img/logo.png" alt="Logo du site Carpool Planner" />
			</div>
		</header>

		<main id="proposal-details">
			<h1>Trajet #<?=$proposal['id']?></h1>

			<table>
				<tr>
					<th>Pseudo</th>
					<td><?=$proposal['username']?></td>
				</tr>

                <tr>
                    <th>Ville de départ</th>
                    <td><?=$proposal['start_city']?></td>
                </tr>

                <tr>
                    <th>Date de départ</th>
                    <td><?=$proposal['start_date']?></td>
                </tr>

                <?php if($proposal['return']) {?>
                <tr>
                    <th>Ville de retour</th>
                    <td><?=$proposal['return_city']?></td>
                </tr>
				<?php } ?>

                <tr>
                    <th>Date de retour</th>
                    <td><?= $proposal['return'] ? $proposal['return_date'] : 'Pas de retour';?></td>
                </tr>

                <tr>
                    <th>Sièges disponibles<?php if($proposal['return']) {?> à l'aller<?php }?></th>
                    <td><?=$proposal['available_seats']?> / <?=$proposal['max_seats']?></td>
                </tr>

				<?php if($proposal['return']) {?>
                <tr>
                    <th>Sièges disponibles au retour</th>
                    <td><?=$proposal['return_available_seats']?> / <?=$proposal['return_max_seats']?></td>
                </tr>
				<?php }?>

                <tr>
                    <th>Détour maximal</th>
                    <td><?=$proposal['detour_radius']?> km</td>
                </tr>

                <tr>
                    <th>Voiture fumeurs</th>
                    <td><?=$proposal['smoking_allowed'] ? 'Oui' : 'Non'?></td>
                </tr>

                <tr>
                    <th>Coût du trajet</th>
                    <td><?=$proposal['free'] ? '<abbr title="Aucune contrepartie monétaire n\'est demandée pour monter à bord, mais le conducteur et vous pouvez tout de même négocier une participation sous une autre forme (nourriture, hébergement sur place...)">Gratuit</abbr>' : '<abbr title="Le conducteur est susceptible de vous demander une participation financière, qui ne peut pas dépasser les frais d\'essence ou de péage. Vous pouvez également lui proposer une contrepartie sous une autre forme (nourriture, hébergement sur place...), qu\'il n\'est cependant pas tenu d\'accepter.">Payant</abbr>'?></td>
                </tr>

                <tr>
                    <th>Dernière modification</th>
                    <td><?=$proposal['last_edited']?></td>
                </tr>
			</table>

			<?php if(isset($_SESSION['userId'])){ if($proposal['user_id'] == $_SESSION['userId']) { ?><a id="edit-button" href="index.php?action=editProposal"><p>M</p></a><?php }} ?> <!-- Picto de crayon à mettre ici -->
		</main>

		<footer>
			<p>© 2020 Carpool Planner - <a href="#">Billetterie de l'évènement</a> - <a href="#">Mentions légales</a> - <a href="#">CGU</a></p>
		</footer>

	</body>
</html>

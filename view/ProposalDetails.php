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
                    <td><?=$proposal['startCity']?></td>
                </tr>

                <tr>
                    <th>Date de départ</th>
                    <td><?=$proposal['startDate']?></td>
                </tr>

                <?php if($proposal['return']) {?>
                <tr>
                    <th>Ville de retour</th>
                    <td><?=$proposal['returnCity']?></td>
                </tr>
				<?php } ?>

                <tr>
                    <th>Date de retour</th>
                    <td><?= $proposal['return'] ? $proposal['returnDate'] : 'Pas de retour';?></td>
                </tr>

                <tr>
                    <th>Sièges disponibles<?php if($proposal['return']) {?> (aller)<?php }?></th>
                    <td><?=$proposal['availableSeats']?> / <?=$proposal['maxSeats']?></td>
                </tr>

				<?php if($proposal['return']) {?>
                <tr>
                    <th>Sièges disponibles (retour)</th>
                    <td><?=$proposal['returnAvailableSeats']?> / <?=$proposal['returnMaxSeats']?></td>
                </tr>
				<?php }?>

                <tr>
                    <th>Détour maximal</th>
                    <td><?=$proposal['detourRadius']?> km</td>
                </tr>

                <tr>
                    <th>Voiture fumeurs</th>
                    <td><?=$proposal['smokingAllowed'] ? 'Oui' : 'Non'?></td>
                </tr>

                <tr>
                    <th>Coût du trajet</th>
                    <td><?=$proposal['free'] ? '<abbr title="Aucune contrepartie monétaire n\'est demandée pour monter à bord, mais le conducteur et vous pouvez tout de même négocier une participation sous une autre forme (nourriture, hébergement sur place...)">Gratuit</abbr>' : '<abbr title="Le conducteur est susceptible de vous demander une participation financière, qui ne peut pas dépasser les frais d\'essence ou de péage. Vous pouvez également lui proposer une contrepartie sous une autre forme (nourriture, hébergement sur place...), qu\'il n\'est cependant pas tenu d\'accepter.">Payant</abbr>'?></td>
                </tr>

                <tr>
                    <th>Dernière modification</th>
                    <td><?=$proposal['lastEdited']?></td>
                </tr>
			</table>

			<?php if(isset($_SESSION['userId'])){ if($proposal['userId'] == $_SESSION['userId']) { ?><a title="Modifier mon trajet" id="edit-button" class="basic-thumb-button" href="index.php?action=editProposal&id=<?=$proposal['id']?>"><img src="static/img/icons/pencil.png" alt="Crayon" width="35px"></a><?php }} ?>
		</main>

		<footer>
			<p>© 2020 Carpool Planner - <a href="#">Billetterie de l'évènement</a> - <a href="#">Mentions légales</a> - <a href="#">CGU</a></p>
		</footer>

		<?php if(!empty($errors)) {
			echo '<script type="text/javascript">'.'alert("Une ou plusieurs erreurs sont survenues ! Détails :\n'.$errors.'");</script>';
		} ?>

	</body>
</html>

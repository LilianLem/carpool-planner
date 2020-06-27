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
            <div id="mobile-menu">
                <input id="mobile-menu-button" type="checkbox" />
                <div id="mobile-menu-container">
                    <label id="mobile-menu-icon" for="mobile-menu-button">
                        <span id="mobile-menu-image"></span>
                    </label>
                </div>
                <nav>
                    <ul>
                        <?php if(!isset($_SESSION['userId'])) { ?>
                        <li><a href="index.php?action=register&page=<?=urlencode(strip_tags($_GET['page'] ?? basename($_SERVER['REQUEST_URI'])))?>">Inscription</a></li>
                        <li><a href="index.php?action=login&page=<?=urlencode(strip_tags($_GET['page'] ?? basename($_SERVER['REQUEST_URI'])))?>">Connexion</a></li>
                        <?php } else { ?>
                        <li><a href="index.php?action=logout&page=<?=urlencode(basename($_SERVER['REQUEST_URI']))?>">Déconnexion</a></li>
                        <?php } ?>
                        <li><a href="index.php?action=showProposals">Trajets disponibles</a></li>
                        <li><a href="index.php?action=showRequests">Demandes de transport</a></li>
                    </ul>
                </nav>
            </div>

            <a href="index.php" id="logo">
                <img src="static/img/logo.png" alt="Logo du site Carpool Planner" />
            </a>
		</header>

		<main id="proposal-details" class="details">
			<h1>Trajet #<?=$proposal['id']?></h1>

			<table>
				<tr>
					<th>Pseudo</th>
					<td><?=$proposal['username']?></td>
				</tr>

                <tr>
                    <th>Ville de départ</th>
                    <td><?=$proposal['startCity'].' ('.$proposal['startDepartment'].')'?></td>
                </tr>

                <tr>
                    <th>Date de départ</th>
                    <td><?=$proposal['startDate']?></td>
                </tr>

                <?php if($proposal['isReturn']) {?>
                <tr>
                    <th>Ville de retour</th>
                    <td><?=$proposal['returnCity'].' ('.$proposal['returnDepartment'].')'?></td>
                </tr>
				<?php } ?>

                <tr>
                    <th>Date de retour</th>
                    <td><?= $proposal['isReturn'] ? $proposal['returnDate'] : 'Pas de retour';?></td>
                </tr>

                <tr>
                    <th>Sièges disponibles<?php if($proposal['isReturn']) {?> (aller)<?php }?></th>
                    <td><?=$proposal['availableSeats']?> / <?=$proposal['maxSeats']?></td>
                </tr>

				<?php if($proposal['isReturn']) {?>
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
                    <th>Description</th>
                    <td><?=$proposal['description']?></td>
                </tr>

                <tr>
                    <th>Dernière modification</th>
                    <td><?=$proposal['lastEdited']?></td>
                </tr>
			</table>

			<?php if(isset($_SESSION['userId'])){ if($proposal['userId'] == $_SESSION['userId']) { ?>
                <a title="Modifier mon trajet" id="edit-button" class="basic-thumb-button" href="index.php?action=editProposal&id=<?=$proposal['id']?>">
                    <img src="static/img/icons/pencil.png" alt="Crayon" width="35px">
                </a>
            <?php } else {
            if (isset($_POST['showSendingConfirmationPrompt'])) { ?>
                <div class="user-actions confirm">
                    <p>Êtes-vous sûr de vouloir envoyer une demande de contact à <?=$proposal['username']?> ?</p>
                    <div class="user-actions-buttons">
                        <a href="index.php?action=showProposal&id=<?=$proposal['id']?>" class="rounded-square-button transparent-button"><p>Annuler</p></a>
                        <form action="index.php?action=sendMessageProposal&id=<?=$proposal['id']?>" method="post">
                            <input type="hidden" id="proposal-message-sending-token" name="proposalMessageSendingToken" value="<?=$proposal['id']?>" required="required" />
                            <input type="submit" class="rounded-square-button" value="Confirmer" />
                        </form>
                    </div>
                </div>
            <?php } else { ?>
                <form action="index.php?action=showProposal&id=<?=$proposal['id']?>" method="post" class="user-actions">
                    <input type="hidden" id="show-sending-confirmation-prompt" name="showSendingConfirmationPrompt" value="1" required="required" />
                    <div class="user-actions-buttons">
                        <input type="submit" class="rounded-square-button" value="Je suis intéressé" />
                    </div>
                </form>
			<?php }}} else { ?>
                <div class="user-actions">
                    <div class="user-actions-buttons">
                        <a href="index.php?action=register&page=<?=urlencode('index.php?action=showProposal&id='.$proposal['id'])?>"" class="rounded-square-button"><p>Je suis intéressé</p></a>
                    </div>
                </div>
            <?php } ?>
		</main>

		<footer>
			<p>© 2020 Carpool Planner - <a href="#">Billetterie de l'évènement</a> - <a href="#">Mentions légales</a> - <a href="#">CGU</a></p>
		</footer>

		<?php if(!empty($errors)) {
			echo '<script type="text/javascript">'.'alert("Une ou plusieurs erreurs sont survenues ! Détails :\n'.$errors.'");</script>';
		} ?>

	</body>
</html>

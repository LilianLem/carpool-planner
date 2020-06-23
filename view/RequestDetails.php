<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Détails de la demande - Carpool Planner</title>
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
                        <li><a href="index.php?action=register">Inscription</a></li>
                        <li><a href="index.php?action=login">Connexion</a></li>
                        <?php } else { ?>
                        <li><a href="index.php?action=logout&page=<?=str_replace("&","%26",basename($_SERVER['REQUEST_URI']))?>">Déconnexion</a></li>
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

		<main id="request-details" class="details">
			<h1>Demande #<?=$request['id']?></h1>

			<table>
				<tr>
					<th>Pseudo</th>
					<td><?=$request['username']?></td>
				</tr>

                <tr>
                    <th>Ville de départ</th>
                    <td><?=$request['startCity']?></td>
                </tr>

                <tr>
                    <th>Date de départ</th>
                    <td><?=$request['startDate']?></td>
                </tr>

                <?php if($request['isReturn']) {?>
                <tr>
                    <th>Ville de retour</th>
                    <td><?=$request['returnCity']?></td>
                </tr>
				<?php } ?>

                <tr>
                    <th>Date de retour</th>
                    <td><?= $request['isReturn'] ? $request['returnDate'] : 'Pas de retour';?></td>
                </tr>

                <tr>
                    <th>Sièges nécessaires</th>
                    <td><?=$request['neededSeats']?></td>
                </tr>

                <tr>
                    <th>Fumeur</th>
                    <td><?=$request['smoker'] ? 'Oui' : 'Non'?></td>
                </tr>

                <tr>
                    <th>Dernière modification</th>
                    <td><?=$request['lastEdited']?></td>
                </tr>
			</table>

			<?php if(isset($_SESSION['userId'])){ if($request['userId'] == $_SESSION['userId']) { ?>
                <a title="Modifier ma demande" id="edit-button" class="basic-thumb-button" href="index.php?action=editRequest&id=<?=$request['id']?>">
                    <img src="static/img/icons/pencil.png" alt="Crayon" width="35px">
                </a>
			<?php } else { ?>
                <form class="user-actions">
                    <input type="submit" href="index.php?action=sendMessageRequest&id=<?=$request['id']?>" class="rounded-square-button" value="Envoyer un message" />
                </form>
			<?php }} else { ?>
                <div class="user-actions">
                    <div class="user-actions-buttons">
                        <a href="index.php?action=register" class="rounded-square-button extended-button"><p>Envoyer un message</p></a>
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

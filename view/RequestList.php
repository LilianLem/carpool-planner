<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Demandes de transport - Carpool Planner</title>
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
                        <li><a href="index.php?action=logout&page=<?=basename($_SERVER['REQUEST_URI'])?>">Déconnexion</a></li>
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

		<main id="proposal-list" class="list">
			<h1>Demandes de transport</h1>

			<table>
				<tr>
					<th>Lieu</th>
					<th>Date de départ</th>
					<th>Pseudo</th>
				</tr>

				<?php
				foreach($requests as $request)
				{ ?>
				<tr onclick="window.location='index.php?action=showRequest&id=<?=$request['id']?>';">
					<td><?=$request['startCity']?></td>
					<td><?=$request['startDate']?></td>
					<td><?=$request['username']?></td>
				</tr>
				<?php } ?>
			</table>

			<a id="add-button" class="basic-thumb-button" href="index.php?action=<?= isset($_SESSION['userId']) ? 'addRequest' : 'register'; ?>"><p>+</p></a>
		</main>

		<footer>
			<p>© 2020 Carpool Planner - <a href="#">Billetterie de l'évènement</a> - <a href="#">Mentions légales</a> - <a href="#">CGU</a></p>
		</footer>

		<?php if(!empty($errors)) {
			echo '<script type="text/javascript">'.'alert("Une ou plusieurs erreurs sont survenues ! Détails :\n'.$errors.'");</script>';
		} ?>

	</body>
</html>

<!DOCTYPE html>
<html>
	<head>
		<title>Trajets proposés - Carpool Planner</title>
		<link rel="stylesheet" href="static/css/style.css"/>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
		<meta name="robots" content="noindex"/>
	</head>

	<body>
		<header>
			<div id="nav-mobile-open">
				<img src="static/img/icons/mobile-menu.png" />
			</div>

			<div id="logo">
				<img src="static/img/logo.png" />
			</div>
		</header>

		<main>
			<h1>Trajets proposés</h1>

			<table>
				<tr>
					<th>Lieu</th>
					<th>Date de départ</th>
					<th>Pseudo</th>
				</tr>

				<?php
				foreach($proposals as $proposal)
				{ ?>
				<tr>
					<td><?=$proposal['ville']?></td>
					<td><?=$proposal['date_depart'] ?? 'RETOUR UNIQUEMENT'?></td>
					<td><?=$proposal['discord_username']?></td>
				</tr>
				<?php } ?>
			</table>

			<a id="add-button" href="index.php?action=addProposal"><p>+</p></a>
		</main>

		<footer>
			<p>© 2020 Carpool Planner - <a href="#">Billetterie de l'évènement</a> - <a href="#">Mentions légales</a> - <a href="#">CGU</a></p>
		</footer>

	</body>
</html>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Proposer un trajet - Carpool Planner</title>
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

		<main>
			<h1>Ajouter un trajet</h1>

			<form id="carpool-add-form" class="basic-form" method="post" action="index.php?action=newProposal">
				<label for="discord-username">Pseudo Discord</label>
				<input type="text" name="discord-username" id="discord-username" placeholder="Ex : Pierre#1234" required="required" maxlength="32" />

                <label for="email">E-mail</label>
                <input type="email" name="email" id="email" placeholder="Ex : pierre.dupont@gmail.com" required="required" minlength="6" maxlength="128" />

				<label for="city">Ville de départ</label>
				<input type="text" name="city" id="city" placeholder="Ex : Rouen / Clermont-Ferrand" required="required" maxlength="45" />

				<label for="department">Département<!-- ou pays--></label>
				<input type="text" name="department" id="department" placeholder="Ex : 01 / 1 / 34..." required="required" maxlength="2" /> <!-- placeholder="Ex : 01 / 1 / 34 / BE / CH..." -->

				<label for="date">Date de départ</label>
				<input type="date" name="date" id="date" required="required" /> <!--pattern="[0-9]{2}/[0-9]{2}/[0-9]{4}"-->

				<label for="time">Heure de départ</label>
				<input type="time" name="time" id="time" required="required" step="900" />

				<label for="date">Date de retour <span class="i">(facultatif)</span></label>
				<input type="date" name="return-date" id="return-date" /> <!--pattern="[0-9]{2}/[0-9]{2}/[0-9]{4}"-->

				<label for="time">Heure de retour <span class="i">(facultatif)</span></label>
				<input type="time" name="return-time" id="return-time" step="900" />

				<input class="button" type="submit" value="Ajouter" />
			</form>
		</main>

		<footer>
			<p>© 2020 Carpool Planner - <a href="#">Billetterie de l'évènement</a> - <a href="#">Mentions légales</a> - <a href="#">CGU</a></p>
		</footer>

		<?php if(!empty($errors)) {
			echo '<script type="text/javascript">'.'alert("Une ou plusieurs erreurs sont survenues ! Détails :\n'.$errors.'");</script>';
		} ?>
	</body>
</html>

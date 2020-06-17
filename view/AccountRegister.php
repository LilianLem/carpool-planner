<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Inscription - Carpool Planner</title>
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
			<h1>Inscription</h1>

			<form id="register-form" class="basic-form" method="post" action="index.php?action=registration">
                <label for="email">E-mail</label>
                <input type="email" name="email" id="email" placeholder="Ex : pierre.dupont@gmail.com" required="required" minlength="6" maxlength="128" value="<?=$prefilledInfos['email']?>" />

				<label for="discord-username">Pseudo Discord</label>
				<input type="text" name="discordUsername" id="discord-username" placeholder="Ex : Pierre#1234" required="required" maxlength="32" value="<?=$prefilledInfos['discordUsername']?>" />

                <label for="password">Mot de passe</label>
                <input type="password" name="password" id="password" required="required" minlength="8" maxlength="128" pattern="(?=.*[a-zß-öø-ÿ])(?=.*[A-ZÀ-ÖØ-Þ])(?=.*\d)(?=.*[!@#$£€%^&*()\\[\]{}\-_+=~`|:;&quot;'<>,.\/?])[A-Za-zÀ-ÖØ-öø-ÿ\d!@#$£€%^&*()\\[\]{}\-_+=~`|:;&quot;'<>,.\/?]{8,128}" title="Merci d'utiliser un mot de passe de 8 à 128 caractères avec au moins 1 minuscule, 1 majuscule, 1 chiffre et 1 caractère spécial" autocomplete="new-password" />

				<input class="button" type="submit" value="Valider" />
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

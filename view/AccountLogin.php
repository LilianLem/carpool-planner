<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Connexion - Carpool Planner</title>
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
			<h1>Connexion</h1>

			<form id="login-form" class="basic-form" method="post" action="index.php?action=loggingIn">
                <label for="email">E-mail</label>
                <input type="email" name="email" id="email" required="required" maxlength="128" value="<?=$prefilledEmail?>" />

                <label for="password">Mot de passe</label>
                <input type="password" name="password" id="password" required="required" maxlength="128" autocomplete="current-password" />

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
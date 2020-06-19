<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Modifier une demande - Carpool Planner</title>
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
                        <li><a href="index.php?action=register">Inscription</a></li>
                        <li><a href="index.php?action=login">Connexion</a></li>
                        <li><a href="index.php?action=showProposals">Trajets disponibles</a></li>
                        <li><a href="index.php?action=showRequests">Demandes de trajet</a></li>
                    </ul>
                </nav>
            </div>

            <a href="index.php" id="logo">
                <img src="static/img/logo.png" alt="Logo du site Carpool Planner" />
            </a>
        </header>

		<main>
			<h1>Modifier une demande</h1>

			<form id="request-edit-form" class="basic-form" method="post" action="index.php?action=changeRequest">
                <input type="hidden" name="id" id="id" value="<?=$request["id"]?>" required="required" />

				<label for="city">Ville de départ</label>
				<input type="text" name="startCity" id="start-city" value="<?=$request["startCity"]?>" placeholder="Ex : Rouen / Clermont-Ferrand" required="required" maxlength="45" />

				<label for="department">Département</label>
				<input type="text" name="startDepartment" id="start-department" value="<?=$request["startDepartment"]?>" placeholder="Ex : 01 / 1 / 34..." required="required" maxlength="2" />

				<label for="date">Date de départ</label>
				<input type="date" name="startDate" id="start-date" value="<?=$request["startDate"]?>" required="required" />

				<label for="time">Heure de départ</label>
				<input type="time" name="startTime" id="start-time" value="<?=$request["startTime"]?>" required="required" step="900" />

				<label for="return-date">Date de retour <span class="i">(facultatif)</span></label>
				<input type="date" name="returnDate" id="return-date" value="<?=$request["returnDate"]?>" />

				<label for="return-time">Heure de retour <span class="i">(facultatif)</span></label>
				<input type="time" name="returnTime" id="return-time" value="<?=$request["returnTime"]?>" step="900" />

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

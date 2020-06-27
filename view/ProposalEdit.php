<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Modifier une proposition de trajet - Carpool Planner</title>
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

		<main>
			<h1>Modifier un trajet</h1>

			<form id="carpool-edit-form" class="basic-form" method="post" action="index.php?action=changeProposal">
                <input type="hidden" name="id" id="id" value="<?=$proposal["id"]?>" required="required" />

				<label for="start-city">Ville de départ</label>
				<input type="text" name="startCity" id="start-city" value="<?=$proposal["startCity"]?>" placeholder="Ex : Rouen / Clermont-Ferrand" required="required" maxlength="45" />

				<label for="start-department">Département</label>
				<input type="text" name="startDepartment" id="start-department" value="<?=$proposal["startDepartment"]?>" placeholder="Ex : 01 / 1 / 34..." required="required" maxlength="2" />

				<label for="start-date">Date de départ</label>
				<input type="date" name="startDate" id="start-date" value="<?=$proposal["startDate"]?>" required="required" />

				<label for="start-time">Heure de départ</label>
				<input type="time" name="startTime" id="start-time" value="<?=$proposal["startTime"]?>" required="required" step="900" />

                <label for="max-seats">Nombre de sièges proposés à l'aller</label>
                <input type="number" name="maxSeats" id="max-seats" value="<?=$proposal["maxSeats"]?>" placeholder="Min. 1 / Max. 8" required="required" min="1" max="8" step="1" />

                <label for="available-seats">Nombre de sièges libres à l'aller</label>
                <input type="number" name="availableSeats" id="available-seats" value="<?=$proposal["availableSeats"]?>" placeholder="Min. 1 / Max. 8" required="required" min="1" max="8" step="1" />

                <label for="detour-radius">Détour maximum</label>
                <input type="number" name="detourRadius" id="detour-radius" value="<?=$proposal["detourRadius"]?>" placeholder="Max. 50km" required="required" min="0" max="50" step="1" />

                <label for="return-city">Ville de retour <span class="i">(facultatif)</span></label>
                <input type="text" name="returnCity" id="return-city" value="<?=$proposal["returnCity"]?>" placeholder="Ex : Rouen / Clermont-Ferrand" maxlength="45" />

                <label for="return-department">Département <span class="i">(facultatif)</span></label>
                <input type="text" name="returnDepartment" id="return-department" value="<?=$proposal["returnDepartment"]?>" placeholder="Ex : 01 / 1 / 34..." maxlength="2" />
                
                <label for="return-date">Date de retour <span class="i">(facultatif)</span></label>
				<input type="date" name="returnDate" id="return-date" value="<?=$proposal["returnDate"]?>" />

				<label for="return-time">Heure de retour <span class="i">(facultatif)</span></label>
				<input type="time" name="returnTime" id="return-time" value="<?=$proposal["returnTime"]?>" step="900" />

                <label for="return-max-seats">Nombre de sièges proposés au retour <span class="i">(facultatif)</span></label>
                <input type="number" name="returnMaxSeats" id="return-max-seats" value="<?=$proposal["returnMaxSeats"]?>" placeholder="Min. 1 / Max. 8" min="1" max="8" step="1" />

                <label for="return-available-seats">Nombre de sièges libres au retour <span class="i">(facultatif)</span></label>
                <input type="number" name="returnAvailableSeats" id="return-available-seats" value="<?=$proposal["returnAvailableSeats"]?>" placeholder="Min. 1 / Max. 8" min="1" max="8" step="1" />

                <label for="description">Description libre <span class="i">(facultatif)</span></label>
                <textarea name="description" id="description" placeholder="Max. 500 caractères" maxlength="500"><?=$proposal["description"]?></textarea>

                <label for="smoking-allowed">Fumeurs admis</label>
                <input type="checkbox" name="smokingAllowed" id="smoking-allowed" value="<?=$proposal["smokingAllowed"]?>" />

                <label for="free">Participation financière obligatoire</label>
                <input type="checkbox" name="free" id="free" value="<?=$proposal["free"]?>" />
                
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

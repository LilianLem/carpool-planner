<?php $title = "Modifier une demande";
ob_start(); ?>

<main>
    <h1>Modifier une demande</h1>

    <form id="request-edit-form" class="basic-form" method="post" action="index.php?action=changeRequest">
        <input type="hidden" name="id" id="id" value="<?=$request["id"]?>" required="required" />

        <label for="start-city">Ville de départ</label>
        <input type="text" name="startCity" id="start-city" value="<?=$request["startCity"]?>" placeholder="Ex : Rouen / Clermont-Ferrand" required="required" maxlength="45" />

        <label for="start-department">Département</label>
        <input type="text" name="startDepartment" id="start-department" value="<?=$request["startDepartment"]?>" placeholder="Ex : 01 / 1 / 34..." required="required" maxlength="2" />

        <label for="start-date">Date de départ</label>
        <input type="date" name="startDate" id="start-date" value="<?=$request["startDate"]?>" required="required" />

        <label for="start-time">Heure de départ</label>
        <input type="time" name="startTime" id="start-time" value="<?=$request["startTime"]?>" required="required" step="900" />

        <label for="needed-seats">Nombre de sièges nécessaires</label>
        <input type="number" name="neededSeats" id="needed-seats" value="<?=$request["neededSeats"]?>" placeholder="Min. 1 / Max. 8" required="required" min="1" max="8" step="1" />

        <label for="return-city">Ville de retour <span class="i">(facultatif)</span></label>
        <input type="text" name="returnCity" id="return-city" value="<?=$request["returnCity"]?>" placeholder="Ex : Rouen / Clermont-Ferrand" maxlength="45" />

        <label for="return-department">Département <span class="i">(facultatif)</span></label>
        <input type="text" name="returnDepartment" id="return-department" value="<?=$request["returnDepartment"]?>" placeholder="Ex : 01 / 1 / 34..." maxlength="2" />

        <label for="return-date">Date de retour <span class="i">(facultatif)</span></label> <p>| <span>Réinitialiser</span></p>
        <input type="date" name="returnDate" id="return-date" value="<?=$request["returnDate"]?>" />

        <label for="return-time">Heure de retour <span class="i">(facultatif)</span></label> <p>| <span>Réinitialiser</span></p>
        <input type="time" name="returnTime" id="return-time" value="<?=$request["returnTime"]?>" step="900" />

        <label for="description">Description libre <span class="i">(facultatif)</span></label>
        <textarea name="description" id="description" placeholder="Max. 500 caractères" maxlength="500"><?=$request["description"]?></textarea>

        <label for="smoker">Passager fumeur</label>
        <input type="checkbox" name="smoker" id="smoker" <?=$request["neededSeats"] ? 'checked="checked"' : ''?> />

        <input class="button" type="submit" value="Valider" />
    </form>
</main>

<?php $mainContent = ob_get_clean();
require_once('generalTemplate.php'); ?>

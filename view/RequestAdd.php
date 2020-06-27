<?php $title = "Soumettre une demande";
ob_start(); ?>

<main>
    <h1>Soumettre une demande</h1>

    <form id="request-submit-form" class="basic-form" method="post" action="index.php?action=newRequest">
        <label for="start-city">Ville de départ</label>
        <input type="text" name="startCity" id="start-city" placeholder="Ex : Rouen / Clermont-Ferrand" required="required" maxlength="45" />

        <label for="start-department">Département<!-- ou pays--></label>
        <input type="text" name="startDepartment" id="start-department" placeholder="Ex : 01 / 1 / 34..." required="required" maxlength="2" /> <!-- placeholder="Ex : 01 / 1 / 34 / BE / CH..." -->

        <label for="start-date">Date de départ</label>
        <input type="date" name="startDate" id="start-date" required="required" /> <!--pattern="[0-9]{2}/[0-9]{2}/[0-9]{4}"-->

        <label for="start-time">Heure de départ</label>
        <input type="time" name="startTime" id="start-time" required="required" step="900" />

        <label for="needed-seats">Nombre de sièges nécessaires</label>
        <input type="number" name="neededSeats" id="needed-seats" placeholder="Min. 1 / Max. 8" required="required" min="1" max="8" step="1" />

        <label for="return-city">Ville de retour <span class="i">(facultatif)</span></label>
        <input type="text" name="returnCity" id="return-city" placeholder="Ex : Rouen / Clermont-Ferrand" maxlength="45" />

        <label for="return-department">Département <span class="i">(facultatif)</span></label>
        <input type="text" name="returnDepartment" id="return-department" placeholder="Ex : 01 / 1 / 34..." maxlength="2" />

        <label for="return-date">Date de retour <span class="i">(facultatif)</span></label>
        <input type="date" name="returnDate" id="return-date" /> <!--pattern="[0-9]{2}/[0-9]{2}/[0-9]{4}"-->

        <label for="return-time">Heure de retour <span class="i">(facultatif)</span></label>
        <input type="time" name="returnTime" id="return-time" step="900" />

        <label for="description">Description libre <span class="i">(facultatif)</span></label>
        <textarea name="description" id="description" placeholder="Max. 500 caractères" maxlength="500"></textarea>

        <label for="smoker">Passager fumeur</label>
        <input type="checkbox" name="smoker" id="smoker" />

        <input class="button" type="submit" value="Soumettre" />
    </form>
</main>

<?php $mainContent = ob_get_clean();
require_once('generalTemplate.php'); ?>

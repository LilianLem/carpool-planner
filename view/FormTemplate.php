<?php
if($formType == "edit") { $data = ($formCategory == "proposal" ? $proposal : $request); }
ob_start();
?>

<main>
    <h1><?=$mainTitle?></h1>

    <form id="<?=$formId?>>" class="basic-form" method="post" action="index.php?action=<?=$formAction?>">
        <?php if($formType == "edit") {?> <input type="hidden" name="id" id="id" value="<?=$data['id'] ?? ''?>" required="required" /><?php } ?>

        <label for="start-city">Ville de départ</label>
        <input type="text" name="startCity" id="start-city" value="<?=$data["startCity"] ?? ''?>" placeholder="Ex : Rouen / Clermont-Ferrand" required="required" maxlength="45" />

        <label for="start-department">Département</label>
        <input type="text" name="startDepartment" id="start-department" value="<?=$data["startDepartment"] ?? ''?>" placeholder="Ex : 01 / 1 / 34..." required="required" maxlength="2" />

        <label for="start-date">Date de départ</label>
        <input type="date" name="startDate" id="start-date" value="<?=$data["startDate"] ?? ''?>" required="required" />

        <label for="start-time">Heure de départ</label>
        <input type="time" name="startTime" id="start-time" value="<?=$data["startTime"] ?? ''?>" required="required" step="900" />

        <?=$dynamicFields1?>

        <label for="return-city">Ville de retour <span class="i">(facultatif)</span></label>
        <input type="text" name="returnCity" id="return-city" value="<?=$data["returnCity"] ?? ''?>" placeholder="Ex : Rouen / Clermont-Ferrand" maxlength="45" />

        <label for="return-department">Département <span class="i">(facultatif)</span></label>
        <input type="text" name="returnDepartment" id="return-department" value="<?=$data["returnDepartment"] ?? ''?>" placeholder="Ex : 01 / 1 / 34..." maxlength="2" />

        <label for="return-date">Date de retour <span class="i">(facultatif)</span></label> <?=($formType == "edit" ? '<p>| <span>Réinitialiser</span></p>' : '')?>
        <input type="date" name="returnDate" id="return-date" value="<?=$data["returnDate"] ?? ''?>" />

        <label for="return-time">Heure de retour <span class="i">(facultatif)</span></label> <?=($formType == "edit" ? '<p>| <span>Réinitialiser</span></p>' : '')?>
        <input type="time" name="returnTime" id="return-time" value="<?=$data["returnTime"] ?? ''?>" step="900" />

        <?=$dynamicFields2 ?? ''?>

        <label for="description">Description libre <span class="i">(facultatif)</span></label>
        <textarea name="description" id="description" placeholder="Max. 500 caractères" maxlength="500"><?=$data["description"] ?? ''?></textarea>

        <?=$dynamicFields3?>

        <input class="button" type="submit" value="<?=$formSubmitValue?>>" />
    </form>
</main>

<?php if($formType == "edit") { $scriptsAfterLoad = '<script type="text/javascript" src="static/js/functions.js"></script>'; }
$mainContent = ob_get_clean();
require_once('GeneralTemplate.php'); ?>

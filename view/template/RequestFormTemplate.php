<?php ob_start(); ?>

<label for="needed-seats">Nombre de sièges nécessaires</label>
<input type="number" name="neededSeats" id="needed-seats" value="<?=$request["neededSeats"] ?? ''?>" placeholder="Min. 1 / Max. 8" required="required" min="1" max="8" step="1" />

<?php $dynamicFields1 = ob_get_clean();
ob_start(); ?>

<label for="smoker">Passager fumeur</label>
<input type="checkbox" name="smoker" id="smoker" <?=@$request["smoker"] ? 'checked="checked"' : ''?> />

<?php $dynamicFields3 = ob_get_clean();
require_once('FormTemplate.php'); ?>

<?php ob_start(); ?>

<label for="max-seats">Nombre de sièges proposés à l'aller</label>
<input type="number" name="maxSeats" id="max-seats" value="<?=$proposal["maxSeats"] ?? ''?>" placeholder="Min. 1 / Max. 8" required="required" min="1" max="8" step="1" />

<label for="available-seats">Nombre de sièges libres à l'aller</label>
<input type="number" name="availableSeats" id="available-seats" value="<?=$proposal["availableSeats"] ?? ''?>" placeholder="Min. 1 / Max. 8" required="required" min="1" max="8" step="1" />

<label for="detour-radius">Détour maximum</label>
<input type="number" name="detourRadius" id="detour-radius" value="<?=$proposal["detourRadius"] ?? ''?>" placeholder="Max. 50km" required="required" min="0" max="50" step="1" />

<?php $dynamicFields1 = ob_get_clean();
ob_start(); ?>

<label for="return-max-seats">Nombre de sièges proposés au retour <span class="i">(facultatif)</span></label>
<input type="number" name="returnMaxSeats" id="return-max-seats" value="<?=$proposal["returnMaxSeats"] ?? ''?>" placeholder="Min. 1 / Max. 8" min="1" max="8" step="1" />

<label for="return-available-seats">Nombre de sièges libres au retour <span class="i">(facultatif)</span></label>
<input type="number" name="returnAvailableSeats" id="return-available-seats" value="<?=$proposal["returnAvailableSeats"] ?? ''?>" placeholder="Min. 1 / Max. 8" min="1" max="8" step="1" />

<?php $dynamicFields2 = ob_get_clean();
ob_start(); ?>

<label for="smoking-allowed">Fumeurs admis</label>
<input type="checkbox" name="smokingAllowed" id="smoking-allowed" <?=@$proposal["smokingAllowed"] ? 'checked="checked"' : ''?> />

<label for="free">Participation financière facultative</label>
<input type="checkbox" name="free" id="free" <?=@$proposal["free"] ? 'checked="checked"' : ''?> />

<?php $dynamicFields3 = ob_get_clean();
require_once('FormTemplate.php'); ?>

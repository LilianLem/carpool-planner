<?php $title = "Détails de la demande";
ob_start(); ?>

<main id="request-details" class="details">
    <h1>Demande #<?=$request['id']?></h1>

    <table>
        <tr>
            <th>Pseudo</th>
            <td><?=$request['username']?></td>
        </tr>

        <tr>
            <th>Ville de départ</th>
            <td><?=$request['startCity'].' ('.$request['startDepartment'].')'?></td>
        </tr>

        <tr>
            <th>Date de départ</th>
            <td><?=$request['startDate']?></td>
        </tr>

        <?php if($request['isReturn']) {?>
        <tr>
            <th>Ville de retour</th>
            <td><?=$request['returnCity'].' ('.$request['returnDepartment'].')'?></td>
        </tr>
        <?php } ?>

        <tr>
            <th>Date de retour</th>
            <td><?= $request['isReturn'] ? $request['returnDate'] : 'Pas de retour';?></td>
        </tr>

        <tr>
            <th>Sièges nécessaires</th>
            <td><?=$request['neededSeats']?></td>
        </tr>

        <tr>
            <th>Fumeur</th>
            <td><?=$request['smoker'] ? 'Oui' : 'Non'?></td>
        </tr>

        <tr>
            <th>Description</th>
            <td><?=(!empty($request['description']) ? $request['description'] : 'Pas de description')?></td>
        </tr>

        <tr>
            <th>Dernière modification</th>
            <td><?=$request['lastEdited']?></td>
        </tr>
    </table>

    <?php if(isset($_SESSION['userId'])){ if($request['userId'] == $_SESSION['userId']) { ?>
        <a title="Modifier ma demande" id="edit-button" class="basic-thumb-button" href="index.php?action=editRequest&id=<?=$request['id']?>">
            <img src="static/img/icons/pencil.png" alt="Crayon" width="35px">
        </a>
    <?php } else {
    if (isset($_POST['showSendingConfirmationPrompt'])) { ?>
    <div class="user-actions confirm">
        <p>Êtes-vous sûr de vouloir envoyer une proposition de trajet à <?=$request['username']?> ?</p>
        <div class="user-actions-buttons">
            <a href="index.php?action=showRequest&id=<?=$request['id']?>" class="rounded-square-button transparent-button"><p>Annuler</p></a>
            <form action="index.php?action=sendMessageRequest&id=<?=$request['id']?>" method="post">
                <input type="hidden" id="request-message-sending-token" name="requestMessageSendingToken" value="<?=$request['id']?>" required="required" />
                <input type="submit" class="rounded-square-button" value="Confirmer" />
            </form>
        </div>
    </div>
    <?php } else { ?>
    <form action="index.php?action=showRequest&id=<?=$request['id']?>" method="post" class="user-actions">
        <input type="hidden" id="show-sending-confirmation-prompt" name="showSendingConfirmationPrompt" value="1" required="required" />
        <div class="user-actions-buttons">
            <input type="submit" class="rounded-square-button" value="Je propose des places" />
        </div>
    </form>
    <?php }}} else { ?>
    <div class="user-actions">
        <div class="user-actions-buttons">
            <a href="index.php?action=register&page=<?=urlencode('index.php?action=showRequest&id='.$request['id'])?>" class="rounded-square-button"><p>Je propose des places</p></a>
        </div>
    </div>
    <?php } ?>
</main>

<?php $mainContent = ob_get_clean();
require_once('generalTemplate.php'); ?>

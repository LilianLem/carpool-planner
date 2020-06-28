<?php $title = "Détails de trajet";
ob_start(); ?>

<main id="proposal-details" class="details">
    <h1>Trajet #<?=$proposal['id']?></h1>

    <table>
        <tr>
            <th>Pseudo</th>
            <td><?=$proposal['username']?></td>
        </tr>

        <tr>
            <th>Ville de départ</th>
            <td><?=$proposal['startCity'].' ('.$proposal['startDepartment'].')'?></td>
        </tr>

        <tr>
            <th>Date de départ</th>
            <td><?=$proposal['startDate']?></td>
        </tr>

        <?php if($proposal['isReturn']) {?>
        <tr>
            <th>Ville de retour</th>
            <td><?=$proposal['returnCity'].' ('.$proposal['returnDepartment'].')'?></td>
        </tr>
        <?php } ?>

        <tr>
            <th>Date de retour</th>
            <td><?= $proposal['isReturn'] ? $proposal['returnDate'] : 'Pas de retour';?></td>
        </tr>

        <tr>
            <th>Sièges disponibles<?php if($proposal['isReturn']) {?> (aller)<?php }?></th>
            <td><?=$proposal['availableSeats']?> / <?=$proposal['maxSeats']?></td>
        </tr>

        <?php if($proposal['isReturn']) {?>
        <tr>
            <th>Sièges disponibles (retour)</th>
            <td><?=$proposal['returnAvailableSeats']?> / <?=$proposal['returnMaxSeats']?></td>
        </tr>
        <?php }?>

        <tr>
            <th>Détour maximal</th>
            <td><?=$proposal['detourRadius']?> km</td>
        </tr>

        <tr>
            <th>Voiture fumeurs</th>
            <td><?=$proposal['smokingAllowed'] ? 'Oui' : 'Non'?></td>
        </tr>

        <tr>
            <th>Coût du trajet</th>
            <td><?=$proposal['free'] ? '<abbr title="Aucune contrepartie monétaire n\'est demandée pour monter à bord, mais le conducteur et vous pouvez tout de même négocier une participation sous une autre forme (nourriture, hébergement sur place...)">Gratuit</abbr>' : '<abbr title="Le conducteur est susceptible de vous demander une participation financière, qui ne peut pas dépasser les frais d\'essence ou de péage. Vous pouvez également lui proposer une contrepartie sous une autre forme (nourriture, hébergement sur place...), qu\'il n\'est cependant pas tenu d\'accepter.">Payant</abbr>'?></td>
        </tr>

        <tr>
            <th>Description</th>
            <td><?=(!empty($proposal['description']) ? $proposal['description'] : 'Pas de description')?></td>
        </tr>

        <tr>
            <th>Dernière modification</th>
            <td><?=$proposal['lastEdited']?></td>
        </tr>
    </table>

    <?php if(isset($_SESSION['userId'])){ if($proposal['userId'] == $_SESSION['userId']) { ?>
        <a title="Modifier mon trajet" id="edit-button" class="basic-thumb-button" href="index.php?action=editProposal&id=<?=$proposal['id']?>">
            <img src="static/img/icons/pencil.png" alt="Crayon" width="35px">
        </a>
    <?php } else {
    if (isset($_POST['showSendingConfirmationPrompt'])) { ?>
        <div class="user-actions confirm">
            <p>Êtes-vous sûr de vouloir envoyer une demande de contact à <?=$proposal['username']?> ?</p>
            <div class="user-actions-buttons">
                <a href="index.php?action=showProposal&id=<?=$proposal['id']?>" class="rounded-square-button transparent-button"><p>Annuler</p></a>
                <form action="index.php?action=sendMessageProposal&id=<?=$proposal['id']?>" method="post">
                    <input type="hidden" id="proposal-message-sending-token" name="proposalMessageSendingToken" value="<?=$proposal['id']?>" required="required" />
                    <input type="submit" class="rounded-square-button" value="Confirmer" />
                </form>
            </div>
        </div>
    <?php } else { ?>
        <form action="index.php?action=showProposal&id=<?=$proposal['id']?>" method="post" class="user-actions">
            <input type="hidden" id="show-sending-confirmation-prompt" name="showSendingConfirmationPrompt" value="1" required="required" />
            <div class="user-actions-buttons">
                <input type="submit" class="rounded-square-button" value="Je suis intéressé" />
            </div>
        </form>
    <?php }}} else { ?>
        <div class="user-actions">
            <div class="user-actions-buttons">
                <a href="index.php?action=register&page=<?=urlencode('index.php?action=showProposal&id='.$proposal['id'])?>"" class="rounded-square-button"><p>Je suis intéressé</p></a>
            </div>
        </div>
    <?php } ?>
</main>

<?php $mainContent = ob_get_clean();
require_once('template/GeneralTemplate.php'); ?>

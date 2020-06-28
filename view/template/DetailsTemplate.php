<?php
$data = ($detailsCategory == "proposal" ? $proposal : $request);

$tableLines = [
    'Pseudo' => $data['username'],
    'Ville de départ' => $data['startCity'].' ('.$data['startDepartment'].')',
    'Date de départ' => $data['startDate']
];

if($data['isReturn']) {
    $tableLines['Ville de retour'] = $data['returnCity'].' ('.$data['returnDepartment'].')';
}

$tableLines['Date de retour'] = ($data['isReturn'] ? $data['returnDate'] : 'Pas de retour');

$tableLines = array_merge($tableLines, $specificLines);

$tableLines = array_merge($tableLines, [
    'Description' => (!empty($data['description']) ? $data['description'] : 'Pas de description'),
    'Dernière modification' => $data['lastEdited']
]);

ob_start(); ?>

<main id="<?=$detailsCategory?>-details" class="details">
    <h1><?=$mainStaticTitle?> #<?=$data['id']?></h1>

    <table>
        <?php
        foreach($tableLines as $lineName => $lineContent)
        { ?>
            <tr>
                <th><?=$lineName?></th>
                <td><?=$lineContent?></td>
            </tr>
        <?php } ?>
    </table>

    <?php if(isset($_SESSION['userId'])){ if($data['userId'] == $_SESSION['userId']) { ?>
        <a title="<?=$editLinkText?>" id="edit-button" class="basic-thumb-button" href="index.php?action=edit<?=ucfirst($detailsCategory)?>&id=<?=$data['id']?>">
            <img src="static/img/icons/pencil.png" alt="Crayon" width="35px">
        </a>
    <?php } else {
    if (isset($_POST['showSendingConfirmationPrompt'])) { ?>
        <div class="user-actions confirm">
            <p>Êtes-vous sûr de vouloir envoyer <?=$confirmText?> à <?=$data['username']?> ?</p>
            <div class="user-actions-buttons">
                <a href="index.php?action=show<?=ucfirst($detailsCategory)?>&id=<?=$data['id']?>" class="rounded-square-button transparent-button"><p>Annuler</p></a>
                <form action="index.php?action=sendMessage<?=ucfirst($detailsCategory)?>&id=<?=$data['id']?>" method="post">
                    <input type="hidden" id="<?=$detailsCategory?>-message-sending-token" name="<?=$detailsCategory?>MessageSendingToken" value="<?=$data['id']?>" required="required" />
                    <input type="submit" class="rounded-square-button" value="Confirmer" />
                </form>
            </div>
        </div>
    <?php } else { ?>
        <form action="index.php?action=show<?=ucfirst($detailsCategory)?>&id=<?=$data['id']?>" method="post" class="user-actions">
            <input type="hidden" id="show-sending-confirmation-prompt" name="showSendingConfirmationPrompt" value="1" required="required" />
            <div class="user-actions-buttons">
                <input type="submit" class="rounded-square-button" value="<?=$promptButtonText?>" />
            </div>
        </form>
    <?php }}} else { ?>
        <div class="user-actions">
            <div class="user-actions-buttons">
                <a href="index.php?action=register&page=<?=urlencode('index.php?action=show'.ucfirst($detailsCategory).'&id='.$data['id'])?>" class="rounded-square-button"><p><?=$promptButtonText?></p></a>
            </div>
        </div>
    <?php } ?>
</main>

<?php $mainContent = ob_get_clean();
require_once('GeneralTemplate.php'); ?>

<?php $title = "Demandes de transport";
ob_start(); ?>

<main id="proposal-list" class="list">
    <h1>Demandes de transport</h1>

    <table>
        <tr>
            <th>Lieu</th>
            <th>Date de départ</th>
            <th>Pseudo</th>
        </tr>

        <?php
        foreach($requests as $request)
        { ?>
        <tr onclick="window.location='index.php?action=showRequest&id=<?=$request['id']?>';">
            <td><?=$request['startCity']?></td>
            <td><?=$request['startDate']?></td>
            <td><?=$request['username']?></td>
        </tr>
        <?php } ?>
    </table>

    <a id="add-button" class="basic-thumb-button" href="index.php?action=addRequest"><p>+</p></a>
</main>

<?php $mainContent = ob_get_clean();
require_once('generalTemplate.php'); ?>

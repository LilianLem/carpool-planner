<?php $title = "Trajets proposés";
ob_start(); ?>

<main id="proposal-list" class="list">
    <h1>Trajets proposés</h1>

    <table>
        <tr>
            <th>Lieu</th>
            <th>Date de départ</th>
            <th>Pseudo</th>
        </tr>

        <?php
        foreach($proposals as $proposal)
        { ?>
        <tr onclick="window.location='index.php?action=showProposal&id=<?=$proposal['id']?>';">
            <td><?=$proposal['startCity']?></td>
            <td><?=$proposal['startDate']?></td>
            <td><?=$proposal['username']?></td>
        </tr>
        <?php } ?>
    </table>

    <a id="add-button" class="basic-thumb-button" href="index.php?action=addProposal"><p>+</p></a>
</main>

<?php $mainContent = ob_get_clean();
require_once('generalTemplate.php'); ?>

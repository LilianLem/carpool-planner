<?php
$data = ($listCategory == "proposal" ? $proposals : $requests);
$columns = ['Lieu','Date de départ','Pseudo']; // A supprimer et à mettre dans le fichier de view plus spécifique lorsque les champs seront différents
$lines = ['startCity','startDate','username']; // A supprimer et à mettre dans le fichier de view plus spécifique lorsque les champs seront différents
ob_start();
?>

<main class="list">
    <h1><?=$mainTitle?></h1>

    <table>
        <tr>
            <?php
            foreach($columns as $column)
            {
                echo "<th>$column</th>";
            }
            ?>
        </tr>

        <?php
        foreach($data as $element)
        { ?>
            <tr onclick="window.location='index.php?action=show<?=ucfirst($listCategory)?>&id=<?=$element['id']?>';">
                <?php
                foreach($lines as $line)
                {
                    echo "<td>$element[$line]</td>";
                }
                ?>
            </tr>
        <?php } ?>
    </table>

    <a id="add-button" class="basic-thumb-button" href="index.php?action=add<?=ucfirst($listCategory)?>"><p>+</p></a>
</main>

<?php $mainContent = ob_get_clean();
require_once('GeneralTemplate.php'); ?>

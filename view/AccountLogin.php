<?php $title = "Connexion";
ob_start(); ?>

<main class="account">
    <h1>Connexion</h1>

    <form id="login-form" class="basic-form" method="post" action="index.php?action=loggingIn<?=isset($_GET['page']) ? '&page='.urlencode(strip_tags($_GET['page'])) : ''?>">
        <label for="email">E-mail</label>
        <input type="email" name="email" id="email" required="required" maxlength="128" value="<?=$prefilledEmail?>" />

        <label for="password">Mot de passe</label>
        <input type="password" name="password" id="password" required="required" maxlength="128" autocomplete="current-password" />

        <input class="button" type="submit" value="Valider" />
    </form>

    <a href="index.php?action=register<?=isset($_GET['page']) ? '&page='.urlencode(strip_tags($_GET['page'])) : ''?>"><p>Pas encore de compte ? Inscrivez-vous !</p></a>
</main>

<?php $mainContent = ob_get_clean();
require_once('template/GeneralTemplate.php'); ?>

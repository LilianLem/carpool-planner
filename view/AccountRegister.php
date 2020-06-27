<?php $title = "Inscription";
ob_start(); ?>

<main class="account">
    <h1>Inscription</h1>

    <form id="register-form" class="basic-form" method="post" action="index.php?action=registration<?=isset($_GET['page']) ? '&page='.urlencode(strip_tags($_GET['page'])) : ''?>">
        <label for="email">E-mail</label>
        <input type="email" name="email" id="email" placeholder="Ex : pierre.dupont@gmail.com" required="required" minlength="6" maxlength="128" value="<?=$prefilledInfos['email']?>" />

        <label for="discord-username">Pseudo Discord</label>
        <input type="text" name="discordUsername" id="discord-username" placeholder="Ex : Pierre#1234" required="required" maxlength="32" value="<?=$prefilledInfos['discordUsername']?>" />

        <label for="password">Mot de passe</label>
        <input type="password" name="password" id="password" required="required" minlength="8" maxlength="128" pattern="(?=.*[a-zß-öø-ÿ])(?=.*[A-ZÀ-ÖØ-Þ])(?=.*\d)(?=.*[!@#$£€%^&*()\\[\]{}\-_+=~`|:;&quot;'<>,.\/?])[A-Za-zÀ-ÖØ-öø-ÿ\d!@#$£€%^&*()\\[\]{}\-_+=~`|:;&quot;'<>,.\/?]{8,128}" title="Merci d'utiliser un mot de passe de 8 à 128 caractères avec au moins 1 minuscule, 1 majuscule, 1 chiffre et 1 caractère spécial" autocomplete="new-password" />

        <input class="button" type="submit" value="Valider" />
    </form>

    <a href="index.php?action=login<?=isset($_GET['page']) ? '&page='.urlencode(strip_tags($_GET['page'])) : ''?>"><p>Déjà inscrit ? Connectez-vous !</p></a>
</main>

<?php $mainContent = ob_get_clean();
require_once('generalTemplate.php'); ?>

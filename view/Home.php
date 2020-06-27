<?php $title = "Accueil";
$bodyId = "body-home";
ob_start(); ?>

<main id="home">
    <div id="intro">
        <h1>Bienvenue sur Carpool Planner !</h1>
        <p>Nous permettons la mise en relation simplifiée entre des personnes se rendant à un même évènement, conducteurs et passagers, afin de passer moins de temps à planifier son trajet vers un concert, spectacle, salon...</p>
        <p>Comment ça marche ? Pour ajouter un trajet ou déposer une demande, il suffit de s'inscrire avec un pseudo Discord et une adresse mail. Les trajets déjà ajoutés et demandes déjà déposées sont visibles par tous via les boutons ci-dessous.</p>
    </div>

    <div id="catchers">
        <div class="catcher">
            <div class="catcher-image round-bg-image"><img src="static/img/icons/car.png" alt="Voiture" width="42px" /></div>

            <div class="catcher-text">
                <h2>Pour les conducteurs</h2>
                <p>Amortissez le coût du trajet</p>
            </div>
        </div>
        <div class="catcher">
            <div class="catcher-image round-bg-image"><img src="static/img/icons/pedestrian.png" alt="Piéton" width="50px" /></div>

            <div class="catcher-text">
                <h2>Pour les passagers</h2>
                <p>Profitez d'une solution à bas coût</p>
            </div>
        </div>
    </div>

    <div id="pros">
        <div class="pro">
            <div class="round-bg-image"><img src="static/img/icons/conversation.png" alt="Bulles de conversation" width="42px" /></div>
            <p>Contact simple</p>
        </div>

        <div class="pro">
            <div class="round-bg-image"><img src="static/img/icons/list.png" alt="Bloc notes" width="42px" /></div>
            <p>Gestion facile</p>
        </div>

        <div class="pro">
            <div class="round-bg-image"><img src="static/img/icons/money.png" alt="Monnaie" width="42px" /></div>
            <p>Sans frais</p>
        </div>
    </div>

    <div id="buttons">
        <a href="index.php?action=showRequests" class="rounded-square-button transparent-button"><p>Demandes</p></a>
        <a href="index.php?action=showProposals" class="rounded-square-button"><p>Trajets disponibles</p></a>
    </div>
</main>

<?php $mainContent = ob_get_clean();
require_once('generalTemplate.php'); ?>

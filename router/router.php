<?php
try {
	require('controller/controller.php');

	if(isset($_GET['action']))
	{
		if($_GET['action'] == 'addProposal')
		{
			displayProposalAddForm();
		}

		if($_GET['action'] == 'showProposal' AND isset($_GET['id']))
		{
			displayProposalDetails();
		}

		elseif($_GET['action'] == 'newProposal')
		{
			checkProposalAdd();
		}

        elseif($_GET['action'] == 'register')
        {
            displayRegisterForm();
        }

        elseif($_GET['action'] == 'registration')
        {
            checkRegistration();
        }

        elseif($_GET['action'] == 'login')
        {
            displayLoginForm();
        }

        elseif($_GET['action'] == 'loggingIn')
        {
            checkLogin();
        }

		else
		{
			// Par défaut si l'action n'est pas reconnue
			displayProposalList();
		}
	}

	else
	{
		// Par défaut si aucune action n'est définie
		displayProposalList();
	}
}

catch(Exception $e) {
    // Si une erreur se produit, on arrive ici
    echo "Une erreur est survenue.<br>Détails : $e";
}

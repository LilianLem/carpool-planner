<?php
try {
	require('controller/controller.php');

	if(isset($_GET['action']))
	{
		if($_GET['action'] == 'addProposal')
		{
			displayProposalAddForm();
		}

		elseif($_GET['action'] == 'newProposal')
		{
			checkProposalAdd();
		}

        elseif($_GET['action'] == 'register')
        {
            displayRegisterForm();
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

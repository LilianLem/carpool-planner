<?php
try {
	require('controller/controller.php');

	if(isset($_GET['action']))
	{
		switch($_GET['action'])
		{
			case 'addProposal':
				displayProposalAddForm();
				break;
			case 'showProposal':
				if(isset($_GET['id']))
				{
					displayProposalDetails();
				}
				else
				{
					$goDefault = 1;
				}
				break;
			case 'newProposal':
				checkProposalAdd();
				break;
			case 'register':
				displayRegisterForm();
				break;
			case 'registration':
				checkRegistration();
				break;
			case 'login':
				displayLoginForm();
				break;
			case 'loggingIn':
				checkLogin();
				break;
			default:
				$goDefault = 1;
				break;
		}
	}

	else
	{
		$goDefault = 1;
	}

	if(isset($goDefault))
	{
		// Par défaut si l'action n'est pas reconnue
		displayProposalList();
	}
}

catch(Exception $e) {
    // Si une erreur se produit, on arrive ici
    echo "Une erreur est survenue.<br>Détails : $e";
}

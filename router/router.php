<?php
try {
	require('controller/ProposalController.php');
	require('controller/RequestController.php');
	require('controller/UserController.php');
	require('controller/HomeController.php');

	if(isset($_GET['action']))
	{
		switch($_GET['action'])
		{
			case 'showProposals':
				displayProposalList();
				break;
			case 'addProposal':
				displayProposalAddForm();
				break;
			case 'showProposal':
				displayProposalDetails();
				break;
			case 'editProposal':
				displayProposalEditForm();
				break;
			case 'changeProposal':
				checkProposalEdit();
				break;
			case 'newProposal':
				checkProposalAdd();
				break;
			case 'sendMessageProposal':
				checkProposalSendMessage();
				break;
			case 'showRequests':
				displayRequestList();
				break;
			case 'addRequest':
				displayRequestAddForm();
				break;
			case 'showRequest':
				displayRequestDetails();
				break;
			case 'editRequest':
				displayRequestEditForm();
				break;
			case 'changeRequest':
				checkRequestEdit();
				break;
			case 'newRequest':
				checkRequestAdd();
				break;
			case 'sendMessageRequest':
				checkRequestSendMessage();
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
			case 'logout':
				logout();
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
		displayHomePage();
	}
}

catch(Exception $e) {
    // Si une erreur se produit, on arrive ici
    echo "Une erreur est survenue.<br>Détails : $e";
}

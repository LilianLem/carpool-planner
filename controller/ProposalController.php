<?php
require_once('controller/DateController.php');
require_once('model/ProposalManager.php');
require_once('model/ApiManager.php');

function displayProposalList($errors = '')
{
	$proposalManager = new ProposalManager();
	$proposals = $proposalManager->getAllProposals();

	require('view/ProposalList.php');
}

function displayProposalAddForm($errors = '')
{
	if(isset($_SESSION['userId']))
	{
		require('view/ProposalAdd.php');
	}
	else
	{
		displayRegisterForm();
		return;
	}
}

function checkProposalFormData()
{
	$proposal = [];
	$errors = '';
	$checkCity = 0;

	if(strlen($_POST['department']) > 2 OR (!ctype_digit($_POST['department']) AND strtolower($_POST['department']) != '2a' AND strtolower($_POST['department']) != '2b'))
	{
		$errors .= "- Le numéro de département est incorrect. Exemples corrects : 01, 1, 34\\n";
	}
	else
	{
		$_POST['department'] = strtoupper($_POST['department']);
		$checkCity++;
	}

	if(!ctype_alpha(utf8_decode(str_replace(array(' ','-'), '', $_POST['city']))))
	{
		$errors .= "- Le format de la ville est incorrect. Exemples corrects : Rouen, Clermont-Ferrand\\n";
	}
	else
	{
		$checkCity++;
	}

	if(strlen($_POST['city']) > 45)
	{
		$errors .= "- Le nom de ville renseigné est trop long (supérieur à 45 caractères)\\n";
	}
	else
	{
		$checkCity++;
	}

	if($checkCity == 3)
	{
		$apiManager = new ApiManager();
		$cityRawData = $apiManager->checkCity(strip_tags($_POST['city']),strip_tags($_POST['department']));

		if(!$cityRawData)
		{
			$errors .= "- La ville n'a pas été trouvée dans la base de l'INSEE\\n";
		}
		else
		{
			$cityData = json_decode($cityRawData);

			$proposal['ville'] = $cityData[0]->nom.' ('.$cityData[0]->codeDepartement.')';
			$proposal['latitude'] = $cityData[0]->centre->coordinates[1];
			$proposal['longitude'] = $cityData[0]->centre->coordinates[0];
		}
	}

	if(!checkDateFormat($_POST['date']))
	{
		$errors .= "- La date de départ renseignée est incorrecte\\n";
	}

	if(!checkTime($_POST['time']))
	{
		$errors .= "- L'heure de départ renseignée est incorrecte\\n";
	}

	if(isset($_POST['return-date']))
	{
		if(!empty($_POST['return-date']))
		{
			if(!checkDateFormat($_POST['return-date']))
			{
				$errors .= "- La date de retour renseignée est incorrecte\\n";
			}

			if(!isset($_POST['return-time']))
			{
				$errors .= "- Une date de retour est renseignée mais pas une heure de retour\\n";
			}
			else
			{
				if(empty($_POST['return-time']))
				{
					$errors .= "- Une date de retour est renseignée mais pas une heure de retour\\n";
				}

				else
				{
					if(!checkTime($_POST['return-time']))
					{
						$errors .= "- L'heure de retour renseignée est incorrecte\\n";
					}
				}
			}
		}
	}

	return ['proposal' => $proposal, 'errors' => $errors];
}

function checkProposalAdd()
{
	if(isset($_POST['city']) AND isset($_POST['department']) AND isset($_POST['date']) AND isset($_POST['time']))
	{
		$checkData = checkProposalFormData();
		$newProposal = $checkData['proposal'];
		$errors = $checkData['errors'];

		if(!empty($errors))
		{
			displayProposalAddForm($errors);
		}
		else
		{
			setlocale(LC_ALL, 'fr_FR.utf8','fra');

			$newProposal['date_depart'] = formatDateTimeForDb($_POST['date'],$_POST['time']);

			if(!empty($_POST['return-date']) AND !empty($_POST['return-time']))
			{
				$newProposal['retour'] = true;
				$newProposal['date_retour'] = formatDateTimeForDb($_POST['return-date'],$_POST['return-time']);
			}
			else
			{
				$newProposal['retour'] = false;
				$newProposal['date_retour'] = NULL;
			}

			$proposalManager = new ProposalManager();
			$proposalManager->insertNewProposal($newProposal);

			displayProposalList();
		}
	}

	else
	{
		displayProposalAddForm("- Vous n'avez pas renseigné tous les champs obligatoires\\n");
	}
}

function displayProposalDetails()
{
	$id = $_GET['id'];

	if(!is_numeric($id))
	{
		displayProposalList("- Le format de l'identifiant de proposition indiqué est incorrect\\n");
	}
	else
	{
		$proposalManager = new ProposalManager();
		$proposal = $proposalManager->getProposal($id);

		if(empty($proposal))
		{
			displayProposalList("- L'identifiant indiqué ne correspond à aucune proposition\\n");
		}
		else
		{
			$proposal['user_id'] = str_pad($proposal['user_id'], 3, "0", STR_PAD_LEFT);

			setlocale(LC_ALL, 'fr_FR.utf8','fra');
			if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
			{
				$monthDateFormat = '%#d';
			}
			else
			{
				$monthDateFormat = '%e';
			}
			$dateFormat = "%A $monthDateFormat %b à %H:%M";

			$startDate = strtotime($proposal['start_date']);
			$proposal['start_date'] = ucfirst(strftime($dateFormat, $startDate));

			if($proposal['return'])
			{
				$returnDate = strtotime($proposal['return_date']);
				$proposal['return_date'] = ucfirst(strftime($dateFormat, $returnDate));
			}

			$lastEditedDate = strtotime($proposal['last_edited']);
			$proposal['last_edited'] = ucfirst(strftime("%A $monthDateFormat %b", $lastEditedDate));

			require('view/ProposalDetails.php');
		}
	}
}
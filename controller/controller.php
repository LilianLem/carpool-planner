<?php
require_once('model/ProposalManager.php');

function displayProposalList()
{
	$proposalManager = new ProposalManager();
	$proposals = $proposalManager->getAllProposals();

	require('view/proposalList.php');
}

function displayProposalAddForm($errors = '')
{
	require('view/proposalAdd.php');
}

function checkProposalAdd()
{
	setlocale(LC_ALL, 'fr_FR.utf8','fra');
	$errors = '';
	if(isset($_POST['discord-username']) AND isset($_POST['city']) AND isset($_POST['department']) AND isset($_POST['date']) AND isset($_POST['time']))
	{
		if(!stristr($_POST['discord-username'], '#'))
		{
			$errors .= "- Le format du pseudo Discord est incorrect. Exemple correct : Pierre#1234\n";
		}
		else
		{
			$discordUsername_array = explode('#', $_POST['discord-username']);

			if(!ctype_digit($discordUsername_array[1]))
			{
				$errors .= "- Le format du pseudo Discord est incorrect. Exemple correct : Pierre#1234\n";
			}
		}

		if(strlen($_POST['discord-username']) > 32)
		{
			$errors .= "- Le pseudo Discord renseigné est trop long (supérieur à 32 caractères)\n";
		}

		if((!ctype_digit($_POST['department']) OR strlen($_POST['department']) > 2) AND (!strcasecmp($_POST['department'], '2a') AND !strcasecmp($_POST['department'], '2b')))
		{
			$errors .= "- Le numéro de département est incorrect. Exemples corrects : 01, 1, 34\n";
		}

		if(!ctype_alpha(utf8_decode(str_replace(array(' ','-'), '', $_POST['city']))))
		{
			$errors .= "- Le format de la ville est incorrect. Exemples corrects : Rouen, Clermont-Ferrand\n";
		}

		if(strlen($_POST['city']) > 45)
		{
			$errors .= "- Le nom de ville renseigné est trop long (supérieur à 45 caractères)\n";
		}
		if(!checkDateFormat($_POST['date']))
		{
			$errors .= "- La date de départ renseignée est incorrecte\n";
		}

		if(!checkTime($_POST['time']))
		{
			$errors .= "- L'heure de départ renseignée est incorrecte\n";
		}

		if(isset($_POST['return-date']))
		{
			if(!empty($_POST['return-date']))
			{
				if(!checkDateFormat($_POST['return-date']))
				{
					$errors .= "- La date de retour renseignée est incorrecte\n";
				}

				if(!isset($_POST['return-time']))
				{
					$errors .= "- Une date de retour est renseignée mais pas une heure de retour\n";
				}
				else
				{
					if(empty($_POST['return-time']))
					{
						$errors .= "- Une date de retour est renseignée mais pas une heure de retour\n";
					}

					else
					{
						if(!checkTime($_POST['return-time']))
						{
							$errors .= "- L'heure de retour renseignée est incorrecte\n";
						}
					}
				}
			}
		}

		if(!empty($errors))
		{
			displayProposalAddForm($errors);
		}
		else
		{

		}
	}

	else
	{
		displayProposalAddForm("- Vous n'avez pas renseigné tous les champs obligatoires\n");
	}
}

function checkDateFormat($date)
{
    if (strtotime($date) === false)
    { 
        return false;
    }

    $date_array = explode('-', $date);
    if(count($date_array) != 3)
    {
    	return false;
    }

    list($year, $month, $day) = explode('-', $date); 
    return checkdate($month, $day, $year);
}

function checkTime($time)
{
    $time_array = explode(':', $time);
    if(count($time_array) != 2)
    {
    	return false;
    }

    if(!ctype_digit($time_array[0]) OR !ctype_digit($time_array[1]))
    {
    	return false;
    }

    if($time_array[0] < 0 OR $time_array[0] > 23 OR $time_array[1] < 0 OR $time_array[1] > 59)
    {
    	return false;
    }
    else
    {
    	return true;
    }
}

}
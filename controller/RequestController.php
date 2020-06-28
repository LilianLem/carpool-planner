<?php
require_once('controller/UtilsController.php');
require_once('model/RequestManager.php');
require_once('model/ApiManager.php');
require_once('config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function displayRequestList($errors = '')
{
	// On récupère toutes les demandes de transport dans la DB
	$requestManager = new RequestManager();
	$requests = $requestManager->getAllRequests();

	// On récupère un format de date pour pouvoir afficher correctement les dates des demandes dans la liste
	$dateFormat = getDateDisplayFormat('list-fullDateMonthReduced');

	// On prépare les données pour la vue, notamment en formatant la date
	foreach($requests as &$element)
	{
		$element = formatArrayKeysInCamelCase($element, '_');

		$startDate = strtotime($element['startDate']);
		$element['startDate'] = ucfirst(strftime($dateFormat, $startDate));
	}

	require('view/RequestList.php');
}

function displayRequestAddForm($errors = '')
{
	if(isset($_SESSION['userId']))
	{
		require('view/RequestAdd.php');
	}
	else
	{
		// Si l'utilisateur n'est pas connecté, on le redirige sur l'inscription
		$_GET['page'] = basename($_SERVER['REQUEST_URI']);
		displayRegisterForm('', '');
		return;
	}
}

function checkAndFormatRequestFormData()
{
	$request = [];
	$errors = '';
	
	$result = checkAndFormatRequestCityData($request, $errors, 'start');
	$request = $result['request'];
	$errors = $result['errors'];

	if(!ctype_digit($_POST['neededSeats']))
	{
		$errors .= "- Le format du nombre de sièges nécessaires est incorrect\\n";
	}
	elseif($_POST['neededSeats'] < 1 OR $_POST['neededSeats'] > 8)
	{
		$errors .= "- Le nombre de sièges nécessaires est incorrect (minimum 1 / maximum 8)\\n";
	}
	else
	{
		$request['neededSeats'] = $_POST['neededSeats'];
	}

	if(isset($_POST['description']))
	{
		if(strlen($_POST['description']) > 500)
		{
			$errors .= "- La description est trop longue\\n";
		}
		else
		{
			$request['description'] = strip_tags($_POST['description']);
		}
	}
	else
	{
		$request['description'] = NULL;
	}

	if(isset($_POST['smoker']))
	{
		$request['smoker'] = 1;
	}
	else
	{
		$request['smoker'] = 0;
	}

	// On initialise une variable permettant de déterminer si toutes les conditions sont réunies pour que la date de départ soit correcte
	$checkStartDate = 0;

	if(!checkDateFormat($_POST['startDate']))
	{
		$errors .= "- La date de départ renseignée est incorrecte\\n";
	}
	else
	{
		$checkStartDate++;
	}

	if(!checkTime($_POST['startTime']))
	{
		$errors .= "- L'heure de départ renseignée est incorrecte\\n";
	}
	else
	{
		$checkStartDate++;
	}

	if($checkStartDate == 2)
	{
		$request['startDate'] = formatDateTimeForDb($_POST['startDate'],$_POST['startTime']);
	}

	// On initialise une variable permettant de déterminer si toutes les conditions sont réunies pour que les informations de retour soient correctes
	$checkReturn = 0;

	if(isset($_POST['returnDate']))
	{
		if(!empty($_POST['returnDate']))
		{
			if(!checkDateFormat($_POST['returnDate']))
			{
				$errors .= "- La date de retour renseignée est incorrecte\\n";
			}

			if(!isset($_POST['returnTime']))
			{
				$errors .= "- Une date de retour est renseignée mais pas une heure de retour\\n";
			}
			else
			{
				if(empty($_POST['returnTime']))
				{
					$errors .= "- Une date de retour est renseignée mais pas une heure de retour\\n";
				}

				else
				{
					if(!checkTime($_POST['returnTime']))
					{
						$errors .= "- L'heure de retour renseignée est incorrecte\\n";
					}
					else
					{
						$checkReturn++;
					}
				}
			}
		}
	}

	if(!empty($_POST['returnCity']) OR !empty($_POST['returnDepartment']))
	{
		if(!empty($_POST['returnCity']) AND !empty($_POST['returnDepartment']))
		{
			$result = checkAndFormatRequestCityData($request, $errors, 'return');
			$request = $result['request'];
			$errors = $result['errors'];

			if(isset($request['returnCity']))
			{
				$checkReturn++;
			}
		}
		else
		{
			$errors .= "- La ville ou le département de retour est manquant\\n";
		}
	}

	if($checkReturn == 2)
	{
		$request['isReturn'] = true;
		$request['returnDate'] = formatDateTimeForDb($_POST['returnDate'],$_POST['returnTime']);

        if($checkStartDate == 2)
        {
            if($request['returnDate'] <= $request['startDate'])
            {
                $errors .= "- La date de retour est antérieure à la date de départ\\n";
            }
        }
	}
	// Comme le retour n'est pas obligatoire, on initialise quand même les variables correspondantes pour l'insertion en DB plus tard
	else
	{
		$request['isReturn'] = 0;
		$request['returnCity'] = NULL;
		$request['returnLat'] = NULL;
		$request['returnLng'] = NULL;
		$request['returnDate'] = NULL;
	}
	
	if($checkReturn > 0 AND $checkReturn < 2)
	{
		$errors .= "- Tous les champs nécessaires pour le retour ne sont pas remplis\\n";
	}

	return ['request' => $request, 'errors' => $errors];
}

function checkAndFormatRequestCityData($request, $errors, $variablePart)
{
	// On initialise une variable permettant de déterminer si toutes les conditions sont réunies pour que la ville soit théoriquement correcte, avant de faire appel à l'API Géo
	$checkCity = 0;

	if(strlen($_POST[$variablePart.'Department']) > 2 OR (!ctype_digit($_POST[$variablePart.'Department']) AND strtolower($_POST[$variablePart.'Department']) != '2a' AND strtolower($_POST[$variablePart.'Department']) != '2b'))
	{
		$errors .= "- Un numéro de département est incorrect. Exemples corrects : 01, 1, 34\\n";
	}
	else
	{
		$_POST[$variablePart.'Department'] = str_pad(strtoupper($_POST[$variablePart.'Department']), 2, "0", STR_PAD_LEFT);
		$checkCity++;
	}

	if(!ctype_alpha(utf8_decode(str_replace(array(' ','-','\''), '', $_POST[$variablePart.'City']))))
	{
		$errors .= "- Le format de la ville est incorrect. Exemples corrects : Rouen, Clermont-Ferrand\\n";
	}
	else
	{
		$checkCity++;
	}

	if(strlen($_POST[$variablePart.'City']) > 45)
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
		$cityRawData = $apiManager->checkCity(strip_tags($_POST[$variablePart.'City']),strip_tags($_POST[$variablePart.'Department']));

		if(!$cityRawData)
		{
			$errors .= "- La ville n'a pas été trouvée dans la base de l'INSEE\\n";
		}
		else
		{
			$cityData = json_decode($cityRawData);

			$request[$variablePart.'City'] = $cityData[0]->code;
			$request[$variablePart.'Lat'] = $cityData[0]->centre->coordinates[1];
			$request[$variablePart.'Lng'] = $cityData[0]->centre->coordinates[0];
		}
	}

	return ['request' => $request, 'errors' => $errors];
}

function checkRequestAdd()
{
	if(isset($_POST['startCity']) AND isset($_POST['startDepartment']) AND isset($_POST['startDate']) AND isset($_POST['startTime']) AND isset($_POST['neededSeats']))
	{
		// Si tous les champs obligatoires sont définis, on appelle la fonction de vérification et de formatage des données
		$checkData = checkAndFormatRequestFormData();
		$newRequest = $checkData['request'];
		$errors = $checkData['errors'];

		if(!empty($errors))
		{
			// S'il y a des erreurs, on retourne sur le formulaire d'ajout et on les affiche...
			displayRequestAddForm($errors);
		}
		else
		{
			// ..., sinon on insère la demande en DB et on affiche la page avec les données ajoutées à l'instant
			$requestManager = new RequestManager();
			$id = $requestManager->insertNewRequest($newRequest);

			displayRequestDetails('', $id);
		}
	}

	else
	{
		displayRequestAddForm("- Vous n'avez pas renseigné tous les champs obligatoires\\n");
	}
}

function displayRequestDetails($errors = '', $id = '')
{
	if(empty($id))
	{
		// La fonction peut être appelée par d'autres fonctions du programme qui spécifient elle-mêmes l'ID de la demande, ou par des liens sur le site où l'ID est inclus en paramètre GET
		if(isset($_GET['id']))
		{
			$id = $_GET['id'];
		}
		else
		{
			displayRequestList('- Aucun identifiant de demande n\'a été spécifié\\n');
			return;
		}
	}

	if(!is_numeric($id))
	{
		displayRequestList("- Le format de l'identifiant de demande indiqué est incorrect\\n");
	}
	else
	{
		$requestManager = new RequestManager();
		$request = $requestManager->getRequest($id);

		if(empty($request))
		{
			displayRequestList("- L'identifiant indiqué ne correspond à aucune demande\\n");
		}
		else
		{
			// Si la demande existe, on formate toutes les données pour la vue, puis on affiche la page de la demande
			$request = formatArrayKeysInCamelCase($request, '_');

			$request['id'] = str_pad($request['id'], 3, "0", STR_PAD_LEFT);
			
			$dateFormat = getDateDisplayFormat('details-fullDate');

			$startDate = strtotime($request['startDate']);
			$request['startDate'] = ucfirst(strftime($dateFormat, $startDate));

			if($request['isReturn'])
			{
				$returnDate = strtotime($request['returnDate']);
				$request['returnDate'] = ucfirst(strftime($dateFormat, $returnDate));
			}

			$lastEditedDate = strtotime($request['lastEdited']);
			$request['lastEdited'] = ucfirst(strftime(getDateDisplayFormat('details-lastEdit'), $lastEditedDate));

			require('view/RequestDetails.php');
		}
	}
}

function displayRequestEditForm($errors = '', $id = '')
{
	if(empty($id))
	{
		if(isset($_GET['id']))
		{
			$id = $_GET['id'];
		}
		else
		{
			displayRequestList('- Aucun identifiant de demande n\'a été spécifié\\n');
			return;
		}
	}

	if(!isset($_SESSION['userId']))
	{
		displayRequestDetails('- Vous n\'avez pas la permission de modifier cette demande\\n', $id);
		return;
	}

	if(!is_numeric($id))
	{
		displayRequestList("- Le format de l'identifiant de demande indiqué est incorrect\\n");
		return;
	}

	$requestManager = new RequestManager();
	$request = $requestManager->getRequest($id);

	if(empty($request))
	{
		displayRequestList("- L'identifiant indiqué ne correspond à aucune demande\\n");
		return;
	}

	// Si la demande avec l'ID spécifié existe (donc si le if précédent est faux), on formate les données pour afficher le formulaire d'édition

	$request = formatArrayKeysInCamelCase($request, '_');

	if($request['userId'] != $_SESSION['userId'])
	{
		displayRequestDetails('- Vous n\'avez pas la permission de modifier cette demande\\n', $id);
		return;
	}

	$request['id'] = str_pad($request['id'], 3, "0", STR_PAD_LEFT);

	$startDateTime = formatDateForForm($request['startDate']);
	$request['startDate'] = $startDateTime['date'];
	$request['startTime'] = $startDateTime['time'];

	if($request['isReturn'])
	{
		$returnDateTime = formatDateForForm($request['returnDate']);
		$request['returnDate'] = $returnDateTime['date'];
		$request['returnTime'] = $returnDateTime['time'];
	}
	else
	{
		$request['returnDate'] = '';
		$request['returnTime'] = '';
	}

	require('view/RequestEdit.php');
}

function checkRequestEdit()
{
	if(!isset($_POST['id']))
	{
		displayRequestList('- Aucun identifiant de demande n\'a été spécifié\\n');
		return;
	}

	$id = $_POST['id'];

	if(!isset($_SESSION['userId']))
	{
		displayRequestDetails('- Vous n\'avez pas la permission de modifier cette demande\\n', $id);
		return;
	}

	if(!is_numeric($id))
	{
		displayRequestList('- Le format de l\'identifiant de demande indiqué est incorrect\\n');
		return;
	}

	$requestManager = new RequestManager();

	$currentRequest = $requestManager->getRequest($id);
	if(empty($currentRequest))
	{
		displayRequestList('- Le format de l\'identifiant de demande indiqué est incorrect\\n');
		return;
	}

	$currentRequest = formatArrayKeysInCamelCase($currentRequest, '_');

	if($currentRequest['userId'] != $_SESSION['userId'])
	{
		displayRequestDetails('- Vous n\'avez pas la permission de modifier cette demande\\n', $id);
		return;
	}

	if(isset($_POST['id']) AND isset($_POST['startCity']) AND isset($_POST['startDepartment']) AND isset($_POST['startDate']) AND isset($_POST['startTime']))
	{
		// Si tous les champs obligatoires sont définis, on vérifie les données et on les formate pour la modification
		$checkData = checkAndFormatRequestFormData();
		$errors = $checkData['errors'];

		if(!empty($errors))
		{
			displayRequestEditForm($errors, $id);
			return;
		}

		$editedRequest = $checkData['request'];

		// S'il n'y a pas eu d'erreurs, on vérifie si toutes les données sont identiques à celles déjà en base. Si c'est le cas, on ne fait pas d'UPDATE, sinon on met à jour
		foreach($editedRequest as $column => $value)
		{
			if($value != $currentRequest[$column])
			{
				$updateRequest = 1;
				break;
			}
		}

		if(isset($updateRequest))
		{
			$requestManager->updateRequest($editedRequest, $id);
		}

		displayRequestDetails('', $id);
	}

	else
	{
		displayRequestEditForm("- Vous n'avez pas renseigné tous les champs obligatoires\\n", $id);
	}
}

function checkRequestSendMessage()
{
	// On vérifie si toutes les conditions sont remplies pour que l'utilisateur puisse envoyer un message à celui qui a fait la demande
	if(!isset($_SESSION['userId']))
	{
		$_GET['page'] = basename($_SERVER['REQUEST_URI']);
		displayRegisterForm('', '');
		return;
	}

	if(!isset($_GET['id']))
	{
		displayRequestList('- Aucun identifiant de demande n\'a été spécifié\\n');
		return;
	}

	$id = $_GET['id'];

	if(!isset($_SESSION['userId']))
	{
		displayRequestDetails('- Vous n\'avez pas la permission de modifier cette demande\\n', $id);
		return;
	}

	if(!is_numeric($id))
	{
		displayRequestList('- Le format de l\'identifiant de demande indiqué est incorrect\\n');
		return;
	}

	$requestManager = new RequestManager();
	$request = $requestManager->getRequest($id);

	if(empty($request))
	{
		displayRequestList("- L'identifiant indiqué ne correspond à aucune demande\\n");
		return;
	}

	$request = formatArrayKeysInCamelCase($request, '_');

	if($request['userId'] == $_SESSION['userId'])
	{
		displayRequestDetails('- Vous ne pouvez pas envoyer une demande de contact à vous-même\\n', $id);
		return;
	}

	if(!isset($_POST['requestMessageSendingToken']))
	{
		displayRequestDetails("- La demande de contact est incorrecte. Si ce message persiste, contactez-nous à l'adresse assistance@fakeEmailAddress.com\\n");
		return;
	}

	if($_POST['requestMessageSendingToken'] != $id)
	{
		displayRequestDetails("- Les données de votre demande de contact sont incorrectes. Si ce message persiste, contactez-nous à l'adresse assistance@fakeEmailAddress.com\\n");
		return;
	}

	// Fonctionnalité non réalisée qui aurait dû être ici : vérification pour voir si une demande de contact a déjà été faite

	$userManager = new UserManager();
	$userContactInfos = formatArrayKeysInCamelCase($userManager->getUserContactInfos($request['userId']), '_');

	$notificationData = [
		'targetedUser' => $request['userId'],
		'sender' => $_SESSION['userId'],
		'requestId' => $request['id'],
		'emailNotify' => $userContactInfos['notifyEmail'],
		'discordNotify' => $userContactInfos['notifyDiscord']
	];

	$requestManager->notifyRequester($notificationData);

	// Si le destinataire de la demande de contact souhaite être contacté par mail (activé pour tous dans la version actuelle du projet), on envoie un mail à l'adresse renseignée en base
	if($userContactInfos['email'])
	{
		$subject = 'Proposition de transport sur votre demande';
		$htmlBody    = '<strong>'.$_SESSION["username"].'</strong> souhaite entrer en contact avec vous pour vous prendre en charge sur <a href="localhost:81/carpoolplanner/index.php?action=showRequest&id='.$request['id'].'">cette demande</a>.<br>Contactez-le via Discord pour plus d\'informations...';
		$textBody = $_SESSION["username"].' souhaite entrer en contact avec vous pour vous prendre en charge sur la demande à l\'adresse suivante : localhost:81/carpoolplanner/index.php?action=showRequest&id='.$request['id'].' Contactez-le via Discord pour plus d\'informations...';

		$result = sendEmail($userContactInfos, $subject, $htmlBody, $textBody);
	}

	displayRequestDetails('', $id);
	if(isset($result))
	{
		if($result['success'])
		{
			echo '<script type="text/javascript">console.log("Email envoyé")</script>';
		}
		else
		{
			echo '<script type="text/javascript">console.log("Le message ne peut pas être envoyé. Erreur : '.$result['error'].'")</script>';
		}
	}
}

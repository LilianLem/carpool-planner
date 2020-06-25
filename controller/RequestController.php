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
	$requestManager = new RequestManager();
	$requests = $requestManager->getAllRequests();

	setlocale(LC_ALL, 'fr_FR.utf8','fra');
	$platformDateFormat = getPlatformFormat();
	$dateFormat = "%A ".$platformDateFormat['day']."/%m à ".$platformDateFormat['hour'].":%M";

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
		$_GET['page'] = urlencode(basename($_SERVER['REQUEST_URI']));
		displayRegisterForm('', '');
		return;
	}
}

function checkAndFormatRequestFormData()
{
	$request = [];
	$errors = '';
	$checkStartCity = 0;

	if(strlen($_POST['startDepartment']) > 2 OR (!ctype_digit($_POST['startDepartment']) AND strtolower($_POST['startDepartment']) != '2a' AND strtolower($_POST['startDepartment']) != '2b'))
	{
		$errors .= "- Le numéro de département est incorrect. Exemples corrects : 01, 1, 34\\n";
	}
	else
	{
		$_POST['startDepartment'] = str_pad(strtoupper($_POST['startDepartment']), 2, "0", STR_PAD_LEFT);
		$checkStartCity++;
	}

	if(!ctype_alpha(utf8_decode(str_replace(array(' ','-','\''), '', $_POST['startCity']))))
	{
		$errors .= "- Le format de la ville est incorrect. Exemples corrects : Rouen, Clermont-Ferrand\\n";
	}
	else
	{
		$checkStartCity++;
	}

	if(strlen($_POST['startCity']) > 45)
	{
		$errors .= "- Le nom de ville renseigné est trop long (supérieur à 45 caractères)\\n";
	}
	else
	{
		$checkStartCity++;
	}

	if($checkStartCity == 3)
	{
		$apiManager = new ApiManager();
		$startCityRawData = $apiManager->checkCity(strip_tags($_POST['startCity']),strip_tags($_POST['startDepartment']));

		if(!$startCityRawData)
		{
			$errors .= "- La ville n'a pas été trouvée dans la base de l'INSEE\\n";
		}
		else
		{
			$startCityData = json_decode($startCityRawData);

			$request['startCity'] = $startCityData[0]->nom.' ('.$startCityData[0]->codeDepartement.')';
			$request['startLat'] = $startCityData[0]->centre->coordinates[1];
			$request['startLng'] = $startCityData[0]->centre->coordinates[0];
		}
	}

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
						$request['isReturn'] = true;
						$request['returnDate'] = formatDateTimeForDb($_POST['returnDate'],$_POST['returnTime']);
					}
				}
			}
		}
	}

	if(!isset($request['isReturn']))
	{
		$request['isReturn'] = false;
		$request['returnDate'] = NULL;
	}

	return ['request' => $request, 'errors' => $errors];
}

function checkRequestAdd()
{
	if(isset($_POST['startCity']) AND isset($_POST['startDepartment']) AND isset($_POST['startDate']) AND isset($_POST['startTime']))
	{
		$checkData = checkAndFormatRequestFormData();
		$newRequest = $checkData['request'];
		$errors = $checkData['errors'];

		if(!empty($errors))
		{
			displayRequestAddForm($errors);
		}
		else
		{
			$requestManager = new RequestManager();
			$requestManager->insertNewRequest($newRequest);

			displayRequestList();
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
			$request = formatArrayKeysInCamelCase($request, '_');

			$request['id'] = str_pad($request['id'], 3, "0", STR_PAD_LEFT);

			setlocale(LC_ALL, 'fr_FR.utf8','fra');
			$platformDateFormat = getPlatformFormat();
			$dateFormat = "%A ".$platformDateFormat['day']." %b à ".$platformDateFormat['hour'].":%M";

			$startDate = strtotime($request['startDate']);
			$request['startDate'] = ucfirst(strftime($dateFormat, $startDate));

			if($request['isReturn'])
			{
				$returnDate = strtotime($request['returnDate']);
				$request['returnDate'] = ucfirst(strftime($dateFormat, $returnDate));
			}

			$lastEditedDate = strtotime($request['lastEdited']);
			$request['lastEdited'] = ucfirst(strftime("%A ".$platformDateFormat['day']." %b", $lastEditedDate));

			require('view/RequestDetails.php');
		}
	}
}

function displayRequestEditForm($errors = '')
{
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

	preg_match("/([A-Za-zàâäèéêëîïôœùûüÿçÀÂÄÈÉÊËÎÏÔŒÙÛÜŸÇ\-' ]+) \(([0-9AB]{2})\)/", $request['startCity'], $parsedStartCity);
	$request['startCity'] = $parsedStartCity[1];
	$request['startDepartment'] = $parsedStartCity[2];

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
		$checkData = checkAndFormatRequestFormData();
		$errors = $checkData['errors'];

		if(!empty($errors))
		{
			displayRequestEditForm($errors);
			return;
		}

		$editedRequest = $checkData['request'];

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
		displayRequestEditForm("- Vous n'avez pas renseigné tous les champs obligatoires\\n");
	}
}

function checkRequestSendMessage()
{
	if(!isset($_SESSION['userId']))
	{
		$_GET['page'] = urlencode(basename($_SERVER['REQUEST_URI']));
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

	// Ajouter ici une vérification pour voir si une demande a déjà été faite

	$userManager = new UserManager();
	$userContactInfos = formatArrayKeysInCamelCase($userManager->getUserContactInfos($request['userId']), '_');

	$notificationData = [
		'targetedUser' => $request['userId'],
		'sender' => $_SESSION['userId'],
		'requestId' => $request['id'],
		'emailNotify' => $userContactInfos['notifyEmail'],
		'discordNotify' => $userContactInfos['notifyDiscord']
	];

	$requestManager->sendMessageToRequester($notificationData);

	if($userContactInfos['email'])
	{
		// On récupère le mail de celui qui souhaite proposer un trajet
		$selfContactInfos = $userManager->getUserContactInfos($_SESSION['userId']);

		require 'vendor/autoload.php';

		$mail = new PHPMailer(true);

		try
		{
			//Server settings
			// $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
			$mail->isSMTP();                                            // Send using SMTP
			$mail->Host       = 'plesk1.dyjix.eu';                    // Set the SMTP server to send through
			$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
			$mail->Username   = WEBSITE_EMAIL;      // SMTP username
			$mail->Password   = WEBSITE_EMAIL_PASSWORD;                               // SMTP password
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
			$mail->Port       = 25;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

			//Recipients
			$mail->setFrom('carpoolplanner@lilianlemoine.fr', 'Carpool Planner');
			$mail->addAddress($userContactInfos['email'], $userContactInfos['username']);     // Add a recipient
			$mail->addReplyTo($selfContactInfos['email'], $_SESSION['username']);

			// Content
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = 'Proposition de transport sur votre demande';
			$mail->Body    = '<strong>'.$_SESSION["username"].'</strong> souhaite entrer en contact avec vous pour vous prendre en charge sur <a href="localhost:81/carpoolplanner/index.php?action=showRequest&id='.$request['id'].'">cette demande</a>.<br>Contactez-le via Discord pour plus d\'informations...';
			$mail->AltBody = $_SESSION["username"].' souhaite entrer en contact avec vous pour vous prendre en charge sur la demande à l\'adresse suivante : localhost:81/carpoolplanner/index.php?action=showRequest&id='.$request['id'].' Contactez-le via Discord pour plus d\'informations...';

			$mail->send();
			$mailSuccess = true;
		}

		catch (Exception $e) {
			$mailSuccess = false;
		}
	}

	displayRequestDetails('', $id);
	if(isset($mailSuccess))
	{
		if($mailSuccess)
		{
			echo '<script type="text/javascript">console.log("Email envoyé")</script>';
		}
		else
		{
			echo '<script type="text/javascript">console.log("Le message ne peut pas être envoyé. Erreur : '.$mail->ErrorInfo.'")</script>';
		}
	}
}
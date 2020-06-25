<?php
require_once('controller/UtilsController.php');
require_once('model/ProposalManager.php');
require_once('model/ApiManager.php');
require_once('config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function displayProposalList($errors = '')
{
	$proposalManager = new ProposalManager();
	$proposals = $proposalManager->getAllProposals();

	setlocale(LC_ALL, 'fr_FR.utf8','fra');
	$platformDateFormat = getPlatformFormat();
	$dateFormat = "%A ".$platformDateFormat['day']."/%m à ".$platformDateFormat['hour'].":%M";

	foreach($proposals as &$element)
	{
		$element = formatArrayKeysInCamelCase($element, '_');

		$startDate = strtotime($element['startDate']);
		$element['startDate'] = ucfirst(strftime($dateFormat, $startDate));
	}

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
		$_GET['page'] = urlencode(basename($_SERVER['REQUEST_URI']));
		displayRegisterForm('', '');
		return;
	}
}

function checkAndFormatProposalFormData()
{
	$proposal = [];
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

	if(!ctype_alpha(utf8_decode(str_replace(array(' ','-'), '', $_POST['startCity']))))
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

			$proposal['startCity'] = $startCityData[0]->nom.' ('.$startCityData[0]->codeDepartement.')';
			$proposal['startLat'] = $startCityData[0]->centre->coordinates[1];
			$proposal['startLng'] = $startCityData[0]->centre->coordinates[0];
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
		$proposal['startDate'] = formatDateTimeForDb($_POST['startDate'],$_POST['startTime']);
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
						$proposal['isReturn'] = true;
						$proposal['returnDate'] = formatDateTimeForDb($_POST['returnDate'],$_POST['returnTime']);
					}
				}
			}
		}
	}

	if(!isset($proposal['isReturn']))
	{
		$proposal['isReturn'] = false;
		$proposal['returnDate'] = NULL;
	}

	return ['proposal' => $proposal, 'errors' => $errors];
}

function checkProposalAdd()
{
	if(isset($_POST['startCity']) AND isset($_POST['startDepartment']) AND isset($_POST['startDate']) AND isset($_POST['startTime']))
	{
		$checkData = checkAndFormatProposalFormData();
		$newProposal = $checkData['proposal'];
		$errors = $checkData['errors'];

		if(!empty($errors))
		{
			displayProposalAddForm($errors);
		}
		else
		{
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

function displayProposalDetails($errors = '', $id = '')
{
	if(empty($id))
	{
		if(isset($_GET['id']))
		{
			$id = $_GET['id'];
		}
		else
		{
			displayProposalList('- Aucun identifiant de proposition n\'a été spécifié\\n');
			return;
		}
	}

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
			$proposal = formatArrayKeysInCamelCase($proposal, '_');

			$proposal['id'] = str_pad($proposal['id'], 3, "0", STR_PAD_LEFT);

			setlocale(LC_ALL, 'fr_FR.utf8','fra');
			$platformDateFormat = getPlatformFormat();
			$dateFormat = "%A ".$platformDateFormat['day']." %b à ".$platformDateFormat['hour'].":%M";

			$startDate = strtotime($proposal['startDate']);
			$proposal['startDate'] = ucfirst(strftime($dateFormat, $startDate));

			if($proposal['isReturn'])
			{
				$returnDate = strtotime($proposal['returnDate']);
				$proposal['returnDate'] = ucfirst(strftime($dateFormat, $returnDate));
			}

			$lastEditedDate = strtotime($proposal['lastEdited']);
			$proposal['lastEdited'] = ucfirst(strftime("%A ".$platformDateFormat['day']." %b", $lastEditedDate));

			require('view/ProposalDetails.php');
		}
	}
}

function displayProposalEditForm($errors = '')
{
	if(!isset($_GET['id']))
	{
		displayProposalList('- Aucun identifiant de proposition n\'a été spécifié\\n');
		return;
	}

	$id = $_GET['id'];

	if(!isset($_SESSION['userId']))
	{
		displayProposalDetails('- Vous n\'avez pas la permission de modifier cette proposition\\n', $id);
		return;
	}

	if(!is_numeric($id))
	{
		displayProposalList("- Le format de l'identifiant de proposition indiqué est incorrect\\n");
		return;
	}

	$proposalManager = new ProposalManager();
	$proposal = $proposalManager->getProposal($id);

	if(empty($proposal))
	{
		displayProposalList("- L'identifiant indiqué ne correspond à aucune proposition\\n");
		return;
	}

	$proposal = formatArrayKeysInCamelCase($proposal, '_');

	if($proposal['userId'] != $_SESSION['userId'])
	{
		displayProposalDetails('- Vous n\'avez pas la permission de modifier cette proposition\\n', $id);
		return;
	}

	$proposal['id'] = str_pad($proposal['id'], 3, "0", STR_PAD_LEFT);

	$startDateTime = formatDateForForm($proposal['startDate']);
	$proposal['startDate'] = $startDateTime['date'];
	$proposal['startTime'] = $startDateTime['time'];

	if($proposal['isReturn'])
	{
		$returnDateTime = formatDateForForm($proposal['returnDate']);
		$proposal['returnDate'] = $returnDateTime['date'];
		$proposal['returnTime'] = $returnDateTime['time'];
	}
	else
	{
		$proposal['returnDate'] = '';
		$proposal['returnTime'] = '';
	}

	preg_match("/([A-Za-zàâäèéêëîïôœùûüÿçÀÂÄÈÉÊËÎÏÔŒÙÛÜŸÇ\-' ]+) \(([0-9AB]{2})\)/", $proposal['startCity'], $parsedStartCity);
	$proposal['startCity'] = $parsedStartCity[1];
	$proposal['startDepartment'] = $parsedStartCity[2];

	require('view/ProposalEdit.php');
}

function checkProposalEdit()
{
	if(!isset($_POST['id']))
	{
		displayProposalList('- Aucun identifiant de proposition n\'a été spécifié\\n');
		return;
	}

	$id = $_POST['id'];

	if(!isset($_SESSION['userId']))
	{
		displayProposalDetails('- Vous n\'avez pas la permission de modifier cette proposition\\n', $id);
		return;
	}

	if(!is_numeric($id))
	{
		displayProposalList('- Le format de l\'identifiant de proposition indiqué est incorrect\\n');
		return;
	}

	$proposalManager = new ProposalManager();

	$currentProposal = $proposalManager->getProposal($id);
	if(empty($currentProposal))
	{
		displayProposalList('- Le format de l\'identifiant de proposition indiqué est incorrect\\n');
		return;
	}

	$currentProposal = formatArrayKeysInCamelCase($currentProposal, '_');

	if($currentProposal['userId'] != $_SESSION['userId'])
	{
		displayProposalDetails('- Vous n\'avez pas la permission de modifier cette proposition\\n', $id);
		return;
	}

	if(isset($_POST['id']) AND isset($_POST['startCity']) AND isset($_POST['startDepartment']) AND isset($_POST['startDate']) AND isset($_POST['startTime']))
	{
		$checkData = checkAndFormatProposalFormData();
		$errors = $checkData['errors'];

		if(!empty($errors))
		{
			displayProposalEditForm($errors);
			return;
		}

		$editedProposal = $checkData['proposal'];

		foreach($editedProposal as $column => $value)
		{
			if($value != $currentProposal[$column])
			{
				$updateProposal = 1;
				break;
			}
		}

		if(isset($updateProposal))
		{
			$proposalManager->updateProposal($editedProposal, $id);
		}

		displayProposalDetails('', $id);
	}

	else
	{
		displayProposalEditForm("- Vous n'avez pas renseigné tous les champs obligatoires\\n");
	}
}

function checkProposalSendMessage()
{
	if(!isset($_SESSION['userId']))
	{
		$_GET['page'] = urlencode(basename($_SERVER['REQUEST_URI']));
		displayRegisterForm('', '');
		return;
	}

	if(!isset($_GET['id']))
	{
		displayProposalList('- Aucun identifiant de proposition n\'a été spécifié\\n');
		return;
	}

	$id = $_GET['id'];

	if(!isset($_SESSION['userId']))
	{
		displayProposalDetails('- Vous n\'avez pas la permission de modifier cette proposition\\n', $id);
		return;
	}

	if(!is_numeric($id))
	{
		displayProposalList('- Le format de l\'identifiant de proposition indiqué est incorrect\\n');
		return;
	}

	$proposalManager = new ProposalManager();
	$proposal = $proposalManager->getProposal($id);

	if(empty($proposal))
	{
		displayProposalList("- L'identifiant indiqué ne correspond à aucune proposition\\n");
		return;
	}

	$proposal = formatArrayKeysInCamelCase($proposal, '_');

	if($proposal['userId'] == $_SESSION['userId'])
	{
		displayProposalDetails('- Vous ne pouvez pas envoyer une demande de contact à vous-même\\n', $id);
		return;
	}

	if(!isset($_POST['proposalMessageSendingToken']))
	{
		displayProposalDetails("- La demande de contact est incorrecte. Si ce message persiste, contactez-nous à l'adresse assistance@fakeEmailAddress.com\\n");
		return;
	}

	if($_POST['proposalMessageSendingToken'] != $id)
	{
		displayProposalDetails("- Les données de votre demande de contact sont incorrectes. Si ce message persiste, contactez-nous à l'adresse assistance@fakeEmailAddress.com\\n");
		return;
	}

	// Ajouter ici une vérification pour voir si une demande a déjà été faite

	$userManager = new UserManager();
	$userContactInfos = formatArrayKeysInCamelCase($userManager->getUserContactInfos($proposal['userId']), '_');

	$notificationData = [
		'targetedUser' => $proposal['userId'],
		'sender' => $_SESSION['userId'],
		'proposalId' => $proposal['id'],
		'emailNotify' => $userContactInfos['notifyEmail'],
		'discordNotify' => $userContactInfos['notifyDiscord']
	];

	$proposalManager->sendMessageToDriver($notificationData);

	if($userContactInfos['email'])
	{
		// On récupère le mail de celui qui a fait la demande
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
			$mail->Subject = 'Demande de contact sur votre trajet';
			$mail->Body    = '<strong>'.$_SESSION["username"].'</strong> souhaite entrer en contact avec vous pour le prendre en charge sur <a href="localhost:81/carpoolplanner/index.php?action=showProposal&id='.$proposal['id'].'">ce trajet</a>.<br>Contactez-le via Discord pour plus d\'informations...';
			$mail->AltBody = $_SESSION["username"].' souhaite entrer en contact avec vous pour le prendre en charge sur le trajet à l\'adresse suivante : localhost:81/carpoolplanner/index.php?action=showProposal&id='.$proposal['id'].' Contactez-le via Discord pour plus d\'informations...';

			$mail->send();
			$mailSuccess = true;
		}

		catch (Exception $e) {
			$mailSuccess = false;
		}
	}

	displayProposalDetails('', $id);
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
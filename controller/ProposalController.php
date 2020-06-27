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
		$_GET['page'] = basename($_SERVER['REQUEST_URI']);
		displayRegisterForm('', '');
		return;
	}
}

function checkAndFormatProposalFormData()
{
	$proposal = [];
	$errors = '';
	
	$result = checkAndFormatProposalCityData($proposal, $errors, 'start');
	$proposal = $result['proposal'];
	$errors = $result['errors'];

	$result = checkAndFormatProposalSeats($proposal, $errors);
	$proposal = $result['proposal'];
	$errors = $result['errors'];
	
	if(isset($_POST['description']))
	{
		if(strlen($_POST['description']) > 500)
		{
			$errors .= "- La description est trop longue\\n";
		}
		else
		{
			$proposal['description'] = strip_tags($_POST['description']);
		}
	}
	else
	{
		$proposal['description'] = NULL;
	}

	if(!isset($_POST['detourRadius']))
	{
		$errors .= "- Le détour autorisé n'a pas été renseigné\\n";
	}
	elseif(!ctype_digit($_POST['detourRadius']))
	{
		$errors .= "- Le format du détour autorisé est incorrect\\n";
	}
	else
	{
		if($_POST['detourRadius'] < 1 OR $_POST['detourRadius'] > 50)
		{
			$errors .= "- La distance de détour autorisé est incorrecte (minimum 1km / maximum 50km)\\n";
		}
		else
		{
			$proposal['detourRadius'] = $_POST['detourRadius'];
		}
	}
	
	if(isset($_POST['smokingAllowed']))
	{
		$proposal['smokingAllowed'] = 1;
	}
	else
	{
		$proposal['smokingAllowed'] = 0;
	}

	if(isset($_POST['free']))
	{
		$proposal['free'] = 0;
	}
	else
	{
		$proposal['free'] = 1;
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
			$result = checkAndFormatProposalCityData($proposal, $errors, 'return');
			$proposal = $result['proposal'];
			$errors = $result['errors'];

			if(isset($proposal['returnCity']))
			{
				$checkReturn++;
			}
		}
		else
		{
			$errors .= "- La ville ou le département de retour est manquant\\n";
		}
	}
	
	if(isset($_POST['returnAvailableSeats']) OR isset($_POST['returnMaxSeats']))
	{
		if(isset($_POST['returnAvailableSeats']) AND isset($_POST['returnMaxSeats']))
		{
			$result = checkAndFormatProposalSeats($proposal, $errors, 'return');
			$proposal = $result['proposal'];

			if($errors != $result['errors'])
			{
				$errors = $result['errors'];
			}
			else
			{
				$checkReturn++;
			}
		}
		else
		{
			$errors .= "- Le nombre de sièges proposés ou libres au retour est manquant\\n";
		}
	}
	
	if($checkReturn == 3)
	{
		$proposal['isReturn'] = true;
		$proposal['returnDate'] = formatDateTimeForDb($_POST['returnDate'],$_POST['returnTime']);

		if($checkStartDate == 2)
        {
            if($proposal['returnDate'] <= $proposal['startDate'])
            {
                $errors .= "- La date de retour est antérieure à la date de départ\\n";
            }
        }
	}
	else
	{
		$proposal['isReturn'] = 0;
		$proposal['returnCity'] = NULL;
		$proposal['returnLat'] = NULL;
		$proposal['returnLng'] = NULL;
		$proposal['returnDate'] = NULL;
		$proposal['returnAvailableSeats'] = NULL;
		$proposal['returnMaxSeats'] = NULL;
	}
	
	if($checkReturn > 0 AND $checkReturn < 3)
	{
		$errors .= "- Tous les champs nécessaires pour le retour ne sont pas remplis\\n";
	}

	return ['proposal' => $proposal, 'errors' => $errors];
}

function checkAndFormatProposalCityData($proposal, $errors, $variablePart)
{
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

			$proposal[$variablePart.'City'] = $cityData[0]->code;
			$proposal[$variablePart.'Lat'] = $cityData[0]->centre->coordinates[1];
			$proposal[$variablePart.'Lng'] = $cityData[0]->centre->coordinates[0];
		}
	}
	
	return ['proposal' => $proposal, 'errors' => $errors];
}

function checkAndFormatProposalSeats($proposal, $errors, $variablePart = '')
{
	$available = "available";
	$max = "max";
	
	if(!empty($variablePart))
	{
		$available = ucfirst($available);
		$max = ucfirst($max);
	}
	
	$checkSeats = 0;
	
	if(!ctype_digit($_POST[$variablePart.$max.'Seats']))
	{
		$errors .= "- Le format du nombre de sièges proposés est incorrect\\n";
	}
	else
	{
		$checkSeats++;
		if($_POST[$variablePart.$max.'Seats'] < 1 OR $_POST[$variablePart.$max.'Seats'] > 8)
		{
			$errors .= "- Le nombre de sièges proposés est incorrect (minimum 1 / maximum 8)\\n";
		}
	}
	
	if(!ctype_digit($_POST[$variablePart.$available.'Seats']))
	{
		$errors .= "- Le format du nombre de sièges libres est incorrect\\n";
	}
	else
	{
		$checkSeats++;
		if($_POST[$variablePart.$available.'Seats'] < 1 OR $_POST[$variablePart.$available.'Seats'] > 8)
		{
			$errors .= "- Le nombre de sièges libres est incorrect (minimum 1 / maximum 8)\\n";
		}
	}
	
	if($checkSeats == 2)
	{
		if($_POST[$variablePart.$available.'Seats'] > $_POST[$variablePart.$max.'Seats'])
		{
			$errors .= "- Le nombre de sièges libres ne peut pas être supérieur au nombre de sièges proposés\\n";
		}
		else
		{
			$proposal[$variablePart.$available.'Seats'] = $_POST[$variablePart.$available.'Seats'];
			$proposal[$variablePart.$max.'Seats'] = $_POST[$variablePart.$max.'Seats'];
		}
	}
	
	return ['proposal' => $proposal, 'errors' => $errors];
}

function checkProposalAdd()
{
	if(isset($_POST['startCity']) AND isset($_POST['startDepartment']) AND isset($_POST['startDate']) AND isset($_POST['startTime']) AND isset($_POST['availableSeats']) AND isset($_POST['maxSeats']) AND isset($_POST['detourRadius']))
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
			$id = $proposalManager->insertNewProposal($newProposal);

			displayProposalDetails('', $id);
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

	if(isset($_POST['id']) AND isset($_POST['startCity']) AND isset($_POST['startDepartment']) AND isset($_POST['startDate']) AND isset($_POST['startTime']) AND isset($_POST['availableSeats']) AND isset($_POST['maxSeats']) AND isset($_POST['detourRadius']))
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
		$_GET['page'] = basename($_SERVER['REQUEST_URI']);
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

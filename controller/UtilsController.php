<?php
require_once('model/ProposalManager.php');
require_once('model/RequestManager.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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

function formatDateTimeForDb($date,$time)
{
	$date_array = explode('-', $date);
	$day_alpha = ucfirst(strftime("%A",strtotime($date)));
	return $date.' '.$time.':00';
}

function formatDateForForm($date)
{
	$time = substr($date, 11, 5);
	$date = substr($date, 0, 10);
	return ['date' => $date, 'time' => $time];
}

// Récupère le format du jour et de l'heure spécifique à la plateforme (différent sous Windows)
function getPlatformFormat()
{
	if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
	{
		return ['day' => '%#d', 'hour' => '%#H'];
	}
	else
	{
		return ['day' => '%e', 'hour' => '%k'];
	}
}

// Récupère un format de date spécifique à son utilisation sur le site
function getDateDisplayFormat($formatType)
{
	$platformFormat = getPlatformFormat();
	
	switch($formatType)
	{
		case 'list-fullDateMonthReduced':
			$dateFormat = "%A ".$platformFormat['day']."/%m à ".$platformFormat['hour'].":%M";
			break;
			
		case 'details-lastEdit':
			$dateFormat = "%A ".$platformFormat['day']." %b";
			break;

		case 'details-fullDate':
		default:
			$dateFormat = "%A ".$platformFormat['day']." %b à ".$platformFormat['hour'].":%M";
			break;
	}
	
	return $dateFormat;
}

// Fonction permettant de formater les clés de tableaux dont les mots sont séparés par un caractère en camel case (utilisé pour les views notamment)
function formatArrayKeysInCamelCase(array $array, string $separator)
{
	$formattedArray = [];

	foreach($array as $key => $element)
	{
		$key_array = explode($separator, $key);

		$newKey_array = array_map('ucfirst', $key_array);
		$newKey_array[0] = $key_array[0];

		$newKey = implode('', $newKey_array);

		$formattedArray[$newKey] = $element;
	}

	return $formattedArray;
}

function sendEmail($recipient, $subject, $htmlBody, $textBody)
{
	// On récupère le mail de celui qui a fait la demande
	$userManager = new UserManager();
	$selfContactInfos = $userManager->getUserContactInfos($_SESSION['userId']);

	require_once 'vendor/autoload.php';
	require_once 'config.php' ;

	$mail = new PHPMailer(true);

	try
	{
		//Server settings
		// $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
		$mail->isSMTP();                                            // Send using SMTP
		$mail->Host       = WEBSITE_EMAIL_HOST;                    // Set the SMTP server to send through
		$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
		$mail->Username   = WEBSITE_EMAIL;      // SMTP username
		$mail->Password   = WEBSITE_EMAIL_PASSWORD;                               // SMTP password
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
		$mail->Port       = WEBSITE_EMAIL_PORT;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

		//Recipients
		$mail->setFrom(WEBSITE_EMAIL, 'Carpool Planner');
		$mail->addAddress($recipient['email'], $recipient['username']);     // Add a recipient
		$mail->addReplyTo($selfContactInfos['email'], $_SESSION['username']);

		// Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $htmlBody;
		$mail->AltBody = $textBody;

		$mail->send();
		$mailSent = [
			'success' => true,
			'error' => ''
		];
	}

	catch (Exception $e) {
		$mailSent = [
			'success' => false,
			'error' => $mail->ErrorInfo
		];
	}
	
	return $mailSent;
}
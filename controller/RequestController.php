<?php
require_once('controller/UtilsController.php');
require_once('model/RequestManager.php');
require_once('model/ApiManager.php');

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
		displayRegisterForm();
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

	var_dump($request);

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
		$request = formatArrayKeysInCamelCase($requestManager->getRequest($id), '_');

		if(empty($request))
		{
			displayRequestList("- L'identifiant indiqué ne correspond à aucune demande\\n");
		}
		else
		{
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
	$request = formatArrayKeysInCamelCase($requestManager->getRequest($id), '_');

	if(empty($request))
	{
		displayRequestList("- L'identifiant indiqué ne correspond à aucune demande\\n");
		return;
	}

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

	$currentRequest = formatArrayKeysInCamelCase($requestManager->getRequest($id), '_');
	if(empty($currentRequest))
	{
		displayRequestList('- Le format de l\'identifiant de demande indiqué est incorrect\\n');
		return;
	}

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
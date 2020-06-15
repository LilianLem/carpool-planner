<?php
require_once('model/ProposalManager.php');
require_once('model/UserManager.php');
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

function checkProposalAdd()
{
	setlocale(LC_ALL, 'fr_FR.utf8','fra');
	$errors = '';
	$checkCity = 0;

	if(isset($_POST['city']) AND isset($_POST['department']) AND isset($_POST['date']) AND isset($_POST['time']))
	{
		if((!ctype_digit($_POST['department']) OR strlen($_POST['department']) > 2) AND (!strcasecmp($_POST['department'], '2a') AND !strcasecmp($_POST['department'], '2b')))
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

				$city = $cityData[0]->nom;
				$department = $cityData[0]->codeDepartement;
				$latitude = $cityData[0]->centre->coordinates[1];
				$longitude = $cityData[0]->centre->coordinates[0];
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

		if(!empty($errors))
		{
			displayProposalAddForm($errors);
		}
		else
		{
			$newProposal['ville'] = $city.' ('.$department.')';
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
			
			$newProposal['latitude'] = $latitude;
			$newProposal['longitude'] = $longitude;

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

function displayRegisterForm($errors = '', $prefilledInfos = ['discord_username' => '', 'email' => ''])
{
    require('view/AccountRegister.php');
}

function checkRegistration()
{
    $errors = '';

    if(isset($_POST['discord-username']) AND isset($_POST['email']) AND isset($_POST['password']))
    {
        if(!stristr($_POST['discord-username'], '#'))
        {
            $errors .= "- Le format du pseudo Discord est incorrect. Exemple correct : Pierre#1234\\n";
        }
        else
        {
            $discordUsername_array = explode('#', $_POST['discord-username']);

            if(!ctype_digit($discordUsername_array[1]) OR empty($discordUsername_array[0]) OR strlen($discordUsername_array[1]) < 4)
            {
                $errors .= "- Le format du pseudo Discord est incorrect. Exemple correct : Pierre#1234\\n";
            }
        }

        if(strlen($_POST['discord-username']) > 32)
        {
            $errors .= "- Le pseudo Discord renseigné est trop long (supérieur à 32 caractères)\\n";
        }

        if(strlen($_POST['email']) < 6)
        {
            $errors .= "- L'adresse mail renseignée est trop courte (inférieure à 6 caractères)\\n";
        }
        elseif(strlen($_POST['email']) > 128)
        {
            $errors .= "- L'adresse mail renseignée est trop longue (supérieure à 128 caractères)\\n";
        }

        if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
        {
            $errors .= "- Le format de l'adresse mail est incorrect. Exemple correct : pierre.dupont@gmail.com\\n";
        }

        if(strlen($_POST['password']) < 8)
        {
            $errors .= "- Le mot de passe renseigné est trop court (inférieur à 8 caractères)\\n";
        }
        elseif(strlen($_POST['password']) > 128)
        {
            $errors .= "- Le mot de passe renseigné est trop long (supérieur à 128 caractères)\\n";
        }

        if(!preg_match("/(?=.*[a-zß-öø-ÿ])(?=.*[A-ZÀ-ÖØ-Þ])(?=.*\d)(?=.*[!@#\$£€%^&*()\\[\]{}\-_+=~`|:;\"'<>,.\/?])/", $_POST['password']))
        {
            $errors .= "- Le format du mot de passe est incorrect. Il doit comporter au moins 1 minuscule, 1 majuscule, 1 chiffre et 1 caractère spécial\\n";
        }

        preg_match("/[A-Za-zÀ-ÖØ-öø-ÿ\d!@#\$£€%^&*()\\[\]{}\-_+=~`|:;\"'<>,.\/? ]+/", $_POST['password'], $checkedPassword);
        if($checkedPassword[0] != $_POST['password'])
        {
            $errors .= "- Le mot de passe comporte un caractère non autorisé. Si ce message persiste après un changement de mot de passe, contactez-nous à l'adresse assistance@fakeEmailAddress.com\\n";
        }

        // Tableau contenant les valeurs du formulaire à préremplir si une erreur survient et que l'application retourne sur la page d'inscription
        $prefilledInfos = ['discord_username' => strip_tags($_POST['discord-username']), 'email' => strip_tags($_POST['email'])];

        if(!empty($errors))
        {
            displayRegisterForm($errors, $prefilledInfos);
        }
        else
        {
            $newUser['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $newUser['discord_username'] = strip_tags($_POST['discord-username']);
            $newUser['email'] = strip_tags($_POST['email']);

            $userManager = new UserManager();
            $checkNewUser = $userManager->insertNewUser($newUser);

            if($checkNewUser == 'existingUser')
            {
                $errors .= "- Un utilisateur existe déjà avec ce nom d'utilisateur ou cette adresse mail\\n";
                displayRegisterForm($errors, $prefilledInfos);
            }
            elseif(empty($checkNewUser))
            {
                $errors .= "- Nous avons rencontré un problème lors de l'enregistrement de votre compte, veuillez réessayer. Si ce message persiste, contactez-nous à l'adresse assistance@fakeEmailAddress.com\\n";
                displayRegisterForm($errors, $prefilledInfos);
            }
            else
            {
                displayProposalList(); // À changer pour faire arriver sur la page de connexion
            }
        }
    }
    else
    {
        $prefilledInfos['discord_username'] = isset($_POST['email']) ? strip_tags($_POST['email']) : '';
        $prefilledInfos['password'] = isset($_POST['password']) ? strip_tags($_POST['password']) : '';

        displayRegisterForm("- Vous n'avez pas renseigné tous les champs obligatoires\\n", $prefilledInfos);
    }
}

function displayLoginForm($errors = '', $prefilledEmail = '')
{
    require('view/AccountLogin.php');
}

function checkLogin()
{
    $errors = '';

    if(isset($_POST['email']) AND isset($_POST['password']))
    {
        if(strlen($_POST['email']) > 128)
        {
            $errors .= "- L'adresse mail renseignée est trop longue (supérieure à 128 caractères)\\n";
        }

        if(strlen($_POST['password']) > 128)
        {
            $errors .= "- Le mot de passe renseigné est trop long (supérieur à 128 caractères)\\n";
        }

        preg_match("/[A-Za-zÀ-ÖØ-öø-ÿ\d!@#\$£€%^&*()\\[\]{}\-_+=~`|:;\"'<>,.\/? ]+/", $_POST['password'], $checkedPassword);
        if($checkedPassword[0] != $_POST['password'])
        {
            $errors .= "- Le mot de passe comporte un caractère non autorisé. Si ce message persiste, contactez-nous à l'adresse assistance@fakeEmailAddress.com\\n";
        }

        // Mail à préremplir si une erreur survient et que l'application retourne sur la page de connexion
        $prefilledEmail = strip_tags($_POST['email']);

        if(!empty($errors))
        {
            displayLoginForm($errors, $prefilledEmail);
        }
        else
        {
            $credentials['password'] = strip_tags($_POST['password']);
            $credentials['email'] = strip_tags($_POST['email']);

            $userManager = new UserManager();
            $checkCredentials = $userManager->checkUserToLogin($credentials);

            if(empty($checkCredentials))
            {
                $errors .= "- Les identifiants renseignés sont incorrects\\n";
                displayLoginForm($errors, $prefilledEmail);
            }
            else
            {
                $_SESSION['userId'] = $checkCredentials['id'];
                $_SESSION['username'] = $checkCredentials['username']; // INITIALISER LA SESSION SUR LES VIEWS
                displayProposalList(); // À changer pour faire arriver sur la page d'où l'utilisateur vient
            }
        }
    }
    else
    {
        $prefilledEmail = isset($_POST['email']) ? strip_tags($_POST['email']) : '';

        displayLoginForm("- Vous n'avez pas renseigné tous les champs obligatoires\\n", $prefilledEmail);
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

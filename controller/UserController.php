<?php
require_once('model/UserManager.php');

function displayRegisterForm($errors = '', $prefilledInfos = ['discordUsername' => '', 'email' => ''])
{
	require('view/AccountRegister.php');
}

function checkRegistration()
{
	$errors = '';

	if(isset($_POST['discordUsername']) AND isset($_POST['email']) AND isset($_POST['password']))
	{
		if(!stristr($_POST['discordUsername'], '#'))
		{
			$errors .= "- Le format du pseudo Discord est incorrect. Exemple correct : Pierre#1234\\n";
		}
		else
		{
			$discordUsername_array = explode('#', $_POST['discordUsername']);

			if(!ctype_digit($discordUsername_array[1]) OR empty($discordUsername_array[0]) OR strlen($discordUsername_array[1]) < 4)
			{
				$errors .= "- Le format du pseudo Discord est incorrect. Exemple correct : Pierre#1234\\n";
			}
		}

		if(strlen($_POST['discordUsername']) > 32)
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
		$prefilledInfos = ['discordUsername' => strip_tags($_POST['discordUsername']), 'email' => strip_tags($_POST['email'])];

		if(!empty($errors))
		{
			displayRegisterForm($errors, $prefilledInfos);
		}
		else
		{
			$newUser['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
			$newUser['discordUsername'] = strip_tags($_POST['discordUsername']);
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
				displayHomePage();
			}
		}
	}
	else
	{
		$prefilledInfos['discordUsername'] = isset($_POST['email']) ? strip_tags($_POST['email']) : '';
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
				$_SESSION['username'] = $checkCredentials['username'];
				displayHomePage();
			}
		}
	}
	else
	{
		$prefilledEmail = isset($_POST['email']) ? strip_tags($_POST['email']) : '';

		displayLoginForm("- Vous n'avez pas renseigné tous les champs obligatoires\\n", $prefilledEmail);
	}
}

function logout()
{
	session_destroy();

	if(isset($_GET['page']))
	{
		$redirectPage = strip_tags($_GET['page']);
		header("Location: $redirectPage");
		exit;
	}
	else
	{
		header("Location: index.php");
		exit;
	}
}
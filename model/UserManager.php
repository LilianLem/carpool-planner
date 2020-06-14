<?php
require_once('model/DatabaseManager.php');

class UserManager extends DatabaseManager
{
	public function insertNewUser($userData)
	{
        $db = $this->dbConnect();

        $userId = $this->checkUser($userData);
        if(!empty($userId))
        {
            return 'existingUser';
        }

		$newUser = $db->prepare('INSERT INTO user(username, email, password, role, notify_email, notify_discord, last_login, registered, activated) VALUES(:username, :email, :password, 1, 0, 0, NULL, CURRENT_TIMESTAMP, 1)');
		$newUser->execute(array(
			'username' => $userData['discord_username'],
			'email' => $userData['email'],
            'password' => $userData['password']
		));

		return $db->lastInsertId();
	}

	public function checkUser($userData)
	{
        $db = $this->dbConnect();

		$user_raw = $db->prepare('SELECT id FROM user WHERE username = :username OR email = :email LIMIT 1');
        $user_raw->execute(array(
            'username' => $userData['discord_username'],
            'email' => $userData['email']
        ));
		$user = $user_raw->fetch();
		if(empty($user))
		{
			return 0;
		}
		else
		{
			return $user['id'];
		}
	}
}

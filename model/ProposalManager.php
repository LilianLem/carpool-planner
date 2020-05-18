<?php
require_once('model/DatabaseManager.php');

class ProposalManager extends DatabaseManager
{
	public function getAllProposals()
	{
		$db = $this->dbSqlConnect();

		$proposals_raw = $db->prepare('SELECT p.start_city, p.start_date, u.username FROM proposal p INNER JOIN user u ON p.user_id = u.id');
		$proposals_raw->execute();
		$proposals = $proposals_raw->fetchAll();

		return $proposals;
	}

	public function insertNewProposal($proposalData)
	{
		$db = $this->dbConnect();
	private function insertNewUser($username,$db)
	{
		$randomMailId = $this->generateRandomMailId($db);
		$fakeMail = $randomMailId.'@fakeDiscordEmail.com';

		$newProposal = $db->get($db->count());
		$transferUser = $db->prepare('INSERT INTO user(username, email, password, role, notify_email, notify_discord, last_login, registered, activated) VALUES(:username, :email, "$2y$10$Qa3JJ/.59hKnApYbzudSD.fvd8mcbBz.TQF167KoTE/Tc5/4mZkqa", 1, 0, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 0)');
		$transferUser->execute(array(
			'username' => $username,
			'email' => $fakeMail
		));

		return $db->lastInsertId();
	}

	private function checkUser($username,$db)
	{
		$user_raw = $db->prepare('SELECT id FROM user WHERE username = ?');
		$user_raw->execute(array($username));
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

	private function generateRandomMailId($db)
	{
		while(1 == 1)
		{
			$random = rand(1000000,1999999);

		$newProposal->ville = $proposalData['ville'];
		$newProposal->discord_username = $proposalData['discord_username'];
		$newProposal->date_depart = $proposalData['date_depart'];
		$newProposal->date_retour = $proposalData['date_retour'];
		$newProposal->latitude = $proposalData['latitude'];
		$newProposal->longitude = $proposalData['longitude'];
			$user_raw = $db->prepare('SELECT id FROM user WHERE email = ?');
			$user_raw->execute(array($random.'@fakeDiscordEmail.com'));
			$user = $user_raw->fetch();
			if(empty($user))
			{
				break;
			}
		}

		$newProposal->save();
	}
}
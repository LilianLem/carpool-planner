<?php
require_once('model/DatabaseManager.php');

class ProposalManager extends DatabaseManager
{
	public function getAllProposals()
	{
		$db = $this->dbConnect();

		$proposals_raw = $db->prepare('SELECT p.id, p.start_city, p.start_date, u.username FROM proposal p INNER JOIN user u ON p.user_id = u.id ORDER BY p.id');
		$proposals_raw->execute();
		$proposals = $proposals_raw->fetchAll();

		return $proposals;
	}

	public function getProposal($id)
	{
		$db = $this->dbConnect();

		$proposal_raw = $db->prepare('SELECT p.id, p.user_id, u.username, p.start_city, p.start_date, p.available_seats, p.max_seats, p.return, p.return_city, p.return_date, p.return_available_seats, p.return_max_seats, p.detour_radius, p.description, p.smoking_allowed, p.free, p.last_edited, p.status FROM proposal p INNER JOIN user u ON p.user_id = u.id WHERE p.id = ? LIMIT 1');
		$proposal_raw->execute(array($id));
		$proposal = $proposal_raw->fetch();

		return $proposal;
	}

	public function insertNewProposal($proposalData)
	{
		$db = $this->dbConnect();

		$newProposal = $db->prepare('INSERT INTO proposal(`user_id`, `start_city`, `start_lat`, `start_lng`, `start_date`, `available_seats`, `max_seats`, `return`, `return_city`, `return_lat`, `return_lng`, `return_date`, `return_available_seats`, `return_max_seats`, `detour_radius`, `description`, `smoking_allowed`, `free`, `created`, `last_edited`, `status`) VALUES(:userId, :city, :lat, :lng, :startDate, 4, 4, :return, :city, :lat, :lng, :returnDate, 4, 4, 10, "Pas de description", 1, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1)');
		$newProposal->execute(array(
			'userId' => $_SESSION['userId'],
			'city' => $proposalData['ville'],
			'lat' => $proposalData['latitude'],
			'lng' => $proposalData['longitude'],
			'startDate' => $proposalData['date_depart'],
			'return' => $proposalData['retour'],
			'returnDate' => $proposalData['date_retour']
		));
	}

	private function insertNewUser($username,$email,$db)
	{
		$transferUser = $db->prepare('INSERT INTO user(username, email, password, role, notify_email, notify_discord, last_login, registered, activated) VALUES(:username, :email, "$2y$10$Qa3JJ/.59hKnApYbzudSD.fvd8mcbBz.TQF167KoTE/Tc5/4mZkqa", 1, 0, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 0)');
		$transferUser->execute(array(
			'username' => $username,
			'email' => $email
		));

		return $db->lastInsertId();
	}

	private function checkUser($username,$db)
	{
		$user_raw = $db->prepare('SELECT id FROM user WHERE username = ? LIMIT 1');
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
}

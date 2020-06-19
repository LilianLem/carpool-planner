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

		$proposal_raw = $db->prepare('SELECT p.id, p.user_id, u.username, p.start_city, p.start_lat, p.start_lng, p.start_date, p.available_seats, p.max_seats, p.is_return, p.return_city, p.return_lat, p.return_lng, p.return_date, p.return_available_seats, p.return_max_seats, p.detour_radius, p.description, p.smoking_allowed, p.free, p.last_edited, p.status FROM proposal p INNER JOIN user u ON p.user_id = u.id WHERE p.id = ? LIMIT 1');
		$proposal_raw->execute(array($id));
		$proposal = $proposal_raw->fetch();

		return $proposal;
	}

	public function insertNewProposal($proposalData)
	{
		$db = $this->dbConnect();

		$newProposal = $db->prepare('INSERT INTO proposal(`user_id`, `start_city`, `start_lat`, `start_lng`, `start_date`, `available_seats`, `max_seats`, `is_return`, `return_city`, `return_lat`, `return_lng`, `return_date`, `return_available_seats`, `return_max_seats`, `detour_radius`, `description`, `smoking_allowed`, `free`, `created`, `last_edited`, `status`) VALUES(:userId, :city, :lat, :lng, :startDate, 4, 4, :isReturn, :city, :lat, :lng, :returnDate, 4, 4, 10, "Pas de description", 1, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1)');
		$newProposal->execute(array(
			'userId' => $_SESSION['userId'],
			'city' => $proposalData['startCity'],
			'lat' => $proposalData['startLat'],
			'lng' => $proposalData['startLng'],
			'startDate' => $proposalData['startDate'],
			'isReturn' => $proposalData['isReturn'],
			'returnDate' => $proposalData['returnDate']
		));
	}

	public function updateProposal($proposalData, $id)
	{
		$db = $this->dbConnect();

		$newProposal = $db->prepare('UPDATE proposal SET `start_city` = :city, `start_lat` = :lat, `start_lng` = :lng, `start_date` = :startDate, `is_return` = :isReturn, `return_city` = :city, `return_lat` = :lat, `return_lng` = :lng, `return_date` = :returnDate, `last_edited` = CURRENT_TIMESTAMP WHERE `id` = :id');
		$newProposal->execute(array(
			'id' => $id,
			'city' => $proposalData['startCity'],
			'lat' => $proposalData['startLat'],
			'lng' => $proposalData['startLng'],
			'startDate' => $proposalData['startDate'],
			'isReturn' => $proposalData['isReturn'],
			'returnDate' => $proposalData['returnDate']
		));
	}

	public function sendMessageToDriver($messageData)
	{
		$db = $this->dbConnect();

		$newMessage = $db->prepare('INSERT INTO notification(user_id, user_id_second, proposal_link_id, email_notified, discord_notified, `type`) VALUES(:targetedUser, :sender, :proposalId, :emailNotify, :discordNotify, 7)');
		$newMessage->execute($messageData);
	}
}

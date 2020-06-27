<?php
require_once('model/DatabaseManager.php');

class ProposalManager extends DatabaseManager
{
	public function getAllProposals()
	{
		$db = $this->dbConnect();

		$proposals_raw = $db->prepare('SELECT p.id, city.name AS start_city, city.department AS start_department, p.start_date, u.username FROM (proposal p INNER JOIN ext_city city ON p.start_city = city.id) INNER JOIN user u ON p.user_id = u.id ORDER BY p.id');
		$proposals_raw->execute();
		$proposals = $proposals_raw->fetchAll();

		return $proposals;
	}

	public function getProposal($id)
	{
		$db = $this->dbConnect();

		$proposal_raw = $db->prepare('SELECT p.id, p.user_id, u.username, city.name AS start_city, city.department AS start_department, p.start_lat, p.start_lng, p.start_date, p.available_seats, p.max_seats, p.is_return, city2.name AS return_city, city2.department AS return_department, p.return_lat, p.return_lng, p.return_date, p.return_available_seats, p.return_max_seats, p.detour_radius, p.description, p.smoking_allowed, p.free, p.last_edited, p.status FROM (proposal p INNER JOIN ext_city city ON p.start_city = city.id INNER JOIN ext_city city2 ON p.return_city = city2.id) INNER JOIN user u ON p.user_id = u.id WHERE p.id = ? LIMIT 1');
		$proposal_raw->execute(array($id));
		$proposal = $proposal_raw->fetch();

		return $proposal;
	}

	public function insertNewProposal($proposalData)
	{
		$db = $this->dbConnect();

		$newProposal = $db->prepare('INSERT INTO proposal(`user_id`, `start_city`, `start_lat`, `start_lng`, `start_date`, `available_seats`, `max_seats`, `is_return`, `return_city`, `return_lat`, `return_lng`, `return_date`, `return_available_seats`, `return_max_seats`, `detour_radius`, `description`, `smoking_allowed`, `free`, `created`, `last_edited`, `status`) VALUES(:userId, :startCity, :startLat, :startLng, :startDate, :availableSeats, :maxSeats, :isReturn, :returnCity, :returnLat, :returnLng, :returnDate, :returnAvailableSeats, :returnMaxSeats, :detourRadius, :description, :smokingAllowed, :free, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1)');
		$newProposal->execute(array(
			'userId' => $_SESSION['userId'],
			'startCity' => $proposalData['startCity'],
			'startLat' => $proposalData['startLat'],
			'startLng' => $proposalData['startLng'],
			'startDate' => $proposalData['startDate'],
			'availableSeats' => $proposalData['availableSeats'],
			'maxSeats' => $proposalData['maxSeats'],
			'isReturn' => $proposalData['isReturn'],
			'returnCity' => $proposalData['returnCity'],
			'returnLat' => $proposalData['returnLat'],
			'returnLng' => $proposalData['returnLng'],
			'returnDate' => $proposalData['returnDate'],
			'returnAvailableSeats' => $proposalData['returnAvailableSeats'],
			'returnMaxSeats' => $proposalData['returnMaxSeats'],
			'description' => $proposalData['description'],
			'detourRadius' => $proposalData['detourRadius'],
			'smokingAllowed' => $proposalData['smokingAllowed'],
			'free' => $proposalData['free']
		));
		
		return $db->lastInsertId();
	}

	public function updateProposal($proposalData, $id)
	{
		$db = $this->dbConnect();

		$newProposal = $db->prepare('UPDATE proposal SET `start_city` = :startCity, `start_lat` = :startLat, `start_lng` = :startLng, `start_date` = :startDate, `available_seats` = :availableSeats, `max_seats` = :maxSeats, `is_return` = :isReturn, `return_city` = :returnCity, `return_lat` = :returnLat, `return_lng` = :returnLng, `return_date` = :returnDate, `return_available_seats` = :returnAvailableSeats, `return_max_seats` = :returnMaxSeats, `description` = :description, `detour_radius` = :detourRadius, `smoking_allowed` = :smokingAllowed, `free` = :free, `last_edited` = CURRENT_TIMESTAMP WHERE `id` = :id');
		$newProposal->execute(array(
			'id' => $id,
			'startCity' => $proposalData['startCity'],
			'startLat' => $proposalData['startLat'],
			'startLng' => $proposalData['startLng'],
			'startDate' => $proposalData['startDate'],
			'availableSeats' => $proposalData['availableSeats'],
			'maxSeats' => $proposalData['maxSeats'],
			'isReturn' => $proposalData['isReturn'],
			'returnCity' => $proposalData['returnCity'],
			'returnLat' => $proposalData['returnLat'],
			'returnLng' => $proposalData['returnLng'],
			'returnDate' => $proposalData['returnDate'],
			'returnAvailableSeats' => $proposalData['returnAvailableSeats'],
			'returnMaxSeats' => $proposalData['returnMaxSeats'],
			'description' => $proposalData['description'],
			'detourRadius' => $proposalData['detourRadius'],
			'smokingAllowed' => $proposalData['smokingAllowed'],
			'free' => $proposalData['free']
		));
	}

	public function sendMessageToDriver($messageData)
	{
		$db = $this->dbConnect();

		$newMessage = $db->prepare('INSERT INTO notification(user_id, user_id_second, proposal_link_id, email_notified, discord_notified, `type`) VALUES(:targetedUser, :sender, :proposalId, :emailNotify, :discordNotify, 7)');
		$newMessage->execute($messageData);
	}
}

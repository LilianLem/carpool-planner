<?php
require_once('model/DatabaseManager.php');

class RequestManager extends DatabaseManager
{
	public function getAllRequests()
	{
		$db = $this->dbConnect();

		$requests_raw = $db->prepare('SELECT r.id, city.name AS start_city, city.department AS start_department, r.start_date, u.username FROM (request r INNER JOIN ext_city city ON r.start_city = city.id) INNER JOIN user u ON r.user_id = u.id ORDER BY r.id');
		$requests_raw->execute();
		$requests = $requests_raw->fetchAll();

		return $requests;
	}

	public function getRequest($id)
	{
		$db = $this->dbConnect();

		$request_raw = $db->prepare('SELECT r.id, r.user_id, u.username, city.name AS start_city, city.department AS start_department, r.start_lat, r.start_lng, r.start_date, r.needed_seats, r.is_return, city2.name AS return_city, city2.department AS return_department, r.return_lat, r.return_lng, r.return_date, r.description, r.smoker, r.last_edited, r.status FROM (request r INNER JOIN ext_city city ON r.start_city = city.id INNER JOIN ext_city city2 ON r.return_city = city2.id) INNER JOIN user u ON r.user_id = u.id WHERE r.id = ? LIMIT 1');
		$request_raw->execute(array($id));
		$request = $request_raw->fetch();

		return $request;
	}

	public function insertNewRequest($requestData)
	{
		$db = $this->dbConnect();

		$newRequest = $db->prepare('INSERT INTO request(`user_id`, `start_city`, `start_lat`, `start_lng`, `start_date`, `needed_seats`, `is_return`, `return_city`, `return_lat`, `return_lng`, `return_date`, `description`, `smoker`, `created`, `last_edited`, `status`) VALUES(:userId, :startCity, :startLat, :startLng, :startDate, :neededSeats, :isReturn, :returnCity, :returnLat, :returnLng, :returnDate, :description, :smoker, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1)');
		$newRequest->execute(array(
			'userId' => $_SESSION['userId'],
			'startCity' => $requestData['startCity'],
			'startLat' => $requestData['startLat'],
			'startLng' => $requestData['startLng'],
			'startDate' => $requestData['startDate'],
			'neededSeats' => $requestData['neededSeats'],
			'isReturn' => $requestData['isReturn'],
			'returnCity' => $requestData['returnCity'],
			'returnLat' => $requestData['returnLat'],
			'returnLng' => $requestData['returnLng'],
			'returnDate' => $requestData['returnDate'],
			'description' => $requestData['description'],
			'smoker' => $requestData['smoker']
		));
		
		return $db->lastInsertId();
	}

	public function updateRequest($requestData, $id)
	{
		$db = $this->dbConnect();

		$newRequest = $db->prepare('UPDATE request SET `start_city` = :startCity, `start_lat` = :startLat, `start_lng` = :startLng, `start_date` = :startDate, `needed_seats` = :neededSeats, `is_return` = :isReturn, `return_city` = :returnCity, `return_lat` = :returnLat, `return_lng` = :returnLng, `return_date` = :returnDate, `description` = :description, `smoker` = :smoker, `last_edited` = CURRENT_TIMESTAMP WHERE `id` = :id');
		$newRequest->execute(array(
			'id' => $id,
			'startCity' => $requestData['startCity'],
			'startLat' => $requestData['startLat'],
			'startLng' => $requestData['startLng'],
			'startDate' => $requestData['startDate'],
			'neededSeats' => $requestData['neededSeats'],
			'isReturn' => $requestData['isReturn'],
			'returnCity' => $requestData['returnCity'],
			'returnLat' => $requestData['returnLat'],
			'returnLng' => $requestData['returnLng'],
			'returnDate' => $requestData['returnDate'],
			'description' => $requestData['description'],
			'smoker' => $requestData['smoker']
		));
	}

	public function sendMessageToRequester($messageData)
	{
		$db = $this->dbConnect();

		$newMessage = $db->prepare('INSERT INTO notification(user_id, user_id_second, proposal_link_id, email_notified, discord_notified, `type`) VALUES(:targetedUser, :sender, :requestId, :emailNotify, :discordNotify, 8)');
		$newMessage->execute($messageData);
	}
}

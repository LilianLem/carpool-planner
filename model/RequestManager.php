<?php
require_once('model/DatabaseManager.php');

class RequestManager extends DatabaseManager
{
	public function getAllRequests()
	{
		$db = $this->dbConnect();

		$requests_raw = $db->prepare('SELECT r.id, r.start_city, r.start_date, u.username FROM request r INNER JOIN user u ON r.user_id = u.id ORDER BY r.id');
		$requests_raw->execute();
		$requests = $requests_raw->fetchAll();

		return $requests;
	}

	public function getRequest($id)
	{
		$db = $this->dbConnect();

		$request_raw = $db->prepare('SELECT r.id, r.user_id, u.username, r.start_city, r.start_lat, r.start_lng, r.start_date, r.needed_seats, r.is_return, r.return_city, r.return_lat, r.return_lng, r.return_date, r.description, r.smoker, r.last_edited, r.status FROM request r INNER JOIN user u ON r.user_id = u.id WHERE r.id = ? LIMIT 1');
		$request_raw->execute(array($id));
		$request = $request_raw->fetch();

		return $request;
	}

	public function insertNewRequest($requestData)
	{
		$db = $this->dbConnect();

		$newRequest = $db->prepare('INSERT INTO request(`user_id`, `start_city`, `start_lat`, `start_lng`, `start_date`, `needed_seats`, `is_return`, `return_city`, `return_lat`, `return_lng`, `return_date`, `description`, `smoker`, `created`, `last_edited`, `status`) VALUES(:userId, :city, :lat, :lng, :startDate, 4, :isReturn, :city, :lat, :lng, :returnDate, "Pas de description", 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1)');
		$newRequest->execute(array(
			'userId' => $_SESSION['userId'],
			'city' => $requestData['startCity'],
			'lat' => $requestData['startLat'],
			'lng' => $requestData['startLng'],
			'startDate' => $requestData['startDate'],
			'isReturn' => $requestData['isReturn'],
			'returnDate' => $requestData['returnDate']
		));
	}

	public function updateRequest($requestData, $id)
	{
		$db = $this->dbConnect();

		$newRequest = $db->prepare('UPDATE request SET `start_city` = :city, `start_lat` = :lat, `start_lng` = :lng, `start_date` = :startDate, `is_return` = :isReturn, `return_city` = :city, `return_lat` = :lat, `return_lng` = :lng, `return_date` = :returnDate, `last_edited` = CURRENT_TIMESTAMP WHERE `id` = :id');
		$newRequest->execute(array(
			'id' => $id,
			'city' => $requestData['startCity'],
			'lat' => $requestData['startLat'],
			'lng' => $requestData['startLng'],
			'startDate' => $requestData['startDate'],
			'isReturn' => $requestData['isReturn'],
			'returnDate' => $requestData['returnDate']
		));
	}
}

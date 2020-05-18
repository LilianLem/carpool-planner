<?php
require_once('model/ProposalManager.php');

class DatabaseTransfer extends ProposalManager
{
	public function transferUsersToMySqlDatabase()
	{
		$db = $this->dbConnect();

		$proposals = $this->getAllProposals();

		$dbSql = $this->dbSqlConnect();
		foreach($proposals as $proposal)
		{
			$checkUser = $this->checkUser($proposal['discord_username'],$dbSql);
			if($checkUser)
			{
				continue;
			}

			$this->insertNewUser($proposal['discord_username'],$dbSql);
		}
	}

	public function transferProposalsToMySqlDatabase()
	{
		$db = $this->dbConnect();

		$proposals = $this->getAllProposals();

		$dbSql = $this->dbSqlConnect();
		foreach($proposals as $proposal)
		{
			if(is_null($proposal['date_depart']))
			{
				continue;
			}

			$userId_raw = $dbSql->prepare('SELECT id FROM user WHERE username = ?');
			$userId_raw->execute(array($proposal['discord_username']));
			$userId = $userId_raw->fetch();

			$startDate_array = $this->regexDate($proposal['date_depart']);
			$startDate = $startDate_array['day'].' '.$startDate_array['time'];

			$returnDate_array = $this->regexDate($proposal['date_retour']);
			$returnDate = $returnDate_array['day'].' '.$returnDate_array['time'];

			$transferProposal = $dbSql->prepare('INSERT INTO proposal(`user_id`, `start_city`, `start_lat`, `start_lng`, `start_date`, `available_seats`, `max_seats`, `return`, `return_city`, `return_lat`, `return_lng`, `return_date`, `return_available_seats`, `return_max_seats`, `detour_radius`, `description`, `smoking_allowed`, `free`, `created`, `last_edited`, `status`) VALUES(:userId, :city, :lat, :lng, :startDate, 4, 4, 1, :city, :lat, :lng, :returnDate, 4, 4, 10, "Pas de description", 1, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1)');
			$transferProposal->execute(array(
				'userId' => $userId['id'],
				'city' => $proposal['ville'],
				'lat' => $proposal['latitude'],
				'lng' => $proposal['longitude'],
				'startDate' => $startDate,
				'returnDate' => $returnDate
			));
		}
	}

	private function regexDate($date)
	{
		preg_match('/^([0-9]{2}\/[0-9]{2})/', $date, $dayResults);
		if(!empty($dayResults))
		{
			$day = $dayResults[0];

			$day_array = explode('/', $day);
			$day = '2019-'.$day_array[1].'-'.$day_array[0];
		}
		else
		{
			$day = '2019-06-29';
		}

		preg_match('/[0-9]{1,2}h([0-9]{1,2})?/', $date, $timeResults);
		if(!empty($timeResults))
		{
			$time = $timeResults[0];

			$time = str_replace('h', ':', $time);
			if(substr($time, -1) == ':')
			{
				$time .= '00';
			}
		}
		else
		{
			if(stristr($date, 'matin'))
			{
				$time = '06:00';
			}
			elseif(stristr($date, 'après-midi'))
			{
				$time = '14:00';
			}
			elseif(stristr($date, 'midi'))
			{
				$time = '12:00';
			}
			elseif(stristr($date, 'soir'))
			{
				$time = '19:00';
			}
			else
			{
				$time = '04:00';
			}
		}

		$time .= ':00';

		return array('day' => $day, 'time' => $time);
	}
}

$databaseTransfer = new DatabaseTransfer();

$databaseTransfer->transferUsersToMySqlDatabase();
$databaseTransfer->transferProposalsToMySqlDatabase();
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

		$newProposal = $db->get($db->count());

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


		$newProposal->ville = $proposalData['ville'];
		$newProposal->discord_username = $proposalData['discord_username'];
		$newProposal->date_depart = $proposalData['date_depart'];
		$newProposal->date_retour = $proposalData['date_retour'];
		$newProposal->latitude = $proposalData['latitude'];
		$newProposal->longitude = $proposalData['longitude'];

		$newProposal->save();
	}
}
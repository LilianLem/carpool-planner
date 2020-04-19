<?php
require_once('model/DatabaseManager.php');

class ProposalManager extends DatabaseManager
{
	public function getAllProposals()
	{
		$db = $this->dbConnect();

		$proposals = $db->where('discord_username','NOT','NULL')->results();

		return $proposals;
	}
}
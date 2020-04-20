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

	public function insertNewProposal($proposalData)
	{
		$db = $this->dbConnect();

		$newProposal = $db->get($db->count());

		$newProposal->ville = $proposalData['ville'];
		$newProposal->discord_username = $proposalData['discord_username'];
		$newProposal->date_depart = $proposalData['date_depart'];
		$newProposal->date_retour = $proposalData['date_retour'];
		$newProposal->latitude = $proposalData['latitude'];
		$newProposal->longitude = $proposalData['longitude'];

		$newProposal->save();
	}
}
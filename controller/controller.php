<?php
require_once('model/ProposalManager.php');

function displayProposalList()
{
	$proposalManager = new ProposalManager();
	$proposals = $proposalManager->getAllProposals();

	require('view/proposalList.php');
}

function displayProposalAddForm()
{
	require('view/proposalAdd.php');
}

function checkProposalAdd()
{
	if(isset($_POST['discord-username']) AND isset($_POST['city']) AND isset($_POST['department']) AND isset($_POST['date']) AND isset($_POST['time']))
	{
	}

	else
	{
	}
}
}
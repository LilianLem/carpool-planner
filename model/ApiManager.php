<?php

require 'vendor/autoload.php';

class ApiManager
{
	public function checkCity($city,$department)
	{
		$result = file_get_contents('https://geo.api.gouv.fr/communes?nom='.urlencode($city).'&codeDepartement='.$department.'&fields=nom,centre,codeDepartement&format=json&geometry=centre');

		if(!$result or empty($result))
		{
			return false;
		}

		return $result;
	}
}
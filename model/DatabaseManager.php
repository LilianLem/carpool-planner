<?php

require 'vendor/autoload.php';

class DatabaseManager
{
	protected function dbConnect()
	{
		$db = new \Filebase\Database([
		    'dir' => 'database/',
		]);

		return $db;
	}

	protected function dbSqlConnect()
	{
		try
		{
			$db = new PDO('mysql:host=localhost;dbname=carpoolplanner;charset=utf8mb4', 'root', '');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (Exception $e)
		{
			die('Erreur : ' . $e->getMessage());
		}

		return $db;
	}
}
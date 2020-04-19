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
}
<?php
require __DIR__ . '/vendor/autoload.php';

$dataToImport = file_get_contents('./database-content-import.json');
// print_r($dataToImport);
$jsontoImport = json_decode($dataToImport);
// echo "<pre>";
// print_r($jsontoImport);
// echo "</pre>";

$finalDataToImport = array();
foreach($jsontoImport->trajets as &$jsonElement)
{
	unset($jsonElement->champ_inutile);
	array_push($finalDataToImport, $jsonElement);
}

// echo "<pre>";
// print_r($jsontoImport);
// echo "</pre>";

echo "<pre>";
print_r($finalDataToImport);
echo "</pre>";

// setting the access and configration to your database
$database = new \Filebase\Database([
    'dir' => 'database/'
]);

foreach($finalDataToImport as $index => $trajet)
{
	$item = $database->get($index);
	$item->ville = $trajet->name;
	$item->discord_username = $trajet->discord_username;
	if(isset($trajet->date_depart)){ $item->date_depart = $trajet->date_depart; }
	else{ $item->date_depart = NULL; }
	if(isset($trajet->date_retour)){ $item->date_retour = $trajet->date_retour; }
	else{ $item->date_retour = NULL; }
	$item->latitude = $trajet->latitude;
	$item->longitude = $trajet->longitude;
	$item->save();
}

?>
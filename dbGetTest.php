<?php

require __DIR__ . '/vendor/autoload.php';

$db = new \Filebase\Database([
    'dir' => 'database/'
]);

$carpools = $db->where('discord_username','NOT','NULL')->results();

var_dump($carpools);
<?php session_start();
setlocale(LC_ALL, 'fr_FR.utf8','fra');
try {
	// if(isset($_GET['action']))
	// {
		require('router/router.php');
	// }
	// else
	// {
	//	require('router/router.php');
	// }
}

catch(Exception $e) {
    // Si une erreur se produit, on arrive ici
    echo "Une erreur est survenue.<br>Détails : $e";
}

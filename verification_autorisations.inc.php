<?php

//**********************
// Copyleft Marc Leygnac 
//**********************

// abréviations statuts
$t_statuts=array('A'=>'administrateur', 'P'=>'professeur', 'C'=>'cpe', 'S'=>'scolarite', 'sec'=>'secours', 'E'=>'eleve', 'R'=>'responsable', 'autre'=>'autre');

// vérification des autorisations (sans faire appel à Propel)
//
// les données
$script_courant=substr(strrchr($_SERVER['PHP_SELF'], '/'),1);
$plugin_xml = simplexml_load_file('plugin.xml');
// on récupère les autorisations définies dans plugin.xml
$t_autorisations=array();
foreach($plugin_xml->administration->fichier->nomfichier as $fichier)
	{
	if ($fichier==$script_courant)
		$t_autorisations=explode("-",$fichier->attributes()->autorisation);
	}
// on convertit les abréviations en satuts
$tab_secure=array();
foreach($t_autorisations as $statut)
	$tab_secure[]=$t_statuts[$statut];
// on vérifie si le satut de l'utilisateur est parmis les statuts autorisés
if (!in_array($_SESSION['statut'],$tab_secure))
	{
    header("Location: ../../logout.php?auto=1");
    die();
	}	

?>
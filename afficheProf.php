<?php

/*
*
* Copyright 2014 Régis Bouguin
*
* This file is part of GEPI.
*
* GEPI is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
*
* GEPI is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with GEPI; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// chercher dans `plugin_archAPB_profs` le numero de prof à partir de son login 
// puis chercher s'il a des notes dans `plugin_archAPB_notes`
// puis chercher les classes à partir des INE 
// au besoin limiter les élèves aux classes choisies
$annee = NULL;
$_annee = getSettingValue("gepiYear")+1;
echo $_annee;
$annee = apb_annee($_annee);


$classesProf = chercheClassesProf($_SESSION['login'], $annee);


?>
	
<?php echo $_SESSION['login']."<br />"; ?>
<?php if($classesProf->num_rows) {
	 while ($classeProf = $classesProf->fetch_object()){
		 //echo $classeProf->id." → ".$classeProf->annee." → ".$classeProf->nom_court."<br />";
		 echo $classeProf->id_gepi." → ".$classeProf->annee."<br />";
		 //echo $classeProf->nom_complet."<br />";
	 }
}








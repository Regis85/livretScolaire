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

//**************************************************
////********* tester la présence du plugin *********
//**************************************************
$APBinstalle = APBinstalle();

$competences = extraitCompetences();
$classes = extraitClasses($anneeSolaire);
//$correspondances = extraitCorrespondances($anneeSolaire);

if (!$APBinstalle || 0 == $APBinstalle->num_rows ) {
?>

<p class="center rouge grand bold" >
	Vous devez avoir installé et ouvert le plugin APB avant d'utiliser ce plugin
</p>
	<?php
} else {
?>	

<p class="center rouge grand bold" >
	Vous devez avoir effectué les extractions pour APB avant d'utiliser ce plugin
</p>
	<?php
}
?>
<fieldset>
	<legend>Télécharger les compétences</legend>
	Chaque ligne du fichier doit contenir 2 colonnes séparées par un point virgule : CODE_COMPETENCE ; LIBELLE_COMPETENCE
	<br />
	La première ligne n'est pas traitée
	<br /><br />
	<form method="post" action="index.php" id="form_LSL" enctype="multipart/form-data">
	<?php // <form action="upload.php" method="post" enctype="multipart/form-data"> ?>
		<p>
			<?php if (function_exists("add_token_field")) echo add_token_field(); ?>
			<input type="file" name="fileToUpload" id="fileToUpload">
			<input type="submit" name="uploadFichier" id="uploadFichier" value="télécharger">	
		</p>
	<?php if ($competences->num_rows) { ?>
		<p title="Cliquez pour afficher/masquer le tableau" 
		   onclick="bascule('tableCompetences');"
		   class="center grand bold"
			   style="cursor:pointer" >
			Tableau des compétences
		</p>
		<table class="boireaus sortable resizable"
			   id="tableCompetences"
			   style="display:none;">
			<tr>
				<th>
					code
				</th>
				<th>
					Compétence
				</th>
			</tr>
	<?php 
		$cpt =1; 
		while ($obj = $competences->fetch_object()) {
	?>
			
			<tr class="lig<?php echo $cpt; ?>">
				<td>
					<?php echo $obj->code_competences; ?>
				</td>
				<td>
					<?php echo $obj->texte_competences; ?>
				</td>
			</tr>
	<?php
		$cpt*=-1;
		}
	?>
		</table>
	<?php } ?>
		
	</form>	
</fieldset>

<fieldset>
	<legend>Liste des classes <?php echo $anneeSolaire; ?>/<?php echo $anneeSolaire+1; ?></legend>
	<?php if ($classes->num_rows) { ?>
	<p title="Cliquez pour afficher/masquer le tableau"
		   onclick="bascule('tableClasses');"
		   class="center grand bold"
			   style="cursor:pointer" 
	   >
		il y a <?php echo $classes->num_rows ?> classes pour <?php echo $anneeSolaire; ?>/<?php echo $anneeSolaire+1; ?>
	</p>
	
	<form method="post" action="index.php" id="form_LSL" enctype="multipart/form-data">	
		<table class="boireaus sortable resizable"
			   id="tableClasses"
			   >
			<tr>
				<th>
					nom_court
				</th>
				<th>
					nom_complet
				</th>
				<th>
					niveau
				</th>
				<th>
					sélectionner
						<img src='../../images/enabled.png' 
							 class='icone15' 
							 title='Cocher toutes les classes'
							 style="cursor:pointer"
							 onclick="CocheColonneSelect(<?php echo $classes->num_rows ?>)" />
					/
						<img src='../../images/disabled.png' 
							 class='icone15' 
							 title='Décocher toutes les classes'
							 style="cursor:pointer"
							 onclick="DecocheColonneSelect(<?php echo $classes->num_rows ?>)"  />
				</th>
			</tr>
	<?php 
		$cpt =1;
		$id =1;
		while ($obj = $classes->fetch_object()) {
	?>
			<tr class="lig<?php echo $cpt; ?>">
			
				<td>
					<?php echo $obj->nom_court; ?>
				</td>
				<td>
					<?php echo $obj->nom_complet; ?>
				</td>
				<td>
					<?php echo $obj->niveau; ?>
				</td>
				<td>
					<input type="checkbox" 
						   name="classes[<?php echo $obj->id; ?>]" 
						   id="classe_<?php echo $id; ?>" />
					<?php echo $obj->id; ?>
				</td>
			
			</tr>
			
	<?php
		$cpt*=-1;
		$id++;
		}
	?>
			
		</table>
	
	<?php } ?>
	

		<p>
			<?php if (function_exists("add_token_field")) echo add_token_field(); ?>
			Créer le fichier
			<input type="submit" name="creeFichier" id="creeFichier" />
		</p>
	</form>
</fieldset>
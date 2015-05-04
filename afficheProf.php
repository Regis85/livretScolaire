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


$anneeChoisie = isset($_POST['choixAnnee']) ? $_POST['choixAnnee'] : (isset($_SESSION['choixAnnee']) ? $_SESSION['choixAnnee'] : NULL );
$classesChoisies = isset($_POST['classes']) ? $_POST['classes'] : (isset($_SESSION['classes']) ? $_SESSION['classes'] : NULL) ;
   
$anneeLSL = lsl_annee(getSettingValue("gepiYear"));

$anneeAPB = $anneeLSL+1;

if(!$anneeChoisie) {
	$anneeChoisie = $anneeAPB;
}
$anneeLSLChoisie = $anneeChoisie -1;

$_SESSION['choixAnnee'] = $anneeChoisie;
$_SESSION['classes'] = $classesChoisies;
// echo $anneeLSL."/".$anneeAPB." année choisie : ".($anneeChoisie-1)."/".$anneeChoisie;
// echo $_SESSION['LSL_choixAnnee']."-".$_SESSION['classes'];

//===== Recherche des classes =====
$classesProf = chercheClassesProf($_SESSION['login'], $anneeChoisie);

//===== Recherche des élèves =====
if ($classesChoisies) {
	$elevesChoisis = chercheElevesProf($classesChoisies, $_SESSION['login'], $anneeChoisie);
}

//===== Recherche des notes =====
if(isset($_POST['appEleves'])) {
	foreach ($_POST['app'] as $key => $value) {
		if ($value) {
			$clefs = explode ("_",$key) ;
			setAppreciationProf($clefs[0], $clefs[1], $clefs[2], $value, $_SESSION['login']);
		}
		
	}
	
}


?>
<fieldset>
	<legend>Liste des classes <?php echo $anneeLSLChoisie; ?>/<?php echo $anneeChoisie; ?></legend>
	<form method="post" action="index.php" id="form_LSL" enctype="multipart/form-data">	
		
		<p>
			<select name="choixAnnee" id="choixAnnee" onchange="submit()">
				<?php 
				$cpt = 0;
				for($cpt = 0;$cpt<10;$cpt++) {
				?>
				<option value="<?php echo $anneeAPB-$cpt; ?>"
						<?php if ($anneeAPB-$cpt == $anneeChoisie) echo " selected = 'selected'" ; ?> >
					<?php echo $anneeAPB-$cpt; ?>
				</option>
				
				<?php }	?>
			</select>
		</p>

<?php if($classesProf->num_rows) {?>
		<table class="boireaus sortable resizable"
					   id="tableClasses">
			<tr>
				<th>
					id
				</th>
				<th>
					nom court
				</th>
				<th>
					Sélectionner
					<img src='../../images/enabled.png' 
						 class='icone15' 
						 title='Cocher toutes les classes'
						 style="cursor:pointer"
						 onclick="CocheColonneSelect(<?php echo $classesProf->num_rows ?>)" />
				/
					<img src='../../images/disabled.png' 
						 class='icone15' 
						 title='Décocher toutes les classes'
						 style="cursor:pointer"
						 onclick="DecocheColonneSelect(<?php echo $classesProf->num_rows ?>)"  />
				</th>
			</tr>
			<?php
				$cpt =1;
				$id =1;
				while ($classeProf = $classesProf->fetch_object()){ ?>
			<tr class="lig<?php echo $cpt; ?>">
				<td>
					<?php  echo $classeProf->id; ?>
				</td>
				<td>
					<?php  echo $classeProf->nom_court; ?>
				</td>
				<td>
					<input type="checkbox" 
						   name="classes[<?php echo $classeProf->id; ?>]" 
						   id="classe_<?php echo $id; ?>"
						   <?php if (isset($_SESSION['classes'][$classeProf->id])) echo "checked = 'checked'"  ?>
						   
						   />
					<?php echo $classeProf->id; ?>

				</td>
			</tr>


			 <?php
				$cpt*=-1;
				$id++;
				} ?>
		</table>

<?php } ?>


		<p>
			<?php if (function_exists("add_token_field")) echo add_token_field(); ?>
			<input type="submit" name="choixClasses" id="choixClasses" value="Sélectionner" />
		</p>
	</form>
</fieldset>


<?php if($classesChoisies) {?>

	<?php  if($elevesChoisis) {?>
<fieldset>
	<legend>Élèves</legend>
<form method="post" action="index.php" id="form_elv" enctype="multipart/form-data">	
<table class="boireaus sortable resizable"
	   id="tableEleves"
	   >
	<tr>
		<th>
			id classe
		</th>
		<th>
			Nom Prénom
		</th>
		<th>
			Notes
		</th>
		<th>
			Appréciation (prof)
		</th>
	</tr>
		<?php $cpt=1;?>
		<?php while ($elvChoisi = $elevesChoisis->fetch_object()){?>
	<tr class="lig<?php echo $cpt; ?>">
		<td>
			<?php echo $elvChoisi->id_classe; ?>
		</td>
		<td style="text-align: left;">
			<?php echo $elvChoisi->nom; ?> <?php echo $elvChoisi->prenom; ?> 
		</td>
		<td style="text-align: left;">
		<?php
			$notes = chercheNotes($elvChoisi->ine,$_SESSION['login'],$anneeChoisie);
			if($notes) {?>
			<?php 
				$exMatiere = "";
				$moyenne = 0;
				$nbNotes = 0; 
			while ($noteActive = $notes->fetch_object()) {
				if ($exMatiere != $noteActive->code_service) {
					//on change de matière
					
					if (0 != $moyenne) {
					//on change vraiment de matière
						// On ferme la case
						// On entre l'appréciation
						// On crée une autre ligne
						echo " moyenne = ".number_format($moyenne/$nbNotes, 2, ',', ' ')." ";
						?>
		</td>
		<td style="text-align: left;">
		<?php if (lsl_getDroit('droitAppreciation')) { ?>
			<textarea rows="4" cols="70"  
					  name="app[<?php echo $elvChoisi->ine; ?>_<?php echo $exMatiere ?>_<?php echo $anneeChoisie; ?>]"
					  id="app_<?php echo $elvChoisi->ine; ?>_<?php echo $exMatiere ?>_<?php echo $anneeChoisie; ?>"
					  maxlength="300"
					  style="text-align: left;"
					  /><?php echo getAppreciationProf($elvChoisi->ine, $exMatiere, $anneeChoisie); ?></textarea>
			<?php $prof = getUtilisateur(getLoginProfAppreciation($elvChoisi->ine, $exMatiere, $anneeChoisie)); ?>
			<?php if ($prof) {echo " (".$prof->nom." ".$prof->prenom.")";} ?>
		<?php } ?>
		</td>
	</tr>
	<tr class="lig<?php echo $cpt; ?>">
		<td><?php echo $elvChoisi->id_classe; ?></td>
		<td style="text-align: left;"><?php echo $elvChoisi->nom; ?> <?php echo $elvChoisi->prenom; ?></td>
		<td style="text-align: left;">
		<?php	
						
						//echo $moyenne;
						$exMatiere = "";
						$moyenne = 0;
						$nbNotes = 0;
					}
					
					echo " ".getMatiere($noteActive->code_service,$anneeChoisie)." "; 
				}
				$nbNotes++;
				$moyenne += floatval($noteActive->moyenne);
				echo "-".$noteActive->moyenne."-";
				$exMatiere = $noteActive->code_service;
			}
			echo " moyenne = ".number_format($moyenne/$nbNotes, 2, ',', ' ')." "; ?>
		<?php	} ?>
		</td>
		<td style="text-align: left;">
		<?php if (lsl_getDroit('droitAppreciation')) { ?>
			<textarea rows="4" cols="70"  
					  name="app[<?php echo $elvChoisi->ine; ?>_<?php echo $exMatiere ?>_<?php echo $anneeChoisie; ?>]"
					  id="app_<?php echo $elvChoisi->ine; ?>_<?php echo $exMatiere ?>_<?php echo $anneeChoisie; ?>"
					  maxlength="300"
					  style="text-align: left;"
					  />
						  <?php echo getAppreciationProf($elvChoisi->ine, $exMatiere, $anneeChoisie); ?>
			</textarea>
			<?php $prof = getUtilisateur(getLoginProfAppreciation($elvChoisi->ine, $exMatiere, $anneeChoisie)); ?>
			<?php if ($prof) {echo " (".$prof->nom." ".$prof->prenom.")";} ?>
		<?php	} ?>
		</td>
	</tr>
		<?php	$cpt *= -1; ?>
		<?php } ?>
	
</table>
	<?php if (function_exists("add_token_field")) echo add_token_field(); ?>
	<input type="submit" name="appEleves" id="appEleves" value="Enregistrer" />
	</form>
</fieldset>

	<?php } ?>

<?php }



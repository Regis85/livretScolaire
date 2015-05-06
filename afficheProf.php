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

$annee = NULL;

if (isset($_GET['classe'])) {unset($_SESSION['classes']);}

// on passe par un tableau, reste 
$classesChoisies = isset($_GET['classe']) ? $_GET['classe'] : (isset($_SESSION['classes']) ? $_SESSION['classes'] : NULL) ;

$anneeLSL = lsl_annee(getSettingValue("gepiYear"));
$anneeAPB = $anneeAPB = $anneeLSL+1;

$_SESSION['classes'] = $classesChoisies;

//===== Recherche des classes =====
$classesProf = chercheClassesProf($_SESSION['login'], $anneeAPB);

//===== Recherche des élèves =====
if ($classesChoisies) {
	$elevesChoisis = chercheElevesProf($classesChoisies, $_SESSION['login'], $anneeAPB);
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
	<legend>Liste des classes <?php echo $anneeLSL; ?>/<?php echo $anneeAPB; ?></legend>
<?php
if($classesProf->num_rows) {	
	while ($classeProf = $classesProf->fetch_object()){ 
		if (lsl_get_ouvert_prof($classeProf->id)) { ?>
	<p>
		<a href="?classe[<?php echo $classeProf->id; ?>]=on<?php echo add_token_in_url(); ?>">
			<?php echo $classeProf->nom_complet; ?>
		</a>		
	</p>
<?php		}
	}
}
?>	
</fieldset>

<?php if($classesChoisies) {?>

	<?php  if($elevesChoisis) {?>
<fieldset class="margin-top:1em">
	<legend>Élèves</legend>
	<form method="post" action="index.php" id="form_elv" enctype="multipart/form-data">	
		<table class="boireaus sortable resizable"
			   id="tableEleves"
			   >
			<tr>
				<th>
					Classe
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
					<?php echo cherche_classe_APB($elvChoisi->id_classe, $anneeAPB)->nom_court; ?>
				</td>
				<td style="text-align: left;">
					<?php echo $elvChoisi->nom; ?> <?php echo $elvChoisi->prenom; ?> 
				</td>
				<td style="text-align: left;">
				<?php
					$notes = chercheNotes($elvChoisi->ine,$_SESSION['login'],$anneeAPB);
					if($notes) {?>
					<?php 
						$exMatiere = "";
						$moyenne = 0;
						$nbNotes = 0; 
					while ($noteActive = $notes->fetch_object()) {
						if ($exMatiere != $noteActive->code_service) {
							//on change de matière

							if (0 != $moyenne) {
							//on change vraiment de matière, on ferme la case, on entre l'appréciation, on crée une autre ligne
								echo " moyenne = ".number_format($moyenne/$nbNotes, 2, ',', ' ')." ";
								?>
				</td>
				<td style="text-align: left;">
				<?php if (lsl_getDroit('droitAppreciation')) { ?>
					<textarea rows="4" cols="70"  
							  name="app[<?php echo $elvChoisi->ine; ?>_<?php echo $exMatiere ?>_<?php echo $anneeAPB; ?>]"
							  id="app_<?php echo $elvChoisi->ine; ?>_<?php echo $exMatiere ?>_<?php echo $anneeAPB; ?>"
							  maxlength="300"
							  style="text-align: left;"
							  /><?php echo getAppreciationProf($elvChoisi->ine, $exMatiere, $anneeAPB); ?></textarea>
					<?php $prof = getUtilisateur(getLoginProfAppreciation($elvChoisi->ine, $exMatiere, $anneeAPB)); ?>
					<?php if ($prof) {echo " (".$prof->nom." ".$prof->prenom.")";} ?>
				<?php } ?>
				</td>
			</tr>
			<tr class="lig<?php echo $cpt; ?>">
				<td><?php echo cherche_classe_APB($elvChoisi->id_classe, $anneeAPB)->nom_court; ?></td>
				<td style="text-align: left;"><?php echo $elvChoisi->nom; ?> <?php echo $elvChoisi->prenom; ?></td>
				<td style="text-align: left;">
				<?php	

								//echo $moyenne;
								$exMatiere = "";
								$moyenne = 0;
								$nbNotes = 0;
							}

							echo " ".getMatiere($noteActive->code_service,$anneeAPB)." "; 
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
							  name="app[<?php echo $elvChoisi->ine; ?>_<?php echo $exMatiere ?>_<?php echo $anneeAPB; ?>]"
							  id="app_<?php echo $elvChoisi->ine; ?>_<?php echo $exMatiere ?>_<?php echo $anneeAPB; ?>"
							  maxlength="300"
							  style="text-align: left;"
							  />
								  <?php echo getAppreciationProf($elvChoisi->ine, $exMatiere, $anneeAPB); ?>
					</textarea>
					<?php $prof = getUtilisateur(getLoginProfAppreciation($elvChoisi->ine, $exMatiere, $anneeAPB)); ?>
					<?php if ($prof) {echo " (".$prof->nom." ".$prof->prenom.")";} ?>
				<?php	} ?>
				</td>
			</tr>
				<?php	$cpt *= -1; ?>
				<?php } ?>

		</table>
		<p class="center">
			<?php if (function_exists("add_token_field")) echo add_token_field(); ?>
			<input style="margin-top:1em;" type="submit" name="appEleves" id="appEleves" value="Enregistrer" />
		</p>	
	</form>
</fieldset>

	<?php } ?>

<?php }



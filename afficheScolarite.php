<?php
$classes = cherche_classe_scolarite($anneeAPB);
//print_r($classes);

$classeChoisie = isset($_GET['classe']) ? $_GET['classe'] : (isset($_SESSION["LSL_classe_scolarite"]) ? $_SESSION["LSL_classe_scolarite"] : NULL) ;
//print_r($classeChoisie);
$enregistre = isset($_POST['enregistre']) ? TRUE : NULL ;

if ($enregistre && $_POST['avis']) {
	foreach ($_POST['avis'] as $key=>$valeur) {
		LSL_enregistre_avis_BAC($key, $anneeLSL, $valeur) ;
	}
}

if ($classeChoisie) { 
	$elevesChoisis = chercheElevesClasse($classeChoisie, $anneeAPB) ;
}
?>

<fieldset>
	<legend>Avis examen</legend>
<?php 
$cpt = 0; ?>
	<fieldset>
		<legend>Classes</legend>
		<div class="colonne" style="border: none;" >
<?php 
while ($classe = $classes->fetch_object()) { ?>
		<p>
			<a href="?classe=<?php echo $classe->classe; ?><?php echo add_token_in_url(); ?>">
				<?php echo $classe->nom_complet; ?>
			</a>
		</p>
<?php 
	$cpt++; 
	if (!($cpt % ($classes->num_rows /3))) { 
$cpt = 0;
?>
	</div>
	<div class="colonne" style="border: none;" >
<?php 
	}
} 
?>
	</div>
	
	</fieldset>
<?php 
if ($classeChoisie) {
?>
	<form method="post" action="index.php" id="form_LSL_classe" enctype="multipart/form-data">
		
	<p style="text-align: center; margin-top: 1em;">
<?php if (lsl_getDroit('avisBacScolarite')) { ?>
		<?php if (function_exists("add_token_field")) {echo add_token_field();} ?>
		<button name="enregistre" value="y" >
			Enregistrer
		</button>
<?php } ?>
	</p>
	
	<table class="boireaus">
		<caption>
			<?php echo cherche_classe_APB($classeChoisie, $anneeAPB)->nom_complet ; ?>
			<?php echo cherche_classe_APB($classeChoisie, $anneeAPB)->niveau ; ?>
			<?php $_SESSION["LSL_classe_scolarite"] = cherche_classe_APB($classeChoisie, $anneeAPB)->id; ?>
		</caption>
		<tr>
			<th>
				Nom Prénom
			</th>
			<th>
				Avis examen
			</th>
			<th>
				Avis annuel
			</th>
		</tr>
<?php 
$cpt=1;
	while ($eleve = $elevesChoisis->fetch_object()) { ?>
		<tr class="lig<?php echo $cpt; ?>">
			<td style="text-align: left;">
				<?php echo $eleve->nom; ?> <?php echo $eleve->prenom; ?>
			</td>
			<td>

<?php if ('terminale' == cherche_classe_APB($classeChoisie, $anneeAPB)->niveau) { 
	if (lsl_getDroit('avisBacScolarite')) { ?>
				
				<select name="avis[<?php echo $eleve->ine; ?>]">
					<option value='' >
						Choisissez un avis...
					</option>
					<option value="T" 
							<?php if (is_object(LSL_get_avis_BAC($eleve->ine, $anneeLSL)) && ('T' === LSL_get_avis_BAC($eleve->ine, $anneeLSL)->avis)) {  ?> 
							selected="selected"
							<?php }  ?> >
						Très favorable
					</option>
					<option value="F"
							<?php if (is_object(LSL_get_avis_BAC($eleve->ine, $anneeLSL)) && ('F' == LSL_get_avis_BAC($eleve->ine, $anneeLSL)->avis)) {  ?> 
							selected="selected"
							<?php }  ?> >
						Favorable
					</option>
<?php 
if ('p' == lsl_get_type_lycee($classeChoisie)) { ?>
					<option value="A"
							<?php if (is_object(LSL_get_avis_BAC($eleve->ine, $anneeLSL)) && ('A' === LSL_get_avis_BAC($eleve->ine, $anneeLSL)->avis))  {  ?> 
							selected="selected"
							<?php } ?> >
						Assez favorable
					</option>	
<?php } ?>
					<option value="D"
							<?php if (is_object(LSL_get_avis_BAC($eleve->ine, $anneeLSL)) && ('D' === LSL_get_avis_BAC($eleve->ine, $anneeLSL)->avis))  {  ?> 
							selected="selected"
							<?php } ?> >
						Doit faire ses preuves
					</option>
				</select>
				
<?php }  elseif (is_object(LSL_get_avis_BAC($eleve->ine, $anneeLSL))) {
	$avisBac =  LSL_get_avis_BAC($eleve->ine, $anneeLSL);
	switch ($avisBac->avis) {
	case "T" :
		echo 'Très favorable';
		break;
	case "F" :
		echo 'Favorable';
		break;
	case "A" :
		echo 'Assez favorable';
		break;
	case "D" :
		echo 'Doit faire ses preuves';
		break;
	default:
		echo '<span style="background:red;color:black;">&nbsp;&nbsp;non saisi&nbsp;&nbsp;</span>';
	} 
} else {
	echo '<span style="background:red;color:black;">&nbsp;&nbsp;non saisi&nbsp;&nbsp;</span>';
}?>	
<?php } ?>
				
			</td>
			<td>				
				<?php /*
				<textarea rows="4" cols="90"  
						  name="app[<?php echo $eleve->ine; ?>_<?php echo $anneeLSL; ?>]"
						  id="app_<?php echo $eleve->ine; ?>_<?php echo $anneeLSL; ?>"
						  maxlength="300"
						  style="text-align: left;"
						 */ ?>
				<?php echo LSL_get_avis_general_annee($eleve->ine, $anneeLSL); ?>
				<?php /*</textarea>	 */ ?>	
			</td>
		</tr>
<?php 
$cpt*=-1;
	}
?>
	</table>
	<p style="text-align: center; margin-top: 1em;">
<?php if (lsl_getDroit('avisBacScolarite')) { ?>
		<button name="enregistre" value="y" >
			Enregistrer
		</button>
<?php } ?>
	</p>
<?php 
}
?>
	</form>
</fieldset>

<fieldset>
	<legend>Vérifications</legend>
	
</fieldset>


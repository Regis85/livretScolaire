<?php
$classes = cherche_classe_scolarite($anneeAPB);
//print_r($classes);

$classeChoisie = isset($_GET['classe']) ? $_GET['classe'] : NULL ;
//print_r($classeChoisie);
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
	<table class="boireaus">
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
			</td>
			<td>
			<td>
			</td>
		</tr>
<?php 
$cpt*=-1;
	}
?>
	</table>
<?php 
}
?>
	
</fieldset>

<fieldset>
	<legend>Vérifications</legend>
	
</fieldset>


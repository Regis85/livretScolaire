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

$formations = extraitFormations($anneeAPB);

if(isset($_SESSION['choixFormation']) && $_SESSION['choixFormation']) {
	$programmes = extraitProgrammes($_SESSION['choixFormation']);
} else {
	$programmes = NULL;
}

//$correspondances = extraitCorrespondances($anneeSolaire);



if (!$APBinstalle || 0 == $APBinstalle->num_rows ) {
?>

<p class="center rouge grand bold" >
	Vous devez avoir installé et ouvert le plugin APB avant d'utiliser ce plugin.
</p>
	<?php
} else {
?>	

<p class="center rouge grand" >
	Les données utilisées sont celles du plugin APB.
	<br />
	Vous devez avoir effectué les extractions du 3<sup>ème</sup> trimestre 
	avant de créer le fichier .xml à importer dans LSL.
</p>

<fieldset>
	<legend>Télécharger les programmes</legend>
	
	<fieldset>
		<legend>Choisissez la formation à afficher</legend>
		<form method="post" action="index.php" id="form_LSL_select_Programme" enctype="multipart/form-data">
			<p style="text-align:center;">
				<?php if (function_exists("add_token_field")) {echo add_token_field(); } ?>
				<br />
				<select name="selectClasse" onchange="submit()">
					<option value="">Choisissez une formation</option>
<?php while ($obj = $formations->fetch_object()) { ?>
					<option value="<?php echo $obj->code_mef; ?>"
<?php if (isset($_SESSION['choixFormation']) && $_SESSION['choixFormation'] == $obj->code_mef) { ?>
							selected="selected"	
<?php } ?>					
							>
						<?php echo $obj->code_mef; ?> - <?php echo $obj->edition; ?>
					</option>
<?php } ?>					
				</select>
				<button name="choixClasse" value="y">
					Choisir
				</button>
			</p>
		</form>
	
	<form method="post" action="index.php" id="form_LSL_select_Programme" enctype="multipart/form-data">
	<table class="boireaus sortable resizable"
		   id="tableProgrammes">
		<tr>
			<th>MEF de rattachement</th>
			<th>Matière</th>
			<th>Modalité</th>
			<th>Note obligatoire</th>
			<th>Appreciation obligatoire</th>
			<th>Commentaire</th>
			<th>Supprimer</th>
		</tr>
	<?php 
		$cpt =1; 
		if ($programmes) {
		while ($programme = $programmes->fetch_object()) {
	?>
		<tr class="lig<?php echo $cpt; ?>">
			<td><?php echo $programme->formation ?></td>
			<td><?php echo $programme->matiere ?></td>
			<td><?php echo $programme->Modalite ?></td>
			<td><?php echo $programme->note ?></td>
			<td><?php echo $programme->appreciation ?></td>
			<td style="text-align:left;"><?php echo $programme->option ?></td>
			<td>
<?php if ($_SESSION['choixFormation'] == $programme->formation) { ?>	
				<input type="checkbox" name="supprime" value="<?php echo $programme->id ?>" />
<?php } ?>
			</td>
		</tr>
	<?php
		$cpt*=-1;
		}
		}
	?>
			   
	</table>	
		<p style="text-align:center; margin-top: .5em;">
			<button name="supprimeAssociation" value="y" >Supprimer les associations cochées</button>
		</p>
	
	</fieldset>
	
 <?php /* ?>
	<fieldset>
	<legend>Importer les programmes</legend>
	<form method="post" action="index.php" id="form_LSL_Programme" enctype="multipart/form-data">	
		<p>
			Pour créer les programmes par MEF, importez le fichier nomenclature.xml ou un fichier avec la même structure.
		</p>
		<p>
			Ce fichier peut-être compressé (ExportXML_Nomenclature.zip)
		</p>
		<p class="rouge">
			Attention dans nomenclature.xml, 
			le caractère obligatoire ou pas des notes et appréciations n'est pas renseigné.
			Vous devez le renseigner manuellement
		</p>
		<p>	
			<?php if (function_exists("add_token_field")) {echo add_token_field(); } ?>
			<input type="file" name="fileToUpload" id="fileToUpload">
			<button name="uploadProgramme" id="uploadFichier" value="y">Télécharger</button>
		</p>
	</form>
	</fieldset>
 <?php /* */ ?>
	<p style="margin:1em; font-size: larger;">
		La plupart des matières qui doivent apparaître dans le livret scolaire sont prédéfinies 
		mais il vous appartient de vérifier que toutes les langues vivantes enseignées dans votre établissement 
		sont bien présentes et au besoin d'ajouter celles qui manquent.
	</p>
	
 <?php if (!$formations->num_rows) {  ?>
	 <p class="center rouge grand bold" >Vous devez initialiser le module avec votre nomenclature.xml ou un fichier équivalent</p>
<?php } else { ?>

<?php if (isset($_SESSION['choixFormation']) && $_SESSION['choixFormation']) { ?>
	<fieldset>
	<legend>Créer/modifier les programmes</legend>
	<form method="post" action="index.php" id="form_LSL_Programme" enctype="multipart/form-data">
		<p>
			Pour créer ou modifier une association pour le MEF : <?php echo $_SESSION['choixFormation']; ?> , 
			saisissez la ci-dessous			
		</p>
		<p>
		<?php if (function_exists("add_token_field")) {echo add_token_field(); } ?>
		MEF : <?php echo $_SESSION['choixFormation']; ?>
		<input type="hidden" name="creerMEF" value="<?php echo $_SESSION['choixFormation']; ?>" />
		Matière :
		<input type="text" style="width:4em;" name="creerMatiere" />
		Modalité :
		<select name="creerModalite">
			<option value="S">Obligatoire (S)</option>
			<option value="F">Facultatif (F)</option>
			<option value="O">Option (O)</option>
		</select>
		
		Note obligatoire dans le livret :
		<select name="creerNote">
			<option value="y">Oui</option>
			<option value="n">Non</option>
		</select>
		Appréciation obligatoire dans le livret 
		<select name="creerAppreciation">
			<option value="y">Oui</option>
			<option value="n">Non</option>
		</select>
		<br />
		Commentaires :
		<input type="text" name="creerOption" />
		<button name="creeModifie" value="y">
			Créer/Modifier une association
		</button>
		</p>
	</form>
	</fieldset>
	
<?php } ?>
</fieldset>

<fieldset <?php if (!lsl_getDroit('droitCompetences')) { ?> 
	style="display : none;"
<?php } ?>	>
	<legend>Télécharger les définitions des compétences</legend>
	Chaque ligne du fichier doit contenir 2 colonnes séparées par un point virgule : CODE_COMPETENCE ; LIBELLE_COMPETENCE
	<br />
	La première ligne n'est pas traitée. Vous pouvez voir les compétences déjà saisies en cliquand sur <span style="cursor:pointer" onclick="bascule('tableCompetences');">Tableau des compétences</span>
	<br /><br />
	<form method="post" action="index.php" id="form_LSL_competences" enctype="multipart/form-data">
	<?php // <form action="upload.php" method="post" enctype="multipart/form-data"> ?>
		<p>
			<?php if (function_exists("add_token_field")) {echo add_token_field(); } ?>
			<input type="file" name="fileToUpload" id="fileToUpload">
			<input type="submit" name="uploadFichier" id="uploadFichier" value="télécharger">	
		</p>
	</form>	
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
			<td id="competences_<?php echo $obj->code_competences; ?>">
				<?php echo $obj->texte_competences; ?>
			</td>
		</tr>
	<?php
		$cpt*=-1;
		}
	?>
	</table>


	<p title="Cliquez pour afficher/masquer le tableau" 
	   onclick="bascule('tableCorrespondances');"
	   class="center grand bold"
		   style="cursor:pointer" >
		Compétences par MEF
	</p>
	<form method="post" action="index.php" id="form_LSL_correspondances" enctype="multipart/form-data" >
		<p class="center">
			<?php if (function_exists("add_token_field")) {echo add_token_field(); } ?>
			Nouvelle association MEF ←→ compétence
		</p>
		<p>
			MEF <input type="text" name="lsl_mef" style="width:7em;" />
			←→ 
			<input type="text" 
				   name="lsl_code" 
				   id="lsl_code_competence" 
				   style="width:4em;" 
				   onblur="afficheCompetences(getElementById('lsl_code_competence').value);"/> 
			Code compétence 
		</p>
		<p>
			Modalité <input type="text" name="lsl_modalite" style="width:2em;"/>
			Matière <input type="text" name="lsl_matiere" style="width:9em;"/>
			Note obligatoire <input type="text" name="lsl_note" style="width:2em;"/>
			Appréciation obligatoire <input type="text" name="lsl_appreciation" style="width:2em;"/>
		</p>
		<p>
			<input type="submit" name="creeCorrespondance" id="creeCorrespondance" value="Créer">				
		</p>
	</form>
		
	<table class="boireaus sortable resizable"
		   id="tableCorrespondances"
		   style="display:none;">
		<tr>
			<th>
				MEF
			</th>
			<th>
				Regroupement
			</th>
			<th>
				Code compétence
			</th>
			<th>
				Modalité
			</th>
			<th>
				Descriptif
			</th>
			<th>
				Matière
			</th>
			<th>
				Note obligatoire
			</th>
			<th>
				Appréciation obligatoire
			</th>
		</tr>
	</table>
		
	<?php } ?>
		
</fieldset>

<fieldset>
	<legend>Liste des classes <?php echo $anneeSolaire; ?>/<?php echo $anneeSolaire+1; ?></legend>
	<?php if ($classes && $classes->num_rows) { ?>
	<p title="Cliquez pour afficher/masquer le tableau"
	   onclick="bascule('tableClasses');"
	   class="center grand bold"
	   style="cursor:pointer" 
	   >
		Il y a <?php echo $classes->num_rows ?> classes pour <?php echo $anneeSolaire; ?>/<?php echo $anneeSolaire+1; ?>.
		
	</p>
		
	<form method="post" action="index.php" id="form_LSL_classe" enctype="multipart/form-data">	
		<p class="center">
			<button type="button" 
					onclick="bascule('tableClasses');" 
					title="Cliquez pour afficher/masquer le tableau" >
				afficher/masquer le tableau
			</button>
		</p>
		<p class="center" style="margin-top:.5em;">
			<button name="ouvertsProfs" 
					id="ouvertsProfs" 
					value="1" 
					title="Enregistrer les classes à ouvrir à la saisie par les enseignants" >
				Ouvrir les classes aux enseignants
			</button>
			&nbsp;
			<button type="submit" 
					name="creeFichier" 
					id="creeFichier" 
					value="1"
					title="Créer le fichier .xml à importer dans LSL">
				Créer le fichier .xml
			</button>
		</p>
	
		<table class="boireaus sortable resizable"
			   id="tableClasses"
			   >
			<tr>
				<th>
					nom court
				</th>
				<th>
					nom complet
				</th>
				<th>
					niveau
				</th>
				<th style="cursor:pointer" 
					title="Sélectionner les classes à ouvrir à la saisie par les enseignants" >
					saisie en profs
						<img src='../../images/enabled.png' 
							 class='icone15' 
							 title='Cocher toutes les classes'
							 style="cursor:pointer"
							 onclick="CocheProfSelect(<?php echo $classes->num_rows ?>)" />
					/
						<img src='../../images/disabled.png' 
							 class='icone15' 
							 title='Décocher toutes les classes'
							 style="cursor:pointer"
							 onclick="DecocheProfSelect(<?php echo $classes->num_rows ?>)"  />
				</th>
				<th title="Sélectionner les classes à extraire dans le fichier .xml pour LSL" 
					style="cursor:pointer" >
					<span onclick="getElementById('creeFichier').validate()">
						à extraire
					</span>
					
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
						   name="classe_prof[<?php echo $obj->id; ?>]" 
						   id="classe_prof_<?php echo $id; ?>" 
						   <?php if (lsl_get_ouvert_prof($obj->id)){ ?>
						   checked="checked"
						   <?php } ?>
						   />
				</td>
				<td>
					<?php if(formationValide($obj->id,$anneeAPB)){ ?>
					<input type="checkbox" 
						   name="classes[<?php echo $obj->id; ?>]" 
						   id="classe_<?php echo $id; ?>"
						   />
						   <?php } ?> 
				</td>
			
			</tr>
			
	<?php
		$cpt*=-1;
		$id++;
		}
	?>
			
		</table>
	
	<?php } else if ($classes) { ?>
		<p class="rouge bold">Aucune classe trouvée, avez-vous bien fait les extractions APB ? </p>
<?php } else  { ?>
		<p class="rouge bold">Erreur lors de l'extraction des classes, le plugin APB est-il bien installé ? </p>
<?php } ?>
		<p class="center" style="margin-top:1em;">			
			<?php if (function_exists("add_token_field")) {echo add_token_field(); } ?>
			<button name="ouvertsProfs" 
					id="ouvertsProfs" 
					value="1" 
					title="Enregistrer les classes à ouvrir à la saisie par les enseignants" >
				Ouvrir les classes aux enseignants
			</button>
			&nbsp;		
			<button type="submit" 
					name="creeFichier" 
					id="creeFichier" 
					value="1"
					title="Créer le fichier .xml à importer dans LSL">
				Créer le fichier .xml
			</button>	
		</p>
	</form>
</fieldset>

	<?php
}
}
?>
<fieldset>
	<legend>Modules ouverts</legend>
	<form method="post" action="index.php" id="form_LSL_ouvert" enctype="multipart/form-data">
		<p>
			<label for="droitAppreciation">
				Saisie des appréciations
			</label>
			<input type="checkbox" 
				   name="droitAppreciation"
				   id="droitAppreciation"
				   <?php if (lsl_getDroit('droitAppreciation')) {echo " checked='checked' ";} ?>
					  />
		</p>
		<p>
			<label for="droitCompetences">
				Saisie des compétences
			</label>
			<input type="checkbox" 
				   name="droitCompetences" 
				   id="droitCompetences"
				   <?php if (lsl_getDroit('droitCompetences')) {echo " checked='checked' ";} ?>
				   disabled="disabled"
					  />
			<span style='color:red'>non encore implémenté...</span>
		</p>
		<p>
			<?php if (function_exists("add_token_field")) {echo add_token_field(); } ?>
			<button name="sauveDroits" id="sauveDroits" value="oui" >
				Enregistrer les droits
			</button>
		</p>
	</form>
</fieldset>
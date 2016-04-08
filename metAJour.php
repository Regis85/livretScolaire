<?php

/*
*
* Copyright 2016 Régis Bouguin
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

$fichierXML = 'xml/lsl-nomenclatures.xml';
if (!file_exists($fichierXML)) {
    ?>
<p class="center grand rouge ">Échec lors de l'ouverture du fichier de mise à jour des nomenclatures → <?php echo $fichierXML ?></p>
    <?php
} else {
    // Mettre à jour la structure de plugin_lsl_formations
    $sql = "ALTER TABLE `plugin_lsl_formations` CHANGE `libelle` `libelle` VARCHAR(250) "
            . "CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'libelle long';";
    //echo $sql.'<br /><br />';
    $resultchargeDB = $mysqli->query($sql);
    
    $xml = simplexml_load_file($fichierXML);
    $nbAnnees= $xml->anneeScolaire->count();
    //echo $nbAnnees.' années<br/>';
    foreach ($xml->anneeScolaire as $anneeScolaire) {
        // Mise à jour de la table plugin_lsl_formations à partir des nœuds mef
        $annee = $anneeScolaire['millesime'];
        //echo $annee.'<br />';
        foreach ($anneeScolaire->mef as $mefs) {
            $codeMef = $mefs['codeMef'];
            $niveau = $mefs['niveau'];
            $codeSerie = $mefs['codeSerie'];
            $serie = $mefs['serie'];
            $specialite = $mefs['specialite'];
            
            $sql = "INSERT INTO `plugin_lsl_formations` (`id`, `MEF`, `edition`, `libelle`, `MEF_rattachement`, `annee`) "
            . "VALUES (NULL, \"$codeMef\", \"$niveau-$codeSerie\", \"$niveau $serie\", \"$codeMef\", \"$annee\")"
            . "ON DUPLICATE KEY UPDATE `edition`= \"$niveau-$codeSerie\", `libelle`= \"$niveau $serie\", `MEF_rattachement`=\"$codeMef\" ";
            //echo $sql.'<br />';
            $resultchargeDB = $mysqli->query($sql);
            
            //  Mise à jour de la table plugin_lsl_programmes à partir des nœuds matiere
            foreach ($mefs->matiere as $matieres) {
                $matiere = $matieres['discipline'];
                $codeBcn = $matieres['codeBcn'];
                $modalite = $matieres['modaliteElection'];
                $note = $matieres['presenceEvaluationPeriodique']=="true" ? "y" : "n";
                $appreciation = "y";
                $sql = "INSERT INTO `plugin_lsl_programmes` (`id`, `formation`, `matiere`, `Modalite`, `note`, `appreciation`, `option`) "
                . "VALUES (NULL, \"$codeMef\", \"$codeBcn\", \"$modalite\", \"$note\", \"$appreciation\", \"$matiere\") "
                . "ON DUPLICATE KEY UPDATE `note`= \"$note\", `appreciation`= \"$appreciation\", `option`= \"$matiere\" ";
                //echo $sql.'<br />';
                $resultchargeDB = $mysqli->query($sql);
            }
            //echo '<br />';
            
        }
        
        
    }

    //print_r($xml->anneeScolaire[1]);
}


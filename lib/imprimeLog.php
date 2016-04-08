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

function nomLog() {
    $dirNom = '../../backup/'.getSettingValue("backup_directory");
    $fichierNom = "log_LSL_".date("d-m-Y").".log";
    $fichierLog = $dirNom."/".$fichierNom;
    return $fichierLog;
}

function ouvreLog() {
    $texte = "\n\n===============================================================================================================";
    $texte .= "\n\n***** ".date("d-m-Y G:i")." *****\n";
    ecrit($texte);
    return ;
}

function ecrit($texte) {
    $monFichierLog = fopen(nomLog(), 'a+');
    fputs($monFichierLog, $texte);
    fclose($monFichierLog);
    return ;
}
function debutExtract() {
    $texte = "----- Extraction des données -----\n";
    ecrit($texte);
    return ;
}




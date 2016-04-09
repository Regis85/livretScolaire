# livretScolaire
plugin Livret Scolaire de Lycée

Ce plugin est destiné à créer, en association avec le plugin_APB, un fichier à importer dans LSL (Livret Scolaire de Lycée dématérialisé)

Pour installer le plugin, récupérer l'archive et la décompresser dans /mod_plugins/livretScolaire

Version 0.1.2
   - Les comptes 'scolarité' peuvent effectuer les exports vers LSL et ouvrir le droit à remplir les appréciations pour les enseignants.
   - Les formations sont recherchées à partir de leur MEF de rattachement

Version 0.1.1
Après avoir effectué les extractions pour APB, vous pouvez (pourrez) générer un fichier .xml importable dans LSL (au 5 avril 2015, le plugin n'a pas encore été validé pour pouvoir être utilisé)
   - Coté enseignants → le plugin permet de saisir les appréciations années si le module est activé par l'administrateur
   - Coté administrateur → le plugin permet de créer le fichier
                         → le plugin affiche les appréciations années manquantes
   - Coté CPE → Rien pour l'instant
   - Coté scolarité → Rien pour l'instant

version 0.2.1
    - En se connectant en administrateur, la base est mise à jour avec le fichier fournit par l'équipe LSL (xml/lsl-nomenclatures)
    - Correction d'une erreur si le nom ou le prénom contenait une apostrophe
    - Les langues sont toutes gérées avec le code 030000, le nom de la langue est trouvé dans APB

version 0.2.2
    - On récupère tous les enseignants pour chaque enseignement

version 0.2.3
    - suppression de l'initialisation des tables dans plugin.xml
    - correction de la recherche des enseignements

    


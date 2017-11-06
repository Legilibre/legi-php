## But de l'application legi-php

Legi-php a pour but de pouvoir faciliter l'accès à la base de données legifrance, contenant l'ensemble des codes de lois français.

Bien que disponible librement et gratuitement ([cliquez ici](ftp://echanges.dila.gouv.fr/LEGI/)), la base legi reste difficilement accessible et manipulable pour plusieurs raisons.

La 1ère raison est le volume des données à traiter. La base LEGI est très imposante. Si nous prenons comme exemple la dernière archive complète (datée du 3 mars 2017), "Freemium_legi_global_20170302-080753.tar.gz", elle pèse 796 Mo, et elle se décompresse dans un répertoire legi d’une taille variant entre 9 et 15 Go selon votre système de fichiers.

Cette  version de la base est composée de 1 624 783 fichiers XML, répartis dans une arborescence absconse de 1 537 949 sous-répertoires.

Une seconde raison est que l'arborescence de l'archive legi est également quasi-impossible à comprendre au 1er abord, sans indication extérieure, sans "guide".

Pour plus d'informations à ce sujet, le fichier "Explications Arborescence Sarde" traite de ce sujet.

---
Legi-php vous permet de pouvoir accéder plus facilement à ces données, via le langage de programmation PHP. Vous pouvez utilisez les scripts et classes conçues pour naviguer et trouver ce que vous désirez.

Mais avant tout legi-php propose de pouvoir insérer les codes de lois dans une base de données SQL.

Ce qui ouvre ensuite bien des portes en termes d'utilisations possibles. 

**A l'heure actuelle, legi-php ne traite que les codes de lois en vigueur.**

## Installation


**Requirements :**

PHP-7.0 minimum.
MySQL-5.3 minimum ou équivalent MariaDB (non testé avec d'autres formats SQL).
Et donc un serveur pour faire marcher PHP et MySQL (Apache par exemple).
Beaucoup d'espace disque. Prévoir un minimum de 15 GB pour l'archive legi globale.
Pour l'espace à prévoir en BDD, cela est proportionnel au nombre de textes de lois que vous désirez inscrire.
Mais il vous faudra plusieurs GBs pour obtenir la base legi complète en format SQL.


Téléchargez l'application.

Créer votre base de données.

Téléchargez l'archive legi (au sein de votre dossier "web" de votre serveur), et décompresser la. De par la taille de l'archive, et suivant votre configuration, cela peut prendre du temps (plusieurs heures).
Un disque dur SSD amèliore grandement les performances pour ce cas de figure.

Configurez le fichier settings.php dans le dossier conf, afin d'indiquez votre base de données et vos identifiants, ainsi que le chemin pour atteindre l'archive legi, et celui pour stocker les archives de mises à jour (updates).
Les chemins renseignés doivent être dans l'espace accessible du serveur.


## Structure de l'application legi-php

L'application est entièrement codée en PHP, et conçue en programmation orientée objet.
 

### Les classes
Les fichiers contenus dans le dossier src constituent le "moteur" de l'application.

Pour résumer de manière (très) succinte la programmation orientée objet, cela consiste avant tout à découper et structurer le code en plusieurs parties (appelées Classes) ayant chacune un rôle spécifique à accomplir.
Chacune de ces parties est contenue dans un fichier qui lui est propre. Elles 

* Navigator : La Classe permettant de définir les chemins permettant de naviguer dans l'archive legi.
* FileReader: La Classe qui (à partir des chemins renseignés) va ouvrir les fichiers XML de l'archive et sélectionner les données de ces fichiers pour les préparer à être insérer dans une base de données.
* DBCreator: La Classe qui va gérer la création des tables dans la base de donnée. (DB signifiant DataBase).
* DBManager: La Classe qui va gérer l'insertion, modification, et suppression des données dans la base de données.
* LegiManager: La Classe qui va faire appel aux autres Classes, afin de jouer le rôle de chef d'orchestre. Son but est surtout de pouvoir regrouper plusieurs étapes en une seule procédure globale, qui va pouvoir insérer, modifier (ou supprimer) l'ensemble d'un code de loi.  
* Update : La Classe qui va gérer les actions ayant trait à la manipulation des archives régulièrement publiés par la DILA . Ces actions sont le téléchargement des archives, la décompression, et la suppression des archives décompressées.


### Les fichiers/pages d'interface utilisateur
Les fichiers présents à la racine du projet.
* index.php : La page avec le formulaire pour choisir un code de loi dans une liste et l'insérer dans la base de donnée
* update.php : La page pour gérer les updates de la base de données. En construction.
* delete.php : La page pour gérer la suppression des codes de lois inscrits dans la base de données.
* cron.php : Conçue pour être appelé dans un crontab, afin d'executer automatiquement et régulièrement les tâches de mises à jour des codes de lois.


### Les fichiers de configuration
Les fichiers présents dans le dossier conf.
* cidTexte_list.php : La liste des codes de lois, sous forme de tableau (array). Elle permet leurs sélections dans les fichiers/pages d'interface utilisateur.
* cidTexte_list.md : La même chose mais dans un format markdown, et donc destiné à être lisible pour les humains plutôt que pour un traitement machine.
* settings.php : Les informations de paramétrages de l'application. Notamment l'accès à la base de données.


### Les fichiers d'exemples
Il s'agit de plusieurs fichiers XML issues de la base legi globale. 
Leurs buts ici est simplement d'avoir des fichiers XML Legi facilement et accessible, sans avoir à naviguer sur les 20 niveaux d'arborescences. 
Si vous désirez comprendre la structure des fichiers composant les codes de lois, avec leurs articles et les sections, n'hésitez pas à y jeter un coup d'oeil.


## Structure de la base de données
Il existe deux tables centrales, communes à tous les codes de lois.
* La table code_versions liste les informations (ou meta-données) sur les codes de lois eux mêmes.
* La table archives_diagnostics_updates liste les diagnostics effectués. Il s'agit de comparatifs entre de nouvelles archives téléchargées, et les données inscrites dans la base de données.


## Licence
A définir la licence convenant le mieux. Sans doute OpenGL.
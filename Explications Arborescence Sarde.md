# Explications sur l'arborescence LEGIFRANCE -SARDE 

Guide officiel : [Cliquez ici](https://www.data.gouv.fr/fr/datasets/sarde-1/).

Pour mieux le comprendre, vous pouvez ouvrir un fichier xml de l'arborescence, et dans les explications données par le guide officiel, remplacez "SARDE" par "LEGI" et "OBJT" par "TEXT" ou "ARTI".


## Structure d'un fichier Article SARDE XML
Dans un fichier XML venant de la base de données SARDE, gérant Légifrance, vous trouverez la structure suivante (sous forme de balises xml).
* ### META : Container Général.
  * #### META_COMMUN : Répértoire Parent ?
    * **ID** : Identifiant unique de l’élément.
    * **ANCIEN_ID** : Champ vide pour SARDE, mais utilisé pour d’autres fonds comme JURI, CETA, JORF, LEGI,.
    * **ORIGINE** : prend toujours la valeur de  l'élément global dont il est issue. Génèralement « LEGI » pour les codes et les articles de lois. Et << JORF >> pour le Journal officiel.  ??
    * **URL** : Emplacement du fichier à partir du dossier global.
    * **NATURE** : valeurs possibles strictement défini dans le type EnumNature. Soit Article, soit Sections, soit Arrêté ???
    * **I_LIBELLE** : contient la lettre qui indique dans quelle liste est rangée la donnée pour la recherche via,
    
  * #### META_SPEC : Répertoire enfant ? En dessous soit Article ou Section.
    * ##### META_ARTICLE : Répertoire sous enfant ? .
      * **NUM** : Numéro de l'article de loi (exemple L225-57)
      * **ETAT** : VIGUEUR || ABROGE
      * **DATE_DEBUT** : Date d'entrée en vigueur de l'article (ex: 2000-09-21)
      * **DATE_FIN** : Date de fin d'application de l'article (exemple: 2999-01-01). Oui pour les articles qui n'ont pas vraiment de date de fin, il n'y a pas de valeur vide mais une date pour l'an 2999.
      * **TYPE** : AUTONOME ??????.

* ### CONTEXTE
  * **TEXTE** : Données sur le texte de loi (ou code de loi) auquel est rattaché cet article (ou section ??).
  * **TITRE_TM** : Sections auquels sont rattachés cet article. De la plus globlale (ex : Partie législative ) à la plus petite (ex: Partie législative => Livre II => TITRE II => Chapitre V => Section 2 => **Sous-section 2** )

* ### VERSIONS
   * **LIEN_ART** : Des infos sur l'article, génèralement inclus dans les blocs META. date debut et fin, etat, id, num, origine.
* ### NOTA
* ### BLOC_TEXTUEL 
  * **CONTENU** : Le texte de l'article de loi
* ### LIENS
  * **LIEN** : Lien n°1 vers d'autres textes ou articles de lois.
  * **LIEN** : Lien n°2 vers d'autres textes ou articles de lois.
  


*****

## Structure de l'archive LEGI

Un élément enfant < BLOC_SARDE > dont l’ID est SARDBLOC000030469109 sera stocké dans :
global/sarde/bloc/SARD/BLOC/00/00/30/46/91/SARDBLOC000030469109.xml


Un élément père <Sarde> dont l’ID est SARDOBJT000030435989 sera stocké dans : 
global/sarde/element/SARD/OBJT/00/00/30/43/59/SARDOBJT000030435989.xml


**Exemple :**
LEGIARTI000006224130
global/sarde/element/LEGI/ARTI/00/00/06/22/41/30/LEGIARTI000006224130.xml

 
 | Niveau | Pattern | Description | Exemple |
 | --- | --- | --- | --- |
 | 1 | FFFF | Identifiant du Fonds sur 4 caractères | SARD - LEGI | 
 | 2 | TTTT | Type de document sur 4 caractères | BLOC ou OBJT - ARTI OU TEXTE | 
 | 3 | xx | Séquence Base | 00 | 
 | 4 | xx | Séquence Base | 00 | 
 | 5 | xx | Séquence Base | 07 |
 | 6 | xx | Séquence Base | 43 | 
 | 7 | xx | Séquence Base | 47 | 
 | 8 |    | Fichier XML   | SARDOBJT000007434712.xml |



 
Ce design d'architecture vaut autant pour les articles de loi que pour les codes de loi qui les contiennent.

Car pour les codes de lois et leurs articles, il existe en fait deux niveaux d'architecture.
Le 1er est celui menant au répertoire du code de loi.
Le second est celui à l'intérieur du code de loi, le chemin vers les articles et les sections.


Prenons comme exemple le code de loi **"Code des instruments monétaires et des médailles"**. Son identifiant est **"LEGITEXT000006070666"**.
Il a l'avantage d'être très peu volumineux.
Vous pouvez le consulter [ici](https://www.legifrance.gouv.fr/affichCode.do?cidTexte=LEGITEXT000006070666&dateTexte=20171008).
 
 
 Le chemin pour accéder à ce code de loi est le suivant :
 /legi/global/code_et_TNC_en_vigueur/code_en_vigueur/LEGI/TEXT/00/00/06/07/06/LEGITEXT000006070666
 
 A ce point là trois sous dossiers s'offrent à nous.
  - article : contient les articles du code de lois
  - section_ta : contient les section du code de loi
  - texte : contient deux fichiers avec des informations sur le code de loi lui meme.
  
 Mettons que je souhaite accéder à la section **LEGISCTA000006135575** ([son lien](https://www.legifrance.gouv.fr/affichCode.do;jsessionid=15D80635C0F700AA209E9CF97E945D17.tplgfr38s_2?idSectionTA=LEGISCTA000006135575&cidTexte=LEGITEXT000006070666&dateTexte=20171008))
 
 
  Le chemin pour accéder à cette section est le suivant :
 /LEGITEXT000006070666/section_ta/LEGI/SCTA/00/00/06/13/55/LEGISCTA000006135575.xml
 
 
  Mettons que je souhaite accéder à l'article **LEGIARTI000006398192** ([son lien](https://www.legifrance.gouv.fr/affichCodeArticle.do;jsessionid=15D80635C0F700AA209E9CF97E945D17.tplgfr38s_2?idArticle=LEGIARTI000006398192&cidTexte=LEGITEXT000006070666&dateTexte=20171008))
  
  
   Le chemin pour accéder à cet article de loi est le suivant :
  /LEGITEXT000006070666/article/LEGI/ARTI/00/00/06/39/81/LEGIARTI000006398192.xml
 
 
 Oui vous n'êtes pas en train de rêver, pour atteindre un article de loi, il faut naviguer sur plus de **20 niveaux d'arborescence**.
 
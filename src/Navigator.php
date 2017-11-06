<?php

//include '../conf/settings.php';

class Navigator {

  public static $BASE_LEGI_GLOBAL;
  public static $BASE_LEGI_UPDATES;
  public static $CODES_PATH;

  protected $cID;
  protected $path_code;
  protected $full_path_code;
  protected $archive_path;
  protected $archive_path_codes;
  protected $codes_collection;

  /**
   * Constructor
   */
  public function __construct() {

    global $config;
    self::$BASE_LEGI_GLOBAL = $config['paths']['legi_global_archive'];
    self::$BASE_LEGI_UPDATES = $config['paths']['legi_download_archives'];
    self::$CODES_PATH = $config['paths']['codes_path'];

  }

  /**
   * Construit le chemin absolu pour atteindre l'entrée de l'archive de type update.
   */
  public function setPathToUpdateArchive($archive_name) {
    $this->archive_path = self::$BASE_LEGI_UPDATES . '/temp/' . $archive_name;
    $this->setPathToArchiveCodes();
  }

  /**
   * Construit le chemin absolu pour atteindre l'entrée de l'archive globale.
   */
  public function setPathToGlobalArchive() {
    $this->archive_path = self::$BASE_LEGI_GLOBAL;
    $this->setPathToArchiveCodes();
  }

  public function setPathToArchiveCodes() {
    $this->archive_path_codes = $this->archive_path . self::$CODES_PATH;
}

  /**
   * A partir des informations propres à un code de loi (et notamment son cIDTexte), on construit le chemin pour l'atteindre depuis le début de l'archive LEGI.
   */
  public function setPathToCode($cID){
    if(empty($this->archive_path_codes)) {
      return NULL;
    }
    $pathCode = $this->IDToPath($cID, 6);
    $this->cID = $cID;
    $this->path_code = $pathCode;
    $this->full_path_code = $this->archive_path_codes . $pathCode;
  }


  /**
   * A partir de l'identifiant d'un texte, article ou section, on va écrire une partie chemin (path) pour y accéder.
   * Exemple :
   * LEGITEXT000006070666 nous donne /06/07/06/LEGITEXT000006070666
   * LEGISCTA000006135575 nous donne /06/13/55/LEGISCTA000006135575
   *
   * Il s'agit de la partie génèrique à ces différents types de documents ou textes.
   * Les parties de chemin spécifique à chaque type de document/texte sont traités dans une fonction à part.
   */
  public function IDToPath($texteID, $intervals_number){
    $path = '/'. $texteID;
    for ($i = 0; $i < $intervals_number; $i = $i +2){
      $add = '/'.substr($texteID, -4-$i, 2);
      $path = str_pad($path, strlen($path)+strlen($add), $add, STR_PAD_LEFT);
    }
    return $path;
  }

  /**
   * Fonction qui va creuser une archive, et trouver tous les codes de lois en vigueurs présents dans l'archive.
   * Conçue pour fonctionner à partir d'un archive_path_codes renseigné.
   * Imperatif d'avoir déterminer l'archive_path AVANT d'utiliser cette fonction, à l'aide des fonctions appropriées.
   */
  public function findAllCodes($path) {
    $datas = scandir($path);
    foreach ($datas as $key => $value){
      if($value != "." && $value != ".."){
        $subpath = $path . '/'.$value;

        if(is_dir($subpath)){
          if(strlen($value) > 17 && strpos($value, 'LEGITEXT') !== FALSE) {
            $this->appendCodesCollection($value, $subpath);
          }
          else{
            $this->findAllCodes($subpath);
          }
        }
        else{
          return; // NE DOIT PAS ARRIVER NORMALEMENT!
        }
      }
    }
  }

  public function appendCodesCollection($code_cID, $path){
    $collection = $this->codes_collection;
    $collection[$code_cID] = $path;
    $this->codes_collection = $collection;
  }


  /**
   * Retourne le chemin vers les sections d'un code de loi
   */
  public function getPathToSections(){
    return $this->getFullPathCode() . '/section_ta/LEGI/SCTA/00/00';
  }

  /**
   * Retourne le chemin vers les articles d'un code de loi
   */
  public function getPathToArticles(){
    return $this->getFullPathCode() . '/article/LEGI/ARTI/00/00';
  }

  /**
   * Retourne les deux chemins vers les metas-données d'un code de loi.
   */
  public function getCodePathMETAS(){
    $paths['struct'] = $this->getFullPathCode() . '/texte/struct/'. $this->getCID() .'.xml';
    $paths['version'] = $this->getFullPathCode() . '/texte/version/'. $this->getCID() .'.xml';
    return $paths;
  }

  public function goToSection($sectionID){
    $pathSection = $this->IDToPath($sectionID, 6);
    $pathSection = $this->getPathToSections() . $pathSection .'.xml';
    return $pathSection;
  }

  public function goToArticle($articleID){
    $pathArticle = $this->IDToPath($articleID, 6);
    $pathArticle = $this->getPathToArticles() .$pathArticle.'.xml';
    return $pathArticle;
  }

 /**
  * Retourne le chemin absolu dynamique vers l'archive legi renseignée (globale ou update).
  */
  public function getArchivePath() {
    return $this->archive_path;
  }

  /**
   * Retourne le chemin absolu dynamique vers le début de la birfurcation des chemins pour les codes de lois en vigueur d'une archive.
   * Devrait ressembler à chemin-vers-l'archive/global/code_et_TNC_en_vigueur/code_en_vigueur/LEGI/TEXT/00/00
   */
  public function getArchivePathCodes() {
    return $this->archive_path_codes;
  }

  public function getCodesCollection() {
    return $this->codes_collection;
  }

  public function getPathCode() {
    return $this->path_code;
  }

  public function getFullPathCode() {
    return $this->full_path_code;
  }

  public function getCID() {
    return $this->cID;
  }


}
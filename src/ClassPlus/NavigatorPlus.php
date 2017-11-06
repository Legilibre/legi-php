<?php



class NavigatorPlus extends Navigator {


  protected $collection_paths = [];


  /**
   * @param $path
   *
   * @return array
   * A utiliser avec precaution.
   *  Cette fonction va boucler sur elle même pour "creuser" l'arborescence pour trouver TOUS les fichiers et leurs chemins à partir du chemin renseigné la 1ère fois qu'elle est invoquée.
   *  Elle renverra le tout dans une array.
   * Si la fonction est initialisée au début de l'archive legi (avec un 1er chemin correct), elle va creuser et renvoyer une array contentant TOUTE l'archive legifrance.
   * Pour obtenir une liste " à plat" des fichiers trouvés (avec leurs chemins), on combine cela avec l'utilisation d'un attribut de la classe que l'on met à jour.
   *
   */
  public function saveAllSubPaths($path) {
    $a = array();
    $datas = scandir($path);
    foreach ($datas as $key => $value){
      if($value != "." && $value != ".."){
      $subpath = $path . '/'.$value;

        if(!array_key_exists($value, $a)){
        $a[$value] = array();
      }
        if(is_dir($subpath)){
          $a[$value][] = $this->saveAllSubPaths($subpath);
        }
        else{
          $this->appendPathsCollection($value, $subpath);
        }
      }
    }
    return $a;
  }

  public function appendPathsCollection($file_name, $path){
    $collection = $this->collection_paths;
    $collection[$file_name] = $path;
    $this->collection_paths = $collection;
  }

  public function resetPathsCollection() {
    $this->collection_paths = array();
  }



  public function getAllSectionsPaths(){
    $path_to_sections = $this->getFullPathCode() . '/section_ta/LEGI/SCTA/00/00';
    $this->resetPathsCollection();
    $this->saveAllSubPaths($path_to_sections);
    return $this->collection_paths;
  }

  public function getAllArticlesPaths(){
    $path_to_articles = $this->getFullPathCode() . '/article/LEGI/ARTI/00/00';
    $this->resetPathsCollection();
    $this->saveAllSubPaths($path_to_articles);
    return $this->collection_paths;
  }

  public function goToSection($sectionID){
    $pathSection = $this->IDToPath($sectionID, 6);
    $pathSection = $this->getFullPathCode() . '/section_ta/LEGI/SCTA/00/00'. $pathSection .'.xml';
    return $pathSection;
  }

  public function goToArticle($articleID){
    $pathArticle = $this->IDToPath($articleID, 6);
    $pathArticle = $this->getFullPathCode() . '/article/LEGI/ARTI/00/00'.$pathArticle.'.xml';
    return $pathArticle;
  }


}
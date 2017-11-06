<?php

/**
 * Class ArrayFileReader
 *
 * Cette classe est une ancienne version de la classe FileReader.
 * Elle contient des méthodes pour travailler sur les fichiers XML sous forme d'array.
 * Elle n'est pas indispensable au bon fonctionnement de l'exportation des codes de lois.
 * Il est même moins performant de passer par ces méthodes que de travailler directement avec des objets SimpleXMLElements.
 *
 */


class ArrayFileReader extends FileReader {


  protected $one_file_path;
  protected $one_file_content;
  protected $files_path;
  protected $files_content;
  protected $valuesToInsert;

  /**
   * Constructor
   */
  public function __construct() {
    $this->valuesToInsert = [];
  }


  public function openFile($file_path){
    if(file_exists($file_path)){
      $xml = new SimpleXmlIterator($file_path, null, true);
      return $xml;
    }
    return NULL;
  }

  public function xmlToArray(SimpleXMLIterator $xml) {
    $a = array();
    foreach ($xml as $nodeKey => $nodeValue){
      // Si dans notre array  la clé courante du pointeur de l'itérator n'existe pas, alors on la crée.
      if(!array_key_exists($nodeKey, $a)){
        $a[$nodeKey] = array();
      }
      // TODO : Peut etre possibilité de ne pas avoir les [0] si mieux conçue.
      if($xml->hasChildren()){
        if(!empty($nodeValue->attributes())) {
          $a[$nodeKey]['attributes'] = $this->getXMLAttributes($nodeValue);
        }


        if($nodeValue->getName() === 'CONTENU'){
          $a[$nodeKey][]['value'] = strval($nodeValue);
        }else{
          $a[$nodeKey][] = $this->xmlToArray($nodeValue);
        }
      }

      else{
        if(!empty($nodeValue)){
          $val= ['value' => strval($nodeValue)];
          if(!empty($nodeValue->attributes())) {
            $val['attributes'] = $this->getXMLAttributes($nodeValue);
          }
          $a[$nodeKey][]= $val;
        }
      }
    }
    return $a;
  }

  /**
   * @param \SimpleXMLIterator $xml
   *
   * @return array
   *  Va renvoyer tous les attributs d'un élément XML sous forme de tableau.
   * Indispensable à la fonction xmlToArray pour obtenir les attributs des éléments.
   */
  public function getXMLAttributes(SimpleXMLIterator $xml){
    $attr = array();
    foreach($xml->attributes() as $attribute => $attribvalue) {
      $attr[$attribute] = strval($attribvalue);
    }
    return $attr;
  }

  public function setFilesXMLContent() {
    $files_content = array();
    foreach ($this->getFilesPath() as $key => $path){
      $files_content[] = $this->openFile($path);
    }
    $this->files_content = $files_content;
  }


  /**
   * @param $array_to_search
   *
   * Fonction servant à trouver le parent direct d'un fichier. Celui ci sera le dernier dans l'arborescence commencant à la balise xml "CONTEXTE".
   * Pour que cette fonction fonctionne correctement il faut commencer à la bonne profondeur de cette arborescence, c'est à dire après le Code de loi lui même.
   * Normalement cela devrait ressembler à ceci : $file_content['CONTEXTE'][0]['TEXTE']
   *
   */
  public function findParentArray($array_to_search){
    if(isset($array_to_search[0]['TM'])) {
      //Alors ce n'est pas encore le parent direct et il faut creuser davantage et la fonction doit boucler sur elle meme.
      $a = $this->findParent($array_to_search[0]['TM']);
    }
    else{
      // Pas d'enfants, on a atteint le fond de la structure, et le TITRE_TM a ce niveau là contient les infos sur le parent direct du fichier.
      if(isset($array_to_search[0]['TITRE_TM'][0]['attributes']['id'])) {
        $a = $array_to_search[0]['TITRE_TM'][0]['attributes']['id'];
      }
      else{
        return NULL;
      }
    }
    return $a;
  }

  /**
   * @param $value
   *
   * Met en forme la valeur renseignée pour qu'elle soit prête pour une injection dans une table SQL.
   */
  public function prepareValue($value) {
    if(isset($value[0]['CONTENU'])){
      $value = $value[0]['CONTENU'];
    }
    //TODO : Cas des attribues.
    if(isset($value[0]['value'])){
      $value = $value[0]['value'];
    }
    if(is_numeric($value)){
      return $value + 0;
    }
    if(empty($value)){
      return '';
    }
    return $value;
  }

  public function appendXMLValuesToInsert($key, $value) {
    $valuesToInsert = $this->valuesToInsert;
    if($key == 'num' || $key == 'num_sequence') {
      $value = (int)$value;
    }
    else{
      $value = (string)$value;
    }

    $valuesToInsert[$key] = $value;
    $this->valuesToInsert = $valuesToInsert;
  }


    public function appendValuesToInsertArrayArray($key, $value) {
    $valuesToInsertArray = $this->valuesToInsert;
    $valuesToInsertArray[$key] = $this->prepareValue($value);
    $this->valuesToInsertArray = $valuesToInsertArray;
  }



  public function setOneFileContentArray(){
    $xml = $this->openFile($this->getOneFilePath());
    $this->one_file_content = $this->xmlToArray($xml);
  }


  /**
   * @param mixed $files_content
   */
  public function setFilesContentArray() {
    $files_content = array();
    foreach ($this->getFilesPath() as $key => $path){
      $files_content[] = $this->xmlToArray($this->openFile($path));
    }
    $this->files_content = $files_content;
  }


  // TODO : A reconstruire. Ne marche pas encore.
  public function getAllSubValues($array) {
    $a = array();
    // TODO : arriver à gérer et enlever le 1er niveau de zéro. et ne pas en rajouter ....
    // D'abord je check si cest une array.
    //Ensuite je check si sa clé c'est 0
    // Je check si y a davantage que 0 ... Un count supérieur à 1 ?

    foreach ($array as $key => $value){

      if(!array_key_exists($key, $a)){
        $a[$key] = array();
      }

      // TODO : Arriver à gérer attributes ou values.
      if(is_array($value)){
        $a[$key][] = $this->getAllSubValues($value);
      }
      else{
        $a[$key][] = $value;
        $this->appendCodeMetas($key, $value);
      }
    }
    
    return $a;
  }


  public function setSectionsValuesFromArray($file_content) {
    $this->resetClassAttribut('valuesToInsert');

    $this->appendValuesToInsert('id', $file_content['ID']);
    $this->appendValuesToInsert('cid_full', $file_content['CONTEXTE'][0]['TEXTE']['attributes']['cid']);
    $this->appendValuesToInsert('titre_ta', $file_content['TITRE_TA']);
    $this->appendValuesToInsert('parent', $this->findParent($file_content['CONTEXTE'][0]['TEXTE']));

    $this->appendValuesToInsert('cID_legiphp', $this->getCIDLegiPHP($file_content['CONTEXTE'][0]['TEXTE']['attributes']['cid']));

    return $this->valuesToInsert;
  }

  public function setArticlesValues($file_content) {
    $this->resetClassAttribut('valuesToInsert');

    $this->appendValuesToInsert('id', $file_content['META'][0]['META_COMMUN'][0]['ID']);
    $this->appendValuesToInsert('bloc_textuel', $file_content['BLOC_TEXTUEL'][0]['CONTENU']);
    $this->appendValuesToInsert('parent', $this->findParent($file_content['CONTEXTE'][0]['TEXTE']));
    $this->appendValuesToInsert('cID_legiphp', $this->getCIDLegiPHP($file_content['CONTEXTE'][0]['TEXTE']['attributes']['cid']));
    $this->appendValuesToInsert('num', $file_content['META'][0]['META_SPEC'][0]['META_ARTICLE'][0]['NUM']);
    $this->appendValuesToInsert('etat', $file_content['META'][0]['META_SPEC'][0]['META_ARTICLE'][0]['ETAT']);
    $this->appendValuesToInsert('date_debut', $file_content['META'][0]['META_SPEC'][0]['META_ARTICLE'][0]['DATE_DEBUT']);
    $this->appendValuesToInsert('date_fin', $file_content['META'][0]['META_SPEC'][0]['META_ARTICLE'][0]['DATE_FIN']);
    $this->appendValuesToInsert('type', $file_content['META'][0]['META_SPEC'][0]['META_ARTICLE'][0]['TYPE']);
    $this->appendValuesToInsert('dossier', $file_content['META'][0]['META_COMMUN'][0]['URL']);
    $this->appendValuesToInsert('cid_full', $file_content['CONTEXTE'][0]['TEXTE']['attributes']['cid']);
    $this->appendValuesToInsert('nota', $file_content['NOTA']);
    return $this->valuesToInsert;
  }

  public function appendCodeMetas($key, $value) {
    $codeMetas = $this->codeMetas;
    $codeMetas[$key] = $this->prepareValue($value);
    $this->codeMetas = $codeMetas;
  }
  public function appendCodeStructure($key, $value) {

    $codeStructure = $this->codeStructure;
    $codeStructure[$key] = $value;
    $this->codeStructure = $codeStructure;
  }



  // 0 = struct
  // 1 = version

  public function setMetasFromArray($file_content){
    $this->resetClassAttribut('codeMetas');

    $this->appendCodeMetas('VISAS', $file_content['VISAS']);
    $this->appendCodeMetas('SIGNATAIRES', $file_content['SIGNATAIRES']);
    $this->appendCodeMetas('TP', $file_content['TP']);
    $this->appendCodeMetas('NOTA', $file_content['NOTA']);
    $this->appendCodeMetas('ABRO', $file_content['ABRO']);
    $this->appendCodeMetas('RECT', $file_content['RECT']);

    $this->appendCodeMetas('ID', $file_content['META'][0]['META_COMMUN'][0]['ID']);
    $this->appendCodeMetas('ANCIEN_ID', $file_content['META'][0]['META_COMMUN'][0]['ANCIEN_ID']);
    $this->appendCodeMetas('ORIGINE', $file_content['META'][0]['META_COMMUN'][0]['ORIGINE']);
    $this->appendCodeMetas('URL', $file_content['META'][0]['META_COMMUN'][0]['URL']);
    $this->appendCodeMetas('NATURE', $file_content['META'][0]['META_COMMUN'][0]['NATURE']);


    $this->appendCodeMetas('CID', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_CHRONICLE'][0]['CID']);
    $this->appendCodeMetas('NUM', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_CHRONICLE'][0]['NUM']);
    $this->appendCodeMetas('NUM_SEQUENCE', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_CHRONICLE'][0]['NUM_SEQUENCE']);
    $this->appendCodeMetas('NOR', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_CHRONICLE'][0]['NOR']);
    $this->appendCodeMetas('DATE_PUBLI', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_CHRONICLE'][0]['DATE_PUBLI']);
    $this->appendCodeMetas('DATE_TEXTE', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_CHRONICLE'][0]['DATE_TEXTE']);
    $this->appendCodeMetas('DERNIERE_MODIFICATION', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_CHRONICLE'][0]['DERNIERE_MODIFICATION']);
    $this->appendCodeMetas('VERSION_A_VENIR', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_CHRONICLE'][0]['VERSIONS_A_VENIR']);
    $this->appendCodeMetas('ORIGINE_PUBLI', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_CHRONICLE'][0]['ORIGINE_PUBLI']);
    $this->appendCodeMetas('PAGE_DEB_PUBLI', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_CHRONICLE'][0]['PAGE_DEB_PUBLI']);
    $this->appendCodeMetas('PAGE_FIN_PUBLI', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_CHRONICLE'][0]['PAGE_FIN_PUBLI']);


    $this->appendCodeMetas('TITRE', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_VERSION'][0]['TITRE']);
    $this->appendCodeMetas('TITREFULL', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_VERSION'][0]['TITREFULL']);
    $this->appendCodeMetas('ETAT', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_VERSION'][0]['ETAT']);
    $this->appendCodeMetas('DATE_DEBUT', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_VERSION'][0]['DATE_DEBUT']);
    $this->appendCodeMetas('DATE_FIN', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_VERSION'][0]['DATE_FIN']);
    $this->appendCodeMetas('AUTORITE', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_VERSION'][0]['AUTORITE']);
    $this->appendCodeMetas('MINISTERE', $file_content['META'][0]['META_SPEC'][0]['META_TEXTE_VERSION'][0]['MINISTERE']);

    return $this->codeMetas;
  }

  public function setStructure($file_content) {
    $this->resetClassAttribut('codeStructure');
    $this->codeStructure =  $file_content['STRUCT'][0]['LIEN_SECTION_TA'];
    return $this->codeStructure;
  }


  public function getValuesToInsert(){
    return $this->valuesToInsert;
  }

  public function setOneFilePath($file_path) {
    $this->one_file_path = $file_path;
  }

  public function getOneFilePath() {
    return $this->one_file_path;
  }

  public function setOneFileContent() {
    $this->one_file_content = $this->openFile($this->getOneFilePath());
  }

  public function getOneFileContent() {
    return $this->one_file_content;
  }

  /**
   * @param mixed $files_path
   */
  public function setFilesPath($files_path) {
    $this->files_path = $files_path;
  }

  /**
   * @return mixed
   */
  public function getFilesPath() {
    return $this->files_path;
  }

  /**
   * @return mixed
   */
  public function getFilesContent() {
    return $this->files_content;
  }

}
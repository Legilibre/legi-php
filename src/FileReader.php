<?php



class FileReader {

  /**
   * Fonction pour ouvrir un fichier XML.
   */
  public function openFile($file_path){
    if(file_exists($file_path)) {
      return new SimpleXmlIterator($file_path, NULL, TRUE);
    }
    return NULL;
  }


  /**
   * @param $full_cid_texte
   *
   * A partir d'un cID entier (l'identifiant unique d'un code de lois), nous allons recréer un nouveau plus petit, maniable, et toujours unique.
   * Ce nouveau cID est surtout destiné à servir de composant pour les noms des tables de données stockant les données relatif au code de loi identifié.
   * LEGITEXT000006070666 nous donne C_06070666
   */
  public function getCIDLegiPHP($full_cid_texte){
      return str_replace('LEGITEXT0000', 'C_', $full_cid_texte);
  }



  /**
   * @param $xml
   *
   * Fonction servant à trouver le parent direct d'un fichier. Celui ci sera le dernier dans l'arborescence commencant à la balise xml "CONTEXTE".
   * Pour que cette fonction fonctionne correctement il faut commencer à la bonne profondeur de cette arborescence, c'est à dire après le Code de loi lui même.
   * Normalement cela devrait ressembler à ceci : $file_content->CONTEXTE->TEXTE
   *
   */
  public function findXMLParent($xml){
    if(isset($xml->TM)) {
      // Ce n'est pas encore le parent direct et il faut creuser davantage et la fonction doit boucler sur elle meme.
      $a = $this->findXMLParent($xml->TM);
    }
    else{
      // Pas d'enfants, on a atteint le fond de l'architecture XML, et le TITRE_TM a ce niveau là contient les infos sur le parent direct du fichier.
      if(isset($xml->TITRE_TM)) {
        $a = $xml->TITRE_TM->attributes()->id;
      }
      else{
        return NULL;
      }
    }
    return $a;
  }

  /**
   * Fonction pour obtenir la valeur en string d'une balise XML, tout en conservant les tags HTML présent à l'intérieur.
   */
  public function blocTextFromXML(SimpleXMLIterator $xml) {
    $value = '';
    if (isset($xml->CONTENU)) {
      $value = $xml->CONTENU->asXML();
      $value = str_replace( array('<CONTENU>', '</CONTENU>', '<CONTENU/>'), '', $value);
    }

    return $value;
  }

  /**
   * Fonction pour obtenir les valeurs d'un article à insérer en BDD à partir d'un fichier XML
   */
  public function setArticlesValuesFromXML(SimpleXMLIterator $file_content) {
    $values = array();
    $values['id'] = (string) $file_content->META->META_COMMUN->ID;
    $values['bloc_textuel'] = (string) $this->blocTextFromXML($file_content->BLOC_TEXTUEL);
    $values['nota'] = (string) $this->blocTextFromXML($file_content->NOTA);
    $values['parent'] = (string) $this->findXMLParent($file_content->CONTEXTE->TEXTE);
    $values['cID_legiphp'] = (string) $this->getCIDLegiPHP($file_content->CONTEXTE->TEXTE->attributes()->cid);
    $values['num'] = (string) $file_content->META->META_SPEC->META_ARTICLE->NUM;
    $values['etat'] = (string) $file_content->META->META_SPEC->META_ARTICLE->ETAT;
    $values['date_debut'] = (string) $file_content->META->META_SPEC->META_ARTICLE->DATE_DEBUT;
    $values['date_fin'] = (string) $file_content->META->META_SPEC->META_ARTICLE->DATE_FIN;
    $values['type'] = (string) $file_content->META->META_SPEC->META_ARTICLE->TYPE;
    $values['dossier'] = (string) $file_content->META->META_COMMUN->URL;
    $values['cid_full'] = (string) $file_content->CONTEXTE->TEXTE->attributes()->cid;
    return $values;
  }

  /**
   * Fonction pour obtenir les valeurs d'une section à insérer en BDD à partir d'un fichier XML
   */
  public function setSectionsValuesFromXML($file_content) {
    $values = array();
    $values['id'] = (string) $file_content->ID;
    $values['cid_full'] = (string) $file_content->CONTEXTE->TEXTE->attributes()->cid;
    $values['titre_ta'] = (string) $file_content->TITRE_TA;
    $values['parent'] = (string) $this->findXMLParent($file_content->CONTEXTE->TEXTE);
    $values['cID_legiphp'] = (string) $this->getCIDLegiPHP($file_content->CONTEXTE->TEXTE->attributes()->cid);
    return $values;
  }

  /**
   * Fonction pour obtenir les META valeurs d'un code de loi à insérer en BDD à partir d'un fichier XML
   */
  public function setCodesMetasValuesFromXML($file_content){
    $values = array();
    $values['cID_legiphp'] = (string) $this->getCIDLegiPHP($file_content->META->META_COMMUN->ID);
    $values['visas'] = (string) $file_content->VISAS;
    $values['signataires'] = (string) $file_content->SIGNATAIRES;
    $values['tp'] = (string) $file_content->TP;
    $values['nota'] = (string) $file_content->NOTA;
    $values['abro'] = (string) $file_content->ABRO;
    $values['rect'] = (string) $file_content->RECT;
    $values['id'] = (string) $file_content->META->META_COMMUN->ID;
    $values['ancien_id'] = (string) $file_content->META->META_COMMUN->ANCIEN_ID;
    $values['origine'] = (string) $file_content->META->META_COMMUN->ORIGINE;
    $values['url'] = (string) $file_content->META->META_COMMUN->URL;
    $values['nature'] = (string) $file_content->META->META_COMMUN->NATURE;
    $values['CID'] = (string) $file_content->META->META_SPEC->META_TEXTE_CHRONICLE->CID;
    $values['num'] = (string) $file_content->META->META_SPEC->META_TEXTE_CHRONICLE->NUM;
    $values['num_sequence'] = (string) $file_content->META->META_SPEC->META_TEXTE_CHRONICLE->NUM_SEQUENCE;
    $values['nor'] = (string) $file_content->META->META_SPEC->META_TEXTE_CHRONICLE->NOR;
    $values['date_publi'] = (string) $file_content->META->META_SPEC->META_TEXTE_CHRONICLE->DATE_PUBLI;
    $values['date_texte'] = (string) $file_content->META->META_SPEC->META_TEXTE_CHRONICLE->DATE_TEXTE;
    $values['derniere_modification'] = (string) $file_content->META->META_SPEC->META_TEXTE_CHRONICLE->DERNIERE_MODIFICATION;
    $values['version_a_venir'] = (string) $file_content->META->META_SPEC->META_TEXTE_CHRONICLE->VERSIONS_A_VENIR;
    $values['origine_publi'] = (string) $file_content->META->META_SPEC->META_TEXTE_CHRONICLE->ORIGINE_PUBLI;
    $values['page_deb_publi'] = (string) $file_content->META->META_SPEC->META_TEXTE_CHRONICLE->PAGE_DEB_PUBLI;
    $values['page_fin_publi'] = (string) $file_content->META->META_SPEC->META_TEXTE_CHRONICLE->PAGE_FIN_PUBLI;
    $values['titre'] = (string) $file_content->META->META_SPEC->META_TEXTE_VERSION->TITRE;
    $values['titrefull'] = (string) $file_content->META->META_SPEC->META_TEXTE_VERSION->TITREFULL;
    $values['etat'] = (string) $file_content->META->META_SPEC->META_TEXTE_VERSION->ETAT;
    $values['date_debut'] = (string) $file_content->META->META_SPEC->META_TEXTE_VERSION->DATE_DEBUT;
    $values['date_fin'] = (string) $file_content->META->META_SPEC->META_TEXTE_VERSION->DATE_FIN;
    $values['autorite'] = (string) $file_content->META->META_SPEC->META_TEXTE_VERSION->AUTORITE;
    $values['ministere'] = (string) $file_content->META->META_SPEC->META_TEXTE_VERSION->MINISTERE;
    return $values;
  }

}
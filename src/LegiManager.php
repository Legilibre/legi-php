<?php


class LegiManager {

  protected $dbcreator;

  protected $dbmanager;

  protected $navigator;

  protected $fileReader;

  private $db;

  protected $con;

  protected $code_cid;

  protected $archive_name;


  public function __construct() {
    global $config;
    $database = $config['database'];
    $this->db = $database;

    $con = new PDO($database['driver'] . ':host=' . $database['host'] . ';dbname=' . $database['database'], $database['username'], $database['password']);
    //$db = new \PDO('mysql:host=localhost;dbname=legi_php', 'root', 'admin');
    $con->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $this->con = $con;
    $this->dbcreator = new DBCreator($database);
    $this->dbmanager = new DBManager($database);
    $this->fileReader = new FileReader();
    $this->navigator = new Navigator();

  }

  /**
   * Fonction pour orienter le navigator vers une archive.
   */
  public function setArchive($archive_type, $archive_name = NULL) {
      if($archive_type == 'global') {
        $this->archive_name = 'global';
        $this->navigator->setPathToGlobalArchive();
      }
      elseif($archive_type == 'update') {
        if(empty($archive_name)){
          echo "Le nom d'archive n'est pas défini.";
          return;
        }
        $this->archive_name = $archive_name;
        $this->navigator->setPathToUpdateArchive($archive_name);
      }
      else {
         echo "Le type d'archive renseigné n'est pas bon";
        return;
      }

  }

  /**
   * Fonction pour définir le code de loi sur lequel l'objet va pouvoir travailler.
   * Indispensable pour les autres fonctions.
   */
  public function setCode($code_cid) {
    $this->navigator->setPathToCode($code_cid);
    if (!is_dir($this->navigator->getFullPathCode())) {
      echo "ERREUR : Le chemin pour le code de loi : " . $code_cid . " n'a pas pu être trouvé.";
      $this->code_cid = '';
      return ;
    }

    $this->code_cid = $this->navigator->getCID();
  }

  /**
   * A utiliser avec precaution.
   *  Cette fonction va boucler sur elle même pour "creuser" l'arborescence pour trouver TOUS les fichiers et leurs chemins à partir du chemin renseigné la 1ère fois qu'elle est invoquée.
   *  Elle ouvrira les fichiers au fur et à mesure pour insérer les valeurs dans la base de données.
   *
   */
  public function findAllFiles($path, $file_type) {
    $datas = scandir($path);
    foreach ($datas as $key => $value){
      if($value != "." && $value != ".."){
        $subpath = $path . '/'.$value;

        if(is_dir($subpath)){
          $this->findAllFiles($subpath, $file_type );
        }
        else{
           $this->fileInsert($this->fileReader->openFile($subpath), $file_type );
        }
      }
    }
  }


  public function fileInsert(SimpleXMLIterator $file_content, $file_type) {

    if($file_type == 'article'){
      $article_values = $this->fileReader->setArticlesValuesFromXML($file_content);
      $this->dbmanager->insertArticle($article_values);
    }
    elseif($file_type == 'section') {
      $section_values = $this->fileReader->setSectionsValuesFromXML($file_content);
      $this->dbmanager->insertSection($section_values);
    }
  }

  /**
   * Fonction globale pour l'import de tout le contenu d'un code de loi dans la base de données.
   */
  public function launchFullCode(){
    if(empty($this->code_cid)) {
      echo "ERREUR : Vous devez d'abord définir un code de loi valide avant d'aller plus loin.";
      return;
    }
    $code_name = $this->code_cid;
    // 1 : Insérer les METAS valeurs du code.
    $code_paths = $this->navigator->getCodePathMETAS();

    if (file_exists($code_paths['version'])) {
      $code_content = $this->fileReader->openFile($code_paths['version']);
      $code_values = $this->fileReader->setCodesMetasValuesFromXML($code_content);
      if(!empty($code_values)) {
        $this->dbmanager->insertCode($code_values);
        $code_name = $code_values['titrefull'];
      }
    } else {
      echo "<br>Il n'y a pas de document pour la version de ce code " . $code_name . " dans l'archive " . $this->archive_name;
    }

    // 2 :obtenir les paths pour accéder aux articles et aux sections.
    $path_to_articles = $this->navigator->getPathToArticles();
    $path_to_sections = $this->navigator->getPathToSections();

    // 3: Boucler pour trouver tous les fichiers et insérer leurs valeurs dans la BDD.
    if (file_exists($path_to_sections)) {
      $this->findAllFiles($path_to_sections, 'section');
    } else {
      echo "<br>Il n'y a pas de sections pour le code " . $code_name . " dans l'archive " . $this->archive_name;
    }

    if (file_exists($path_to_articles)) {
      $this->findAllFiles($path_to_articles, 'article');
    } else {
      echo "<br>Il n'y a pas d'articles pour le code " . $code_name . " dans l'archive " . $this->archive_name;
    }

    echo "<br> ARCHIVE : " . $this->archive_name. " - CODE : " . $code_name . " - INSERTION TERMINEE <br>";
  }

  /**
   * Fonction pour créer les tables pour le code à traiter.
   *
   */
  public function initCode() {
    if(empty($this->code_cid)) {
      return ;
    }

    $cID_legiphp = $this->fileReader->getCIDLegiPHP($this->code_cid);
    $this->dbcreator->createSectionsTable($cID_legiphp);
    $this->dbcreator->createArticlesTable($cID_legiphp);
    // TODO : BIENTOT CREATION DES TABLES POUR LES LIENS
  }

  /**
   * Fonction pour supprimer les tables pour le code à traiter.
   */
  public function deleteCode($cID_legiphp) {
    if(empty($cID_legiphp)) {
      return ;
    }

    //$cID_legiphp = $this->fileReader->getCIDLegiPHP($this->code_cid);
    $this->dbmanager->dropArticlesTable($cID_legiphp);
    $this->dbmanager->dropSectionsTable($cID_legiphp);
    $this->dbmanager->deleteCodeRow($cID_legiphp);
  }

  public function getListCodes(){
    return $this->dbmanager->listCodesRows();
  }

  /**
   * Function pour updater la BDD avec le contenu d'une archive, après un diagnostic.
   */
  public function fullUpdateProcess($archive_name) {
    $diagnostic = $this->obtainOneArchiveDiagnosticUpdate($archive_name);
    if($diagnostic['status'] == 'TERMINEE') {
      echo "Un update a déjà été réalisé pour cette archive. Si vous désirez quand même effectuer un nouvel update, il vous faut d'abord réaliser un nouveau diagnostic pour cette archive.";
      return ;
    }

    $codes_to_update = $diagnostic['codes_need_update'];

    $this->setArchive('update', $archive_name);
    foreach ($codes_to_update as $code) {
      $this->setCode($code);
      $this->launchFullCode();
    }
    $this->updateSuppressions();
    $this->dbmanager->updateStatutArchiveDiagnosticUpdate($archive_name, 'TERMINEE');

  }
  
  
  /**
   * Fonction pour réaliser un diagnostic sur une archive. 
   * On va comparer les codes de lois inscrits dans notre BDD, et ceux de cette archive, afin de savoir les codes de lois qui ont besoin d'être mis à jours.
   */
  public function makeArchiveDiagnosticUpdate() {
    if(empty($this->archive_name)) {
      echo "ERREUR : L'archive n'a pas été définie.";
      return;
    }
    $codes_in_db = $this->dbmanager->listCodesRows();
    d($codes_in_db);

    if(empty($codes_in_db)) {
      echo "ERREUR : Il n'y a aucun code de lois inscrit dans la base de données.";
      return;
    }

    $this->navigator->findAllCodes($this->navigator->getArchivePathCodes());
    $codes_collection = $this->navigator->getCodesCollection();
    d($codes_collection);

    $intersect_codes = array_intersect(array_column($codes_in_db,'cid_full'), array_keys($codes_collection));
    d($intersect_codes);

    // json_encode


    $values = [
      'archive_name' => $this->archive_name,
      'status' => 'EN ATTENTE',
      'codes_in_db' => json_encode($codes_in_db),
      'codes_in_archive' => json_encode($codes_collection),
      'codes_need_update' => json_encode($intersect_codes),
    ];

    d($values);
    $this->dbmanager->insertArchiveDiagnosticUpdate($values);
  }


  public function obtainOneArchiveDiagnosticUpdate($archive_name) {
    $archive = $this->dbmanager->getArchiveDiagnosticUpdate($archive_name);
    if(empty($archive)) {
      echo "ERREUR : Le diagnostic pour cette archive n'existe pas.";
      return ;
    }
    $archive['codes_in_db'] = json_decode($archive['codes_in_db'], true);
    $archive['codes_in_archive'] = json_decode($archive['codes_in_archive'], true);
    $archive['codes_need_update'] = json_decode($archive['codes_need_update'], true);

    return $archive;

  }

  public function obtainAllArchivesDiagnosticsUpdates() {
    $all_archives = $this->dbmanager->getAllArchivesDiagnosticsUpdates();
    foreach ($all_archives as $key => $archive) {
      $all_archives[$key]['codes_in_db'] = json_decode($archive['codes_in_db'], true);
      $all_archives[$key]['codes_in_archive'] = json_decode($archive['codes_in_archive'], true);
      $all_archives[$key]['codes_need_update'] = json_decode($archive['codes_need_update'], true);
    }
    return $all_archives;

  }


  public function updateSuppressions() {
    if(empty($this->archive_name)) {
      echo "ERREUR : L'archive n'a pas été définie.";
      return;
    }
    $archive_path = $this->navigator->getArchivePath();

    $liste_suppressions_path = $archive_path . "/liste_suppression_legi.dat";
    if (!file_exists($liste_suppressions_path)) {
      echo "<br>Il n'y a pas de liste de suppressions dans l'archive : " . $this->archive_name;
      return;
    }
    $file = file($liste_suppressions_path);
    $lines = array();
    foreach ($file as $key => $line){

      if(strpos($line,'code_en_vigueur') !== false){
        $start = strpos($line, 'LEGITEXT0000') ;
        $cid = substr($line, $start, 20);
        $type = strpos($line,'article') !== false ? 'articles' : 'sections';
        $id = substr(trim($line), -20);
        $lines[] = ['file' => $line, 'cid' => $cid, 'type' => $type, 'id' => $id];
      }
    }

    if(empty($lines)) {
      echo "<br> Il n'y a aucun codes de loi en vigueur ayant du contenu à supprimer pour l'archive : " . $this->archive_name;
      return;
    }

    $cids_in_db = array_column($this->dbmanager->listCodesRows(), 'cid_full');
    foreach ($lines as $key => $line) {
     if(array_search($line['cid'], $cids_in_db) === FALSE ) {
       unset($lines[$key]);
     }
    }
    d($lines);
    if(empty($lines)) {
      echo "<br> Il n'y a aucun codes déjà inscrit dans la BDD ayant du contenu à supprimer pour l'archive : " . $this->archive_name;
      return;
    }

    foreach ($lines as $key => $line) {
      $cID_legiphp = $this->fileReader->getCIDLegiPHP($line['cid']);
      $this->dbmanager->deleteRow($cID_legiphp, $line['type'], $line['id']);
    }
    echo "<br>Suppressions terminées pour l'archive : ". $this->archive_name;

  }




  // TODO : A réecrire
  /**
   * Insérer directement un fichier dans la BDD a partir de son idTexte, et du type de fichier.
   */
  public function launchOneFile($idTexte, $file_type) {

    if($file_type == 'article'){
      $path_file = $this->navigator->goToArticle($idTexte);
    }
    elseif($file_type == 'section') {
      $path_file = $this->navigator->goToSection($idTexte);
    }
    else {
      return NULL;
    }

    $this->fileReader->setOneFilePath($path_file);
    $this->fileReader->setOneFileContent();
    $file_content = $this->fileReader->getOneFileContent();

    if($file_type == 'article'){
      $article_values = $this->fileReader->setArticlesValuesFromXML($file_content);
      $this->dbmanager->insertArticle($article_values);
    }
    elseif($file_type == 'section') {
      $section_values = $this->fileReader->setSectionsValuesFromXML($file_content);
      $this->dbmanager->insertSection($section_values);
    }

  }


}
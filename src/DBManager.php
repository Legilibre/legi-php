<?php


class DBManager {

  private $db;
  protected $con;
  /**
   * Constructeur étant chargé d'enregistrer l'instance de PDO dans l'attribut $con.
   * @param $db \PDO Le DAO
   *
   */
  public function __construct($database)
  {

    $this->db = $database;

    $con = new PDO($database['driver'].':host='.$database['host'].';dbname='.$database['database'], $database['username'], $database['password']);
    $con->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $this->con = $con;
  }

  /**
   * @return \PDO
   */
  public function getDb() {
    return $this->db;
  }

  /*
   * Vérifie si une table a déjà été créée.
   */
  public function checkTableExist($table){
    $result = $this->con->query("SHOW TABLES LIKE '" . $table . "'")->rowCount();
    return $result;
  }

  /*
   * Vérifie si un code de loi est déjà présent dans la table codes_versions
   */
  public function checkCodeRowExist($cID_legiphp, $table) {

    $query = "SELECT COUNT(*) FROM codes_versions WHERE cID = '$cID_legiphp'";
    try {
      $con = $this->con->prepare($query);
      $con->execute();
        $result = $con->fetch(PDO::FETCH_ASSOC);
        d($result);
      $result = (int) current($result);

      if($result !== 0) {
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
    catch(PDOException $e)
    {
      echo "<br>" . $e->getMessage() . "<br>";
    }
  }

  /*
   * Retourne une liste des codes de lois enregistrés dans la BDD.
   */
  public function listCodesRows() {
    $query = "SELECT cID, cid_full, titrefull FROM codes_versions";

    try {
      $con = $this->con->prepare($query);
      $con->execute();
      $results = $con->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
    catch(PDOException $e)
    {
      echo "<br>" . $e->getMessage() . "<br>";
    }
  }




  public function insertCode($code_values){

    if($this->checkTableExist('codes_versions') == FALSE){
      echo 'TABLE DON\'T EXIST!<br>';
      return;
    }




    $query = "INSERT INTO `codes_versions` (
  `cID`,
  `cid_full`,
  `titrefull`,
  `titrefull_s`,
  `date_debut`,
  `date_fin`,
  `etat`,
  `nature`,
  `autorite`,
  `ministere`,
  `num`,
  `num_sequence`,
  `nor`,
  `date_publi`,
  `date_texte`,
  `derniere_modification`,
  `origine_publi`,
  `page_deb_publi`,
  `page_fin_publi`,
  `visas`,
  `signataires`,
  `tp`,
  `nota`,
  `abro`,
  `rect`,
  `dossier`,
  `mtime`,
  `texte_id` 
) VALUES(
  :cID,
  :cid_full,
  :titrefull,
  :titrefull_s,
  :date_debut,
  :date_fin,
  :etat,
  :nature,
  :autorite,
  :ministere,
  :num,
  :num_sequence,
  :nor,
  :date_publi,
  :date_texte,
  :derniere_modification,
  :origine_publi,
  :page_deb_publi,
  :page_fin_publi,
  :visas,
  :signataires,
  :tp,
  :nota,
  :abro,
  :rect,
  :dossier,
  :mtime,
  :texte_id
) ON DUPLICATE KEY UPDATE
 cID = VALUES(cID),
 cid_full = VALUES(cid_full),
 titrefull = VALUES(titrefull),
 titrefull_s = VALUES(titrefull_s),
 date_debut = VALUES(date_debut),
 date_fin = VALUES(date_fin),
 etat = VALUES(etat),
 nature = VALUES(nature),
 autorite = VALUES(autorite),
 ministere = VALUES(ministere),
 num = VALUES(num),
 num_sequence = VALUES(num_sequence),
 nor = VALUES(nor),
 date_publi = VALUES(date_publi),
 date_texte = VALUES(date_texte),
 derniere_modification = VALUES(derniere_modification),
 origine_publi = VALUES(origine_publi),
 page_deb_publi = VALUES(page_deb_publi),
 page_fin_publi = VALUES(page_fin_publi),
 visas = VALUES(visas),
 signataires = VALUES(signataires),
 tp = VALUES(tp),
 nota = VALUES(nota),
 abro = VALUES(abro),
 rect = VALUES(rect),
 dossier = VALUES(dossier),
 mtime = VALUES(mtime),
 texte_id = VALUES(texte_id)
";
    // \s:(.*)   $1 = VALUES($1),
    $query_values = array(
        "cID" => $code_values['cID_legiphp'],
        "cid_full" => $code_values['CID'],
        "titrefull" => $code_values['titre'],
        "titrefull_s" => $code_values['titrefull'],
        "date_debut" => $code_values['date_debut'],
        "date_fin" => $code_values['date_fin'],
        "etat" => $code_values['etat'],
        "nature" => $code_values['nature'],
        "autorite" => $code_values['autorite'],
        "ministere" => $code_values['ministere'],
        "num" => $code_values['num'],
        "num_sequence" => $code_values['num_sequence'],
        "nor" => $code_values['nor'],
        "date_publi" => $code_values['date_publi'],
        "date_texte" => $code_values['date_texte'],
        "derniere_modification" => $code_values['derniere_modification'],
        "origine_publi" => $code_values['origine_publi'],
        "page_deb_publi" => $code_values['page_deb_publi'],
        "page_fin_publi" => $code_values['page_fin_publi'],
        "visas" => $code_values['visas'],
        "signataires" => $code_values['signataires'],
        "tp" => $code_values['tp'],
        "nota" => $code_values['nota'],
        "abro" => $code_values['abro'],
        "rect" => $code_values['rect'],
        "dossier" => $code_values['url'],
        "mtime" => date("Y-m-d"),
        "texte_id" => $code_values['id']
      );
    
    try {
      $con = $this->con;
      $con->prepare($query)->execute($query_values);

      echo "Insertion Réussie dans la Table codes_versions<br>";
    }
    catch(PDOException $e)
    {
      echo "<br> CODE :" .$code_values['cID_legiphp']. "<br>". $e->getMessage();
    }
  }

  /*
   * Insert une nouvelle ligne dans la table d'un code de loi
   */
  public function insertSection($section_values){
    if(!isset($section_values['cID_legiphp'])){
      return;
    }

    $tb_name = $section_values['cID_legiphp']."_sections" ;

    if($this->checkTableExist($tb_name) == FALSE) {
      echo "TABLE $tb_name DON'T EXIST!<br>";
      return;
    }

    $query = "INSERT INTO `$tb_name` (
  `id`,
  `titre_ta`,
  `cid`,
  `cid_full`,
  `parent`,
  `mtime`
) VALUES(
  :id,
  :titre_ta,
  :cid,
  :cid_full,
  :parent,
  :mtime
)
ON DUPLICATE KEY UPDATE 
  id = VALUES(id),
  titre_ta = VALUES(titre_ta),
  cid = VALUES(cid),
  cid_full = VALUES(cid_full),
  parent = VALUES(parent),
  mtime = VALUES(mtime)
 ";

    $query_values = array(
      "id" => $section_values['id'],
      "titre_ta" => $section_values['titre_ta'],
      "cid" => $section_values['cID_legiphp'],
      "cid_full" => $section_values['cid_full'],
      "parent" => $section_values['parent'],
      "mtime" => date("Y-m-d"),
    );

    try {
      $con = $this->con;
      $con->prepare($query)->execute($query_values);

     // echo "Insertion Réussie dans la Table $tb_name<br>";
    }
    catch(PDOException $e)
    {
      echo "<br>" .$section_values['id']. " => " . $e->getMessage() . "<br>" ;
    }

  }

  /*
   * Insert une nouvelle ligne
   */
  public function insertArticle($article_values){
    if(!isset($article_values['cID_legiphp'])){
      return;
    }

    $tb_name = $article_values['cID_legiphp']."_articles" ;

    if($this->checkTableExist($tb_name) == FALSE) {
      echo "<br>TABLE $tb_name DON'T EXIST!";
      return;
    }

    $query = "INSERT INTO `$tb_name` (
  `id`,
  `bloc_textuel`,
  `cid`,
  `cid_full`,
  `parent`,
  `num`,
  `etat`,
  `date_debut`,
  `date_fin`,
  `type`,
  `dossier`,
  `nota`,
  `mtime`
) VALUES(
  :id,
  :bloc_textuel,
  :cid,
  :cid_full,
  :parent,
  :num,
  :etat,
  :date_debut,
  :date_fin,
  :type,
  :dossier,
  :nota,
  :mtime
) ON DUPLICATE KEY UPDATE 
  `id` = VALUES(id),
  bloc_textuel = VALUES(bloc_textuel),
   cid = VALUES(cid),
 cid_full = VALUES(cid_full),
 parent = VALUES(parent),
  num = VALUES(num),
 etat = VALUES(etat),
 date_debut = VALUES(date_debut),
 date_fin = VALUES(date_fin),
 type = VALUES(type),
 dossier = VALUES(dossier),
 nota = VALUES(nota),
 mtime = VALUES(mtime)";

    $query_values = array(
      "id" => $article_values['id'],
      "bloc_textuel" => $article_values['bloc_textuel'],
      "cid" => $article_values['cID_legiphp'],
      "cid_full" => $article_values['cid_full'],
      "parent" => $article_values['parent'],
      "num" => $article_values['num'],
      "etat" => $article_values['etat'],
      "date_debut" => $article_values['date_debut'],
      "date_fin" => $article_values['date_fin'],
      "type" => $article_values['type'],
      "dossier" => $article_values['dossier'],
      "nota" => $article_values['nota'],
      "mtime" => date("Y-m-d"),
    );

    try {
      $con = $this->con;
      $con->prepare($query)->execute($query_values);

     // echo "Insertion Réussie dans la Table $tb_name<br>";
    }
    catch(PDOException $e)
    {
      echo "<br>" . $article_values['id'] . " => " . $e->getMessage();
    }
  }



  /*
   * Supprime la table des articles pour un code de loi
   */
  public function dropArticlesTable($cID_legiphp) {
    $tb_name= $cID_legiphp ."_articles";
    if($this->checkTableExist($tb_name) == FALSE) {
      echo "TABLE $tb_name DON'T EXIST!";
      return;
    }
    $sql= $this->con->prepare("DROP TABLE ". $tb_name);

    if($sql->execute()){
      echo " Table $tb_name deleted <br>";
    }else{
      print_r($sql->errorInfo());
    }
  }

  /*
   * Supprime la table des sections pour un code de loi.
   */
  public function dropSectionsTable($cID_legiphp) {
    $tb_name = $cID_legiphp ."_sections";

    if($this->checkTableExist($tb_name) == FALSE) {
      echo "<br>TABLE $tb_name DON'T EXIST! <br>";
      return;
    }
    $sql= $this->con->prepare("DROP TABLE ". $tb_name);

    if($sql->execute()){
      echo " Table $tb_name deleted <br>";
    }else{
      print_r($sql->errorInfo());
    }
  }

  /*
   * Supprime un enregistrement d'article ou de section
   */
  public function deleteRow($cID_legiphp, $type, $id) {
    $tb_name= $cID_legiphp ."_".$type;
    if($this->checkTableExist($tb_name) == FALSE) {
      echo "TABLE $tb_name DON'T EXIST!";
      return;
    }

    $query = "DELETE FROM `$tb_name` WHERE id = '$id'";
    d($query);

    try {
      $con = $this->con->prepare($query);
      d($con);
      //$con->execute();
    }
    catch(PDOException $e)
    {
      echo "<br>" . $e->getMessage() . "<br>";
    }


  }

  /*
   * Supprime la ligne d'un code de loi dans la table 'code_versions'
   */
  public function deleteCodeRow($cID_legiphp) {

    if($this->checkCodeRowExist($cID_legiphp, 'codes_versions') == FALSE) {
      echo "Code ROW $cID_legiphp  n'existe pas ! <br>";
      return;
    }

    $sql = "DELETE FROM `codes_versions` WHERE `cID` = :cID";
    $query = $this->con->prepare( $sql );
    if($query->execute( array( ":cID" => $cID_legiphp ))) {
      echo " ROW $cID_legiphp deleted from TABLE codes_versions <br>";
    }else{
      print_r($query->errorInfo());
    }
  }

  /*
   * Insert un nouveau diagnostic d'archive à traiter.
   */
  public function insertArchiveDiagnosticUpdate(array $values) {
    if(!isset($values['archive_name'])){
      return;
    }
    $tb_name = "archives_diagnostics_updates" ;

    if($this->checkTableExist($tb_name) == FALSE) {
      echo "TABLE $tb_name DON'T EXIST!<br>";
      return;
    }

    $query = "INSERT INTO `$tb_name` (
  `archive_name`,
  `status`,
  `codes_in_db`,
  `codes_in_archive`,
  `codes_need_update`
) VALUES(
  :archive_name,
  :status_value,
  :codes_in_db,
  :codes_in_archive,
  :codes_need_update
)ON DUPLICATE KEY UPDATE 
 archive_name = VALUES(archive_name),
 `status` = VALUES(status),
 codes_in_db = VALUES(codes_in_db),
 codes_in_archive = VALUES(codes_in_archive),
 codes_need_update = VALUES(codes_need_update),
 created = NOW()
";
    $query_values = array(
      "archive_name" => $values['archive_name'],
      "status_value" => $values['status'],
      "codes_in_db" => $values['codes_in_db'],
      "codes_in_archive" => $values['codes_in_archive'],
      "codes_need_update" => $values['codes_need_update'],
    );

    try {
      $con = $this->con;
      $con->prepare($query)->execute($query_values);

      echo "Insertion Réussie dans la Table $tb_name<br>";
    }
    catch(PDOException $e)
    {
      echo "<br>" . $values['archive_name']. "<br>" . $e->getMessage();
    }
  }


  public function getArchiveDiagnosticUpdate($archive_name) {
    $query = "SELECT * FROM archives_diagnostics_updates WHERE archive_name = '$archive_name'";

    try {
      $con = $this->con->prepare($query);
      $con->execute();
      $results = $con->fetch(PDO::FETCH_ASSOC);
      return $results;
    }
    catch(PDOException $e)
    {
      echo "<br>" . $e->getMessage() . "<br>";
    }
  }

  public function getAllArchivesDiagnosticsUpdates() {
    $query = "SELECT * FROM archives_diagnostics_updates";

    try {
      $con = $this->con->prepare($query);
      $con->execute();
      $results = $con->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
    catch(PDOException $e)
    {
      echo "<br>" . $e->getMessage() . "<br>";
    }
  }

  public function updateStatutArchiveDiagnosticUpdate($archive_name, $statut) {
    $query = "UPDATE archives_diagnostics_updates SET `status`='$statut' WHERE archive_name = '$archive_name'";
    try {
      $con = $this->con->prepare($query);
      $con->execute();
    }
    catch(PDOException $e)
    {
      echo "<br>" . $e->getMessage() . "<br>";
    }
  }

}


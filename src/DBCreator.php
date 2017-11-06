<?php


class DBCreator {

  protected $db;

  /**
   * Constructeur étant chargé d'enregistrer l'instance de PDO dans l'attribut $db.
   * @param $db \PDO Le DAO
   * @return void
   */
  public function __construct($database)
  {
    //$db = self::getMysqlConnexionWithPDO();
    //global $config;
    //$database = $config['database'];

    $db = new PDO($database['driver'].':host='.$database['host'].';dbname='.$database['database'], $database['username'], $database['password']);
    //$db = new \PDO('mysql:host=localhost;dbname=legi_php', 'root', 'admin');
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $this->db = $db;
  }


  public function initProject() {

  }

  public function createCodesVersionsTable(){
    try {
      $db = $this->db;
      //$db = new \PDO('mysql:host=localhost;dbname=legi_php', 'root', 'admin');
      // sql to create table codes_versions
      $sql_create = "CREATE TABLE `codes_versions` (
  `cID` varchar(30) NOT NULL,
  `cid_full` varchar(30) NOT NULL,
  `titrefull` text NOT NULL,
  `titrefull_s` text NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `etat` text NOT NULL,
  `nature` text NOT NULL,
  `autorite` text,
  `ministere` text,
  `num` text,
  `num_sequence` text,
  `nor` varchar(12) DEFAULT NULL,
  `date_publi` date DEFAULT NULL,
  `date_texte` date DEFAULT NULL,
  `derniere_modification` date DEFAULT NULL,
  `origine_publi` text,
  `page_deb_publi` int(11) DEFAULT NULL,
  `page_fin_publi` int(11) DEFAULT NULL,
  `visas` text,
  `signataires` text,
  `tp` text,
  `nota` text,
  `abro` text,
  `rect` text,
  `dossier` text,
  `mtime` date DEFAULT NULL,
  `texte_id` varchar(30) NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";


      // use exec() because no results are returned
      $db->exec($sql_create);
      echo "Table codes_versions created successfully<br>";

      $sql_alter = "
      ALTER TABLE `codes_versions`
  ADD PRIMARY KEY (`cID`),
  ADD UNIQUE KEY `cid_full` (`cid_full`),
  ADD UNIQUE KEY `cID` (`cID`);
      ";
      $db->exec($sql_alter);

      echo "Table codes_versions altered successfully<br>";
    }
    catch(PDOException $e)
    {
      echo $sql_create . "<br>" . $e->getMessage();
    }
  }


  public function createArchivesDiagnosticsUpdates() {

    $sql_create = "CREATE TABLE `archives_diagnostics_updates` (
  `id` int(11) NOT NULL,
  `archive_name` varchar(40) NOT NULL,
  `status` varchar(15) NOT NULL,
  `codes_in_db` longtext NOT NULL,
  `codes_in_archive` longtext NOT NULL,
  `codes_need_update` longtext NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

    $sql_alter = "ALTER TABLE `archives_diagnostics_updates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `archive_name` (`archive_name`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;";

    try {
      $db = $this->db;
            // use exec() because no results are returned
      $db->exec($sql_create);
      echo "Table archives_diagnostics_updates created successfully<br>";

      $db->exec($sql_alter);
      echo "Table archives_diagnostics_updates altered successfully<br>";
    }
    catch(PDOException $e)
    {
      echo "archives_diagnostics_updates <br>" . $e->getMessage();
    }

  }

  /**
   * @param $cID_legi_php
   *
   * Afin d'éviter une seule table gigantesque avec tous les sections de tous les codes de lois, nous crééons autant de tables qu'il y a de codes de lois.
   * Pour faciliter au mieux leurs maintenances et accès, nous les nommons avec un identifiant issue du cIDTexte original (l'identifiant SARDE du texte de loi).
   * Elle aura donc comme nom son cID spécial legi-php + _sections.
   */
  public function createSectionsTable($cID_legi_php){

    try {
      $db = $this->db;
      //$db = new \PDO('mysql:host=localhost;dbname=legi_php', 'root', 'admin');
      // sql to create table codes_versions
      $sql_create = "CREATE TABLE `".$cID_legi_php."_sections` (
  `id` varchar(30) NOT NULL,
  `titre_ta` text NOT NULL,
  `cid` varchar(30) NOT NULL,
  `cid_full` varchar(30) NOT NULL,
  `parent` varchar(40) DEFAULT NULL,
  `commentaire` text,
  `etat` varchar(40) DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `niv` int(11) DEFAULT NULL,
  `mtime` date DEFAULT NULL,
  `dossier` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

      $db->exec($sql_create);
      echo "Table ".$cID_legi_php."_sections created successfully<br>";

      $sql_alter = "
      ALTER TABLE `".$cID_legi_php."_sections`
        ADD PRIMARY KEY (`id`),
        ADD UNIQUE KEY `id` (`id`);
      ";
      $db->exec($sql_alter);

      echo "Table ".$cID_legi_php."_sections altered successfully<br>";
    }
    catch(PDOException $e)
    {
      echo "<br>" . $e->getMessage();
    }

  }


  /**
   * @param $cID_legi_php
   *
   * Afin d'éviter une seule table gigantesque avec tous les sections de tous les codes de lois, nous crééons autant de tables qu'il y a de codes de lois.
   * Pour faciliter au mieux leurs maintenances et accès, nous les nommons avec un identifiant issue du cIDTexte original (l'identifiant SARDE du texte de loi).
   * Elle aura donc comme nom son cID spécial legi-php + _sections.
   */
  public function createArticlesTable($cID_legi_php){

    try {
      $db = $this->db;
      //$db = new \PDO('mysql:host=localhost;dbname=legi_php', 'root', 'admin');
      // sql to create table codes_versions
      $sql_create = "
      CREATE TABLE `".$cID_legi_php."_articles` (
  `id` varchar(20) NOT NULL,
  `parent` varchar(20) NOT NULL,
  `bloc_textuel` mediumtext NOT NULL,
  `num` varchar(255) DEFAULT NULL,
  `cid` varchar(20) NOT NULL,
  `etat` varchar(40) DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `type` text,
  `dossier` text,
  `nota` text DEFAULT NULL,
  `cid_full` varchar(20) NOT NULL,
  `mtime` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
      ";

      $db->exec($sql_create);
      echo "Table ".$cID_legi_php."_articles created successfully<br>";

      $sql_alter = "
      ALTER TABLE `".$cID_legi_php."_articles`
        ADD PRIMARY KEY (`id`),
        ADD UNIQUE KEY `id` (`id`);
      ";
      $db->exec($sql_alter);

      echo "Table ".$cID_legi_php."_articles altered successfully<br>";
    }
    catch(PDOException $e)
    {
      echo "<br>" . $e->getMessage();
    }

  }


}
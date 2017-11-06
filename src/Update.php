<?php

class Update {

  public static $legi_ftp_path;
  public static $dl_path;

  protected $legi_ftp_list;
  protected $dl_list;
  protected $temp_list;
  protected $diff_list;



  public function __construct() {
    global $config;
    self::$legi_ftp_path = 'ftp://echanges.dila.gouv.fr/LEGI/';
    self::$dl_path = $config['paths']['legi_download_archives'];
    $this->setLegiFTPList();
    $this->setDLList();
    $this->setDiffDownloadList();
  }

  public function setDLList(){
    $dl_path = self::$dl_path;
    $dl_list = scandir($dl_path);
    foreach ($dl_list as $key => $value) {
      if(is_dir($dl_path. $value) || strpos($value, 'tar.gz') === false) {
        unset($dl_list[$key]);
      }
    }
    $this->dl_list = array_values($dl_list);
  }


  public function setLegiFTPList() {
    $legi_ftp_list =  scandir(self::$legi_ftp_path);
    foreach ($legi_ftp_list as $key => $value) {
      if(substr($value,0, 4) !== 'legi') {
        unset($legi_ftp_list[$key]);
      }
    }
    $this->legi_ftp_list = array_values($legi_ftp_list);
  }


  /*
   * Fait un differentiel entre la liste des archives présentes sur le FTP Legi, et les archives déjà téléchargées
   */
  public function setDiffDownloadList() {
    $diff = array_diff($this->legi_ftp_list, $this->dl_list);
    $this->diff_list = $diff;
  }

  public function getDlList() {
    return $this->dl_list;
  }

  public function getTempList() {
    $this->temp_list = array_diff(scandir(self::$dl_path. '/temp'), array('.', '..'));
    return $this->temp_list;
  }

  public function getDiffList() {
    return $this->diff_list;
  }

  public function getLegiFTPList() {
    return $this->legi_ftp_list;
  }


  public function unCompress($file_name){
    $file_path = self::$dl_path .'/'. $file_name;
    if(!is_file($file_path)) {
      echo 'Le chemin pour le fichier' . $file_name . ' n\'a pas pu être trouvé.';
      return;
    }

    try {
      // decompress from gz
      $p = new PharData($file_path);
      $p->decompress(); // creates files.tar
      $file_tar = substr($file_path, 0, -3);

      // unarchive from the tar
      $phar = new PharData($file_tar);
      $phar->extractTo(self::$dl_path . '/temp/');
      //$phar->extractTo(self::$dl_path . 'temp/', $file_to_extract, TRUE);

      unlink($file_tar);

    }
    catch (Exception $e ) {
      echo 'Exception reçue : ',  $e->getMessage(), "\n";
    }

  }


  /**
   * Fonction Récursive pour supprimer l'intégralité d'un dossier en PHP.
   */
  public static function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
      throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
      $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
      if (is_dir($file)) {
        self::deleteDir($file);
      } else {
        unlink($file);
      }
    }
    rmdir($dirPath);
  }

  /**
   * Fonction pour télécharger une archive legi depuis le FTP Legi.
   */
  public function downloadLegiArchive(string $archive_name) {
    $url = self::$legi_ftp_path . $archive_name;
    d($url);

    set_time_limit(0);

    //File to save the contents to
    $file = fopen (self::$dl_path . '/'. $archive_name, 'w+');
    d($file);
    $curl = curl_init($url);

    curl_setopt_array($curl, [
      CURLOPT_URL            => $url,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_FILE           => $file,
      CURLOPT_TIMEOUT        => 500,
    ]);

    $response = curl_exec($curl);

    if($response === false) {
      // Update as of PHP 5.3 use of Namespaces Exception() becomes \Exception()
      throw new \Exception('Curl error: ' . curl_error($curl));
    }
    curl_close($curl);
  }


  /**
   * Fonction pour obtenir le nom d'une archive legi décompressée à partir du fichier tar.gz
   * "legi_20170301-212232.tar.gz" devient "20170301-212232"
   */
  public function getNameArchiveUncompressed($file_name) {
    if(strpos($file_name,'legi') !== FALSE){
      $file_name = str_replace(['legi_', ".tar.gz"], "", $file_name);
    }
    return $file_name;
  }

  /**
   * Possiblement plus performant. A voir
   */
  public function gzDecompressFile($srcName, $dstName) {
    $error = false;

    if( $file = gzopen($srcName, 'rb') ) { // open gz file

      $out_file = fopen($dstName, 'wb'); // open destination file

      while (($string = gzread($file, 4096)) != '') { // read 4kb at a time
        if( !fwrite($out_file, $string) ) { // check if writing was successful
          $error = true;
        }
      }

      // close files
      fclose($out_file);
      gzclose($file);

    } else {
      $error = true;
    }

    if ($error)
      return false;
    else
      return true;
  }


  // TODO: Si des fichiers en trop du coté dl, les supprimer.



}
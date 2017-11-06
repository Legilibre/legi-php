<?php

/**
 * Fichier dédié pour l'accomplissement automatique des tâches d'update.
 * En gros ce que le fichier update.php permet de faire via une interface HTML.
 *
 */


//http://php.net/manual/fr/function.exec.php
//exec("wget $url -O $filename");

include 'conf/settings.php';
require 'autoload.php';
include 'conf/cidTexte_list.php';
global $config;

$manager = new LegiManager();
$updater = new Update();
$diff_list = $updater->getDiffList();
//$dl_list = $updater->getDlList();
//$temp_list = $updater->getTempList();
//d($diff_list);
if(isset($diff_list[1])) {

  $archive_name = $diff_list[1];

  // DOWNLOAD
   $updater->downloadLegiArchive($archive_name);

  // UNCOMPRESS
  $updater->unCompress($archive_name);

  $archive_name = $updater->getNameArchiveUncompressed($archive_name);
  // DIAGNOSTIC
  $manager->setArchive('update', $archive_name);
  $manager->makeArchiveDiagnosticUpdate();

  // UPDATE
   $manager->fullUpdateProcess($archive_name);

  // DELETE
  $path = $updater::$dl_path . '/temp/' . $archive_name;
  if(is_dir($path)) {
    $updater::deleteDir($path);
  }

}
else {
  $archive_name = "Aucune archive à traiter";
}


$file = dirname(__FILE__) . '/output.txt';
$data = "hello, it's " . date('d/m/Y H:i:s') . ". \n ";
$data .= "Traitement de l'archive : " . $archive_name . ". \n ";

file_put_contents($file, $data, FILE_APPEND);

?>

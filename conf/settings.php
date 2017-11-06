<?php

/**
 * Database configuration
 */
$config['database'] = array (
  'database' => 'legi_php',
  'username' => 'username',
  'password' => 'password',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '3306',
  'driver' => 'mysql',
);


/**
 * Changer les valeurs si dessous à votre convenance. 
 * legi_global_archive : Chemin pour atteindre l'archive legi global. A configurer suivant ou vous l'aurez installée.
 * legi_download_archives : Chemin pour atteindre les archives qui seront téléchargés régulièrement pour mettre à jour les données. Assurez vous simplement d'avoir un dossier temp dans le chemin indiqué (avec les droits d'écriture nécessaires)
 * Vous pouvez 	placer l'archive globale ou les petites archives de mises à jour au même endroit, ou bien dans des emplacements différents. 
 * 
 * codes_paths : Chemin depuis le début de l'archive legi jusqu'aux codes de lois en vigueur. N'a pas besoin d'être changé normalement.
 */
$config['paths'] = array(
  'legi_global_archive' => $_SERVER['DOCUMENT_ROOT'].'/legiglobal',
  'legi_download_archives' => $_SERVER['DOCUMENT_ROOT'].'/legi-php/downloads',
  'codes_path' => '/legi/global/code_et_TNC_en_vigueur/code_en_vigueur/LEGI/TEXT/00/00',
);



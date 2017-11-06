<?php
include 'conf/settings.php';
require 'autoload.php';
include 'conf/cidTexte_list.php';
global $config;

$manager = new LegiManager();
$updater = new Update();
$html_maker = new HTMLMaker();

if (isset($_POST['choix-download'])){
  $updater->downloadLegiArchive($_POST['choix-download']);
}

if (isset($_POST['choix-uncompress'])){
 $updater->unCompress($_POST['choix-uncompress']);
}

if (isset($_POST['choix-diagnostic'])){
  $manager->setArchive('update', $_POST['choix-diagnostic']);
  $manager->makeArchiveDiagnosticUpdate();
}

if (isset($_POST['choix-update'])){
    $manager->fullUpdateProcess($_POST['choix-update']);
}

if (isset($_POST['choix-delete'])){
  $path = $updater::$dl_path . '/temp/' . $_POST['choix-delete'];
  if(is_dir($path)) {
    $updater::deleteDir($path);
  }
}

$all_archives_updates = $manager->obtainAllArchivesDiagnosticsUpdates();
$dl_list = $updater->getDlList();
$temp_list = $updater->getTempList();
$diff_list = $updater->getDiffList();

?>
<html>
<head>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-default" role="navigation">
<ul class="nav nav-tabs">
    <li><a href="index.php">Insert</a></li>
    <li><a href="delete.php">Delete</a></li>
</ul>
</nav>
<div class="container-all-forms container">
    <div class="row">
        <!-- LISTE FORM : download, uncompress, run diagnostic, update db, delete -->
        <!--  FORM : download  --->
        <div class="container-form form-update col-md-3">
            <form id="update-form-download" method="post" action="update.php">
                <label class="form-title">Sélection : Download Archive</label>
                <select name="choix-download">
                  <?php
                  print($html_maker::makeSelect($diff_list));
                  ?>
                </select>
                <br>
                <input class="btn btn-success btn-sm" type="submit" value="Download">
            </form>
        </div>

        <!--  FORM : uncompress  --->
        <div class="container-form form-update col-md-3">
            <form id="update-form-uncompress" method="post" action="update.php">
                <label class="form-title">Sélection : Uncompress Archive</label>
                <select name="choix-uncompress">
                  <?php
                  print($html_maker::makeSelect($dl_list));
                  ?>
                </select>
                <br>
                <input class="btn btn-success btn-sm" type="submit" value="Uncompress">
            </form>
        </div>

        <!--  FORM : diagnostic  --->
        <div class="container-form form-update col-md-3">
            <form id="update-form-diagnostic" method="post" action="update.php">
                <label class="form-title">Sélection : Diagnostic Archive</label>
                <select name="choix-diagnostic">
                  <?php
                  print($html_maker::makeSelect($temp_list));
                  ?>
                </select>
                <br>
                <input class="btn btn-warning btn-sm" type="submit" value=" Run Diagnostic">
            </form>
        </div>

        <!--  FORM : update  --->
        <div class="container-form form-update col-md-3">
            <form id="update-form-update" method="post" action="update.php">
                <label class="form-title">Sélection : Update BDD</label>
                <select name="choix-update">
                  <?php
                  $archives_names = array_column($all_archives_updates, 'archive_name');
                  print($html_maker::makeSelect($archives_names));
                  ?>
                </select>
                <br>
                <input class="btn btn-primary btn-sm" type="submit" value="Update">
            </form>
        </div>

        <!--  FORM : delete  --->
        <div class="container-form form-update col-md-3">
            <form id="update-form-delete" method="post" action="update.php">
                <label class="form-title">Sélection : Delete Folder</label>
                <select name="choix-delete">
                  <?php
                  print($html_maker::makeSelect($temp_list));
                  ?>
                </select>
                <br>
                <input class="btn btn-danger btn-sm" type="submit" value="Delete">
            </form>
        </div>
    </div>
</div>

<h3>Liste des diagnostics incrits dans la BDD.</h3>
<table class="table table-responsive table-bordered table-hover">
    <thead>
    <tr>
        <th>Nom de l'archive</th>
        <th>Status</th>
        <th>Date d'enregistrement en BDD</th>
        <th>Codes Updates </th>
        <th>Codes enregistrées en BDD</th>
        <th>Codes dans l'archive</th>
    </tr>
    </thead>
    <tbody>
    <?php

    foreach ($all_archives_updates as $key=>$archive) {
      echo '<tr>';
      echo '<td>' . $archive['archive_name'] .'</td>';
      echo '<td>' . $archive['status'] .'</td>';
      echo '<td>' . $archive['created'] .'</td>';

      echo '<td>';
      foreach ($archive['codes_need_update'] as $code) {
        echo '<span>' . $code . '</span><br>';
      }
      echo '</td>';
      echo '<td>';
      foreach ($archive['codes_in_db'] as $code) {
        echo '<span>' . $code['cid_full'] . '</span><br>';
      }
      echo '</td>';
      echo '<td>' . count($archive['codes_in_archive']) .'</td>';
      echo '</tr>';
    }

    ?>
    </tbody>
</table>

<script src="assets/jquery-3.2.1.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
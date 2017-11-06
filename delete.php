<?php
include 'conf/settings.php';
require 'autoload.php';
include 'conf/cidTexte_list.php';
$manager = new LegiManager();
$list_codes = $manager->getListCodes();

if (isset($_POST['choix-code'])){

  $manager = new LegiManager();

//  $code_infos = '';
//  foreach($cidTexte_list as $key => $code) {
//      if($code['code_legiphp'] == $_POST['choix-code']) {
//          $code_infos = $cidTexte_list[$key];
//      }
//  }

  $manager->deleteCode($_POST['choix-code']);
}
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
        <li><a href="update.php">Update</a></li>
    </ul>
</nav>
<div class="container">
    <div class="row">
        <div class="container-form form-update col-md-3">
            <form id="legi-form" method="post" action="delete.php">
              <select name="choix-code">;
              <?php

              foreach($list_codes as $code_values)
              {
                echo '<option value="'. $code_values['cID'] .'">' . $code_values['titrefull']. '</option>';
              }
              ?>
                </select>
                <br>
                <input type="submit" class="btn btn-success btn-sm" value="Effacez ce code de loi">
            </form>
        </div>
    </div>
</div>
<script src="assets/jquery-3.2.1.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>

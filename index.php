<?php
include 'conf/settings.php';
require 'autoload.php';
include 'conf/cidTexte_list.php';
//d($cidTexte_list);

//$nav = new Navigator();
//$nav->setPathToGlobalArchive();
//$nav->setPathToCode('LEGITEXT000006074234');
//$path_article = $nav->goToArticle('LEGIARTI000033052200');
//d($path_article);
//
//$filereader = new FileReader();
//$filecontent = $filereader->openFile($path_article);
//d($filecontent);
//$file_values = $filereader->setArticlesValuesFromXML($filecontent);
//d($file_values);

if (isset($_POST['choix-code'])){

 //$code_infos = $cidTexte_list[$_POST['choix-code']];


  $manager = new LegiManager();
  $manager->setArchive('global');
  $manager->setCode($_POST['choix-code']);
  $manager->initCode();
  $manager->launchFullCode();

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
        <li><a href="update.php">Update</a></li>
        <li><a href="delete.php">Delete</a></li>
    </ul>
</nav>
<div class="container">
    <div class="row">
        <div class="container-form form-update col-md-3">
            <form id="legi-form" method="post" action="index.php">
                <select name="choix-code">
                  <?php
                  foreach($cidTexte_list as $code_id => $code_infos)
                  {
                    echo '<option value="'. $code_infos['cIDTexte'] .'">' .$code_infos['full_name']. '</option>';
                  }
                  ?>
                </select>
                <br><br>
                <input type="submit" class="btn btn-success btn-sm" value="Go ninja go !!!">
            </form>
        </div>
    </div>
</div>
<script src="assets/jquery-3.2.1.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>

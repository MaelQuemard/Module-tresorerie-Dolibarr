<?php 
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';                  // to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';            // to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../dolibarr/htdocs/main.inc.php';     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../../dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (! $res) die("Include of main fails");
require_once 'class/connect.php';
$connect = new connect($dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user ,$dolibarr_main_db_pass);
$link = $connect->link();
    if (isset($_GET['nom'])) {
        $sql = "SELECT * FROM llx_tresorerie LIMIT 1";
        $res = mysqli_query($link, $sql) or die (mysqli_error($link));
        $nb = mysqli_num_fields($res);
        $string = "";
        $ex = "";
        while ($data = mysqli_fetch_row($res)) {
            for ($i=0; $i < $nb; $i++) {
                $finfo = mysqli_fetch_field_direct($res, $i);
                $string .= $finfo->name.",";
                if ($finfo->name == "rowid") {
                    $ex .= "\n,";
                }elseif ($finfo->name == "type") {
                    $ex .= "reel,";
                }
                elseif ($finfo->name == "date") {
                    $ex .= date("Y-m-d").",";
                }
                else{
                    $ex .= "2000,";
                }
            }
        }
        $_GET['nom'] = str_replace("/", "-", $_GET['nom']);
        $file = $_GET['nom'].".csv";
        $f = fopen($file, "w");
        fwrite($f, $string);
        fwrite($f, $ex);
        fclose($f);
    }
$url = ($file);
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'. basename($url) .'";');
@readfile($url) OR die();
?>
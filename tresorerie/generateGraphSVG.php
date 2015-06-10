<?php
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../dolibarr/htdocs/main.inc.php';
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../../dolibarr/htdocs/main.inc.php';
if (! $res) die("Include of main fails");
require_once 'class/tresorerie.php';
require_once 'class/connect.php';
include('phpGraph/phpGraph.php');
$connect = new connect($dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user ,$dolibarr_main_db_pass);
$link = $connect->link();
$tresorerie = new tresorerie($link);
$ca_N_moins_1 = $tresorerie->getCA_N_moins_1();
$ca_N = $tresorerie->getCA_N();
$tab = array($ca_N, $ca_N_moins_1);
$taux = $tresorerie->getTaux();
$G = new phpGraph();
$options = array(
	'steps' => 10000,
	'multi'=>true,
	'responsive' => true,
	'filled'=>false,
	'stroke' => array(
	    '0'=>'red',
	    '1'=>'blue'
	),
	'legends' => array(
	    '0'=>'chiffre d\'affaire N (HT)',
	    '1'=>'chiffre d\'affaire N-1 (HT)',
	),
	'title' => 'Comparatif du chiffre d\'affaire de N-1 - N'
);
$svg = $G->draw($tab, $options);
echo $G->putInCache($svg,$outputName='Comparatif_CA',$outputDir='');
$url = ("Comparatif_CA.svg");
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'. basename($url) .'";');
@readfile($url) OR die();
?>
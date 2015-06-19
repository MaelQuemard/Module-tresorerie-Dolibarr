<?php
/**
 *	This file create the graph to svg
 */

/**
 *	This is used for create the graph on svg
 *
 *	@filesource /htdocs/tresorerie/generateGraph2SVG.php
 *	@package /
 *	@licence http://www.gnu.org/licenses/ GPL
 *	@version Version 1.0
 *	@author Maël Quémard
 */

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
$taux = $tresorerie->getTaux();
if (isset($_GET['re'])) {
	$cumul_charge = $tresorerie->cumul_charge_2($taux, $_GET['re']);
	$cumul_charge_prev = $tresorerie->cumul_charge_prev_2($taux, $_GET['re']);
}
else{
	$cumul_charge = $tresorerie->cumul_charge_2($taux);
	$cumul_charge_prev = $tresorerie->cumul_charge_prev_2($taux);
}
$max = 0;
$min = 1000000;
foreach ($cumul_charge as $value) {
	$value = intval($value);
	if ($max < $value) {
		$max = $value;
	}
	if ($min > $value) {
		$min = $value;
	}
}
if ($max == 0) {
	$max = 10;
}
if ($min == 1000000) {
	$min = 3;
}
if (empty($cumul_charge)) {
	$tab = array($cumul_charge_prev);	
}
elseif (empty($cumul_charge_prev)) {
	$tab = array($cumul_charge);
}
else{
	$tab = array($cumul_charge, $cumul_charge_prev);
}
if (!empty($cumul_charge_prev) || !empty($cumul_charge)) {
	$G = new phpGraph();
	$options = array(
		'steps' => ($max-$min)/10,
		'width' => 1000,
	 	'multi'=>true,
	 	'type' => 'bar',
	  	'filled'=> true,
	  	'stroke' => array(
	    	'0'=>'red',
	        '1'=>'blue'
	    ),
	  	'legends' => array(
	        '0'=>'cumul charge réel',
	        '1'=>'cumul charge prévisionnel'
	    ),
	  	'title' => 'Comparatif des charges réel / prévisionnel - Cumul d\'une année à partir du mois en cours ou du mois selectionné'
	);
	$svg = $G->draw($tab, $options);
	echo $G->putInCache($svg,$outputName='cumul_charge',$outputDir='');
}
$url = ("cumul_charge.svg");
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'. basename($url) .'";');
@readfile($url) OR die();
?>
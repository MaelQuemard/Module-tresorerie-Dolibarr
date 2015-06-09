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
//We call an instance of phpGraph() class
/*$ca_N_moins_1 = $tresorerie->getCA_N_moins_1();
$ca_N = $tresorerie->getCA_N();
$tab = array($ca_N, $ca_N_moins_1);*/
$taux = $tresorerie->getTaux();
/*$G = new phpGraph();
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
);*/
if (isset($_GET['re'])) {
			$cumul_charge = $tresorerie->cumul_charge_2($taux, $_GET['re']);
			$cumul_charge_prev = $tresorerie->cumul_charge_prev_2($taux, $_GET['re']);
		}
		else{
			$cumul_charge = $tresorerie->cumul_charge_2($taux);
			$cumul_charge_prev = $tresorerie->cumul_charge_prev_2($taux);
		}
		$tab = array($cumul_charge, $cumul_charge_prev);
		$G = new phpGraph();
		$options = array(
			'steps' => 1000,
		 	'multi'=>true,
		 	'width' => 1100,
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
		  	'title' => 'Comparatif des charges réel - prévisionnel'
		);
$svg = $G->draw($tab, $options);
echo $G->putInCache($svg,$outputName='svg',$outputDir='');
$url = ("svg.svg");
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'. basename($url) .'";');
@readfile($url) OR die();
/*
		if (isset($_GET['re'])) {
			$cumul_charge = $tresorerie->cumul_charge_2($taux, $_GET['re']);
			$cumul_charge_prev = $tresorerie->cumul_charge_prev_2($taux, $_GET['re']);
		}
		else{
			$cumul_charge = $tresorerie->cumul_charge_2($taux);
			$cumul_charge_prev = $tresorerie->cumul_charge_prev_2($taux);
		}
		$tab = array($cumul_charge, $cumul_charge_prev);
		$G = new phpGraph();
		$options = array(
			'steps' => 1000,
		 	'multi'=>true,
		 	'width' => 1100,
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
		  	'title' => 'Comparatif des charges réel - prévisionnel'
		);
		$G->draw($tab, $options);*/
?>
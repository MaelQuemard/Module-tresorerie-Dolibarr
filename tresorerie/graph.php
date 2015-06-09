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
llxHeader('','Graphique' ,'');
?>
<table class="notopnoleftnoright" border="0" style="margin-bottom: 2px;">
    <tbody>
        <tr>
            <td class="nobordernopadding hideonsmartphone" width="40" valign="middle" align="left">
                <img border="0" title="" alt="" src="/dolibarr/htdocs/theme/eldy/img/title.png"></img>
            </td>
            <td class="nobordernopadding">
                <div class="titre">Tableau de bord - Graphique</div>
            </td>
        </tr>
    </tbody>
</table>
 <td class="nobordernopadding">
                	<?php
                	if (isset($_GET['re'])) {
                		echo "<form action='generateGraphSVG.php?re=".$_GET['re']."' method='post' target='_blank'>";
                		echo "<a target='_blank' href='generateGraphSVG.php?re=".$_GET['re']."'><img src='pdf.jpeg' alt='Télécharger le pdf' style='width:65%;'></img></a>";
                	}
                	else{
                		echo "<form action='generateGraphSVG.php' method='post' target='_blank'>";
                		echo "<a target='_blank' href='generateGraphSVG.php'><img src='pdf.jpeg' alt='Télécharger le pdf' style='width:65%;'></img></a>";
                	}
                ?>
                </form>
                </td>
<div class="fichecenter">
	<div class="fichethirdleft">
<?php
$connect = new connect($dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user ,$dolibarr_main_db_pass);
$link = $connect->link();
$tresorerie = new tresorerie($link);
//We call an instance of phpGraph() class
$ca_N_moins_1 = $tresorerie->getCA_N_moins_1();
$ca_N = $tresorerie->getCA_N();
$tab = array($ca_N, $ca_N_moins_1);
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
echo $G->draw($tab, $options);
?></div>
	<div class="fichetwothirdright">
	<div class="ficheaddleft">
	<?php
		$taux = $tresorerie->getTaux();
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
		?>
		<b>Pour voir le nom des charges corespondant au numéro cliquer sur le graphique</b>
		<form action="#" method="get">
            Date de début pour le graphique ci-dessous : 
            <input id="re" type="text" onchange="dpChangeDay('re','dd/MM/yyyy'); " value="" maxlength="11" size="9" name="re" placeholder="jj/mm/aaaa"></input>
            <button id="reButton" class="dpInvisibleButtons" onclick="showDP('/dolibarr/htdocs/core/','re','dd/MM/yyyy','fr_FR');" type="button">
                <img class="datecallink" border="0" title="Sélectionnez une date" alt="Sélectionnez une date" src="/dolibarr/htdocs/theme/eldy/img/object_calendarday.png"></img>
            </button>
            <input type="submit" name="rechercher" value="Rechercher">
            </form>
            <a href='../compta/bank/categ.php' style='text-decoration:none'>
		<?php
		echo $G->draw($tab, $options);
		echo "</a>";
	?>
		</div>
	</div>
</div>
<?php
llxFooter();
?>
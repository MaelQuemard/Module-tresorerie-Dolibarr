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
?>
<form action='generateGraphSVG.php?re=".$_GET['re']."' method='post'>
	<input type="submit" value="Télécharger le graphique ci-dessous"></input>
</form>
<?php
echo $G->draw($tab, $options);
?>
</div>
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
			//	print_r($tab);
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
				<?php echo "<form action='generateGraph2SVG.php?re=".$_GET['re']."' method='post'>";
					echo	"<input type=\"submit\" value=\"Télécharger le graphique ci-dessous\"></input>";
					echo "</form>";
				?>
	            <a href='../compta/bank/categ.php' style='text-decoration:none'>
			<?php
			echo $G->draw($tab, $options);
			echo "</a>";
		}
		else{
			echo "<b>Le graphique de comparatif des charges réel et prévisionnel n'a aucune donnée donc ne peux s'afficher, il doit comporter au minimum deux charges fixes.";
		}
	?>
		</div>
	</div>
</div>
<?php
llxFooter();
?>
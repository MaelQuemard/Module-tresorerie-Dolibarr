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
$tresorerie = new tresorerie($db, $link);
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
?>
	</div>
	<div class="fichethirdright">
	<?php ?>
	</div>
</div>
<?php
llxFooter();
?>
<?php
/* Copyright (C) 2015	Mael Quemard	<quemard.mael@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/treorerie/tdb.php
 *	\ingroup    tresorerie
 *	\brief      Show the tresorerie
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';					// to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../dolibarr/htdocs/main.inc.php';     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../../dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (! $res) die("Include of main fails");

// Change this following line to use the correct relative path from htdocs
require_once 'class/tresorerie.php';
require_once 'class/connect.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
dol_include_once('/module/class/skeleton_class.class.php');
// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");

// Get parameters
$id			= GETPOST('id','int');
$action		= GETPOST('action','alpha');
$myparam	= GETPOST('myparam','alpha');

// Protection if external user
if ($user->societe_id > 0)
{
	//accessforbidden();
}

/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/

if ($action == 'add')
{
	$object=new Skeleton_Class($db);
	$object->prop1=$_POST["field1"];
	$object->prop2=$_POST["field2"];
	$result=$object->create($user);
	if ($result > 0)
	{
		// Creation OK
	}
	{
		// Creation KO
		$mesg=$object->error;
	}
}

/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('','Tableau de bord','');

$form=new Form($db);
// Put here content of your page
$connect = new connect($dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user ,$dolibarr_main_db_pass);
$link = $connect->link();
$moisM = date("m");
$annee = date("Y");

$tresorerie = new tresorerie($db, $link);

$nb_lignes = $tresorerie->getNbLignes();
$categorie = $tresorerie->getCategorie();
$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
$replace = array("");
$categorie2 = array();
for ($i=0; $i <= $nb_lignes; $i++) { 
	$categorie2[$i] = str_replace($search, $replace, $categorie[$i]);
	for ($j=0; $j < 24; $j++) {	
		if (isset($_POST["$categorie2[$i];$i;$j"])) {
			$tresorerie->setPrevisionel($_POST["$categorie2[$i];$i;$j"]);
		}
		if (isset($_POST["achat;$j"])) {
			$tresorerie->setPrevisionel($_POST["achat;$j"]);
		}
		if (isset($_POST["soldeDebut;$j"])) {
			$tresorerie->setPrevisionel($_POST["soldeDebut;$j"]);
		}
		if (isset($_POST["soldeCourant;$j"])) {
			$tresorerie->setPrevisionel($_POST["soldeCourant;$j"]);
		}
		if (isset($_POST["CA;$j"])) {
			$tresorerie->setPrevisionel($_POST["CA;$j"]);
		}
	}
}
if (isset($_GET['synchro'])) {
	$tresorerie->up_tresorerie_charge_fixe($categorie);
	$solde = $tresorerie->getSolde();
	$tCharge = $tresorerie->getTotalCharge();
	$mCateg = $tresorerie->getMontantCategorie();
	$ca = $tresorerie->getCA();
	$achat = $tresorerie->getAchat();
	$tresorerie->Upsert($tCharge, $solde, $ca, $achat, $mCateg, $categorie);
}


$taux = $tresorerie->getTaux();
if(isset($_GET['re'])){
	$tresoPrev = $tresorerie->getTresorerie_Prev_HT($taux, $_GET['re']);
	$tresoReel =$tresorerie->getTresorerie_Reel_HT($taux, $_GET['re']);
	$charge_total = $tresorerie->getCharge($categorie, $taux, $_GET['re']);
	$charge_total_prev = $tresorerie->getChargePrev($categorie, $taux, $_GET['re']);
	if (isset($_GET['synchro'])) {
		$tresorerie->calcul_solde_tresorerie_prev($categorie, $taux, $_GET['re']);
	}
	$pourcentage_ca_par_ca_n_moins_1 = $tresorerie->calcul_pourcentage_ca_par_ca_n_moins_1($_GET['re']);
	$taux_de_marge = $tresorerie->calcul_taux_de_marge($_GET['re']);
	$cumul = $tresorerie->calcul_ca_cumule($_GET['re']);
	$tva_collecte = $tresorerie->getTVACollecte($_GET['re']);
	$tva_due = $tresorerie->getTVADeductible($_GET['re']);
	$tva_payer = $tresorerie->getTVAPayer($_GET['re']);
	$cumul_CA = $tresorerie->cumul_CA($_GET['re']);
	$cumul_achat = $tresorerie->cumul_achat($_GET['re']);
	$cumul_Charge = $tresorerie->cumul_Charge($taux, $_GET['re']);
	$cumul_solde = $tresorerie->cumul_solde_tresorerie($_GET['re']);
	$cumul_CA_prev = $tresorerie->cumul_CA_prev($_GET['re']);
	$cumul_achat_prev = $tresorerie->cumul_achat_prev($_GET['re']);
	$cumul_Charge_prev = $tresorerie->cumul_Charge_prev($taux, $_GET['re']);
	$cumul_solde_prev = $tresorerie->cumul_solde_tresorerie_prev($_GET['re']);
	$date_test = explode("/", $_GET['re']);
	$date_test[1]--;
}
else{
	$tresoPrev = $tresorerie->getTresorerie_Prev_HT($taux);
	$tresoReel =$tresorerie->getTresorerie_Reel_HT($taux);
	$charge_total = $tresorerie->getCharge($categorie, $taux);
	$charge_total_prev = $tresorerie->getChargePrev($categorie, $taux);
	if (isset($_GET['synchro'])) {
		$tresorerie->calcul_solde_tresorerie_prev($categorie, $taux);
	}
	$pourcentage_ca_par_ca_n_moins_1 = $tresorerie->calcul_pourcentage_ca_par_ca_n_moins_1();
	$taux_de_marge = $tresorerie->calcul_taux_de_marge();
	$cumul = $tresorerie->calcul_ca_cumule();
	$tva_collecte = $tresorerie->getTVACollecte();
	$tva_due = $tresorerie->getTVADeductible();
	$tva_payer = $tresorerie->getTVAPayer();
	$cumul_CA = $tresorerie->cumul_CA();
	$cumul_achat = $tresorerie->cumul_achat();
	$cumul_Charge = $tresorerie->cumul_Charge($taux);
	$cumul_solde = $tresorerie->cumul_solde_tresorerie();
	$cumul_CA_prev = $tresorerie->cumul_CA_prev();
	$cumul_achat_prev = $tresorerie->cumul_achat_prev();
	$cumul_Charge_prev = $tresorerie->cumul_Charge_prev($taux);
	$cumul_solde_prev = $tresorerie->cumul_solde_tresorerie_prev();
	$date_test = explode("-", date("d-m-Y"));
	$date_test[1]--;
}
if (isset($_GET['synchro'])) {
	$tresorerie->calcul_reel_futur();
	$tresorerie->calcul_solde_tresorerie_reel_futur($categorie);
}

$tva_solde = $tresorerie->calculSoldeTVA($tva_due, $tva_collecte, $tva_payer);
$encoursFourn = $tresorerie->getEncoursFournisseur();
$cumul_total_charge = $tresorerie->cumul_total_charge($charge_total);
$cumul_total_charge_prev = $tresorerie->cumul_total_charge_prev($charge_total_prev);
$pourcentage_cumul_charge = $tresorerie->pourcentage_cumul_charge($cumul_Charge, $cumul_Charge_prev);
$pourcentage_cumul_achat = $tresorerie->pourcentage_cumul_achat($cumul_achat, $cumul_achat_prev);
$pourcentage_cumul_ca = $tresorerie->pourcentage_cumul_ca($cumul_CA, $cumul_CA_prev);
$pourcentage_cumul_total_charge = $tresorerie->pourcentage_cumul_total_charge($cumul_total_charge, $cumul_total_charge_prev);
$pourcentage_cumul_solde_tresorerie = $tresorerie->pourcentage_cumul_solde_tresorerie($cumul_solde, $cumul_solde_prev);
$nom = $tresorerie->getNomEntreprise();

$mois = array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");
$moisPrecedent = array();
?>
	<script type="text/javascript" src="js/script.js"></script>
	<script type="text/javascript" src="js/coloration.js"></script>
    <table class="notopnoleftnoright" border="0" style="margin-bottom: 2px;">
        <tbody>
            <tr>
                <td class="nobordernopadding hideonsmartphone" width="40" valign="middle" align="left">
                    <img border="0" title="" alt="" src="/dolibarr/htdocs/theme/eldy/img/title.png"></img>
                </td>
                <td class="nobordernopadding">
                    <div class="titre">Tableau de bord</div>
                </td>
                <td class="nobordernopadding">
                <form action="#">
                	<input type="hidden" name="account" value="1"></input>
                	<input type="submit" name="synchro" value="Sychroniser"></input>
                </form>
                </td>
                <td class="nobordernopadding">
                	<?php
                	if (isset($_GET['re'])) {
                		echo "<form action='generatePDF.php?re=".$_GET['re']."' method='post' target='_blank'>";
                		echo "<a target='_blank' href='generatePDF.php?re=".$_GET['re']."'><img src='pdf.jpeg' alt='Télécharger le pdf' style='width:65%;'></img></a>";
                	}
                	else{
                		echo "<form action='generatePDF.php' method='post' target='_blank'>";
                		echo "<a target='_blank' href='generatePDF.php'><img src='pdf.jpeg' alt='Télécharger le pdf' style='width:65%;'></img></a>";
                	}
                ?>
                </form>
                </td>
            </tr>
        </tbody>
    </table>
     <div class="fichecenter">
        <div class="fichethirdleft">
            <form action="#" method="get">
            Date de début : 
            <input id="re" type="text" onchange="dpChangeDay('re','dd/MM/yyyy'); " value="" maxlength="11" size="9" name="re" placeholder="jj/mm/aaaa"></input>
            <button id="reButton" class="dpInvisibleButtons" onclick="showDP('/dolibarr/htdocs/core/','re','dd/MM/yyyy','fr_FR');" type="button">
                <img class="datecallink" border="0" title="Sélectionnez une date" alt="Sélectionnez une date" src="/dolibarr/htdocs/theme/eldy/img/object_calendarday.png"></img>
            </button>
            <input type="hidden" name="account" value="1"></input>
            <input type="submit" name="rechercher" value="Rechercher">
            </form>
            <b>*Il est préferable de choisir le premier de chaque mois</b>
            <form action="#" method="post">
                <table id="tableau" class="border nohover" width="100%">
                    <tbody>
                        <tr class="liste_titre">
                            <td class="center">
                                <?php echo $nom; ?>
                            </td>
                            <?php
                                if (isset($_GET['re'])) {
                                    $date_Debut = explode("/" ,$_GET['re']);
                                    $x = 0;
                                    foreach ($mois as $key => $value) {
                                        if ($date_Debut[1] <= $key+1) {
                                            ?>
                                                <td class="center" colspan="2"><b><?php echo $value." - ".$date_Debut[2]; ?></b></td>
                                            <?php
                                        }
                                        else {
                                            $moisPrecedent[] = $x;
                                        }
                                        $x++;
                                    }
                                    for ($i=0; $i <= sizeof($moisPrecedent)-1; $i++) { 
                                        ?>
                                            <td class="center" colspan="2"><b><?php echo $mois[$i]." - ".(intval($date_Debut[2])+1); ?></b></td>
                                        <?php
                                    }
                                    ?> <td class="center" colspan="2"><b>Cumul</b></td>
                                    <td class="center" colspan="2" style="padding:0px 10px 0px 10px;"><b>%</b></td> <?php
                                }
                                else{
                                    for ($i=0; $i <= 12; $i++) {
                                        if ($i >= $moisM) {
                                        ?>
                                            <td class="center" colspan="2"><b><?php echo $mois[$i-1]." - ".$annee; ?></b></td>
                                        <?php
                                        }
                                        else {
                                            $moisPrecedent[] = $i;
                                        }
                                    }
                                    for ($i=0; $i < sizeof($moisPrecedent)-1; $i++) { 
                                        ?>
                                            <td class="center" colspan="2"><b><?php echo $mois[$i]." - ".(intval($annee)+1); ?></b></td>
                                        <?php
                                    }
                                    ?> <td class="center" colspan="2"><b>Cumul</b></td>
                                    <td class="center" colspan="2" style="padding:0px 10px 0px 10px;"><b>%</b></td> <?php
                                }
                            ?>
                        </tr>
                        <tr>
                            <td>
                            </td>
                            <?php
                                for ($i=0; $i < 13 ; $i++) { 
                                    ?>
                                <td>
                                    Prévisonnel
                                </td>
                                <td style="padding:0px 20px 0px 20px">
                                	<b>Réel</b>
                                </td>
                                    <?php
                                }
                            ?>
                            	<td></td>
                        </tr>
                        <tr class="impair">
                            <td class="right"><b>Solde inital (TTC)</b></td>
                            <?php for ($j=0; $j < 24; $j++) {
	                            	for($i=0; $i< 24; $i++){
		                        		foreach($tresoPrev as $truc => $key){
		                    				foreach($key as $row =>$value){
		                    					$val = $truc*2;
		                                    	$val2 = $j;
												if($row == "soldeDebut" && $val==$val2 && $truc<12){
	                    							if ($date_test[1] >= 12) {
														$date_test[1]=1;
														$date_test[2]++;
													}
													else{
														$date_test[1]++;
													}
													if ($j<=23) {
														if($value!=0){
			                    							echo "<td>"./*round(*/price($value)/*, 2)*/."</td>";
			                    						}
			                    						else{
			                    							echo "<td></td>";
			                    						}
													}
	                    							$j++;
	                    						}
		                    				}
		                    			}
		                    			foreach($tresoReel as $truc => $key){
		                    				foreach($key as $row =>$value){
		                    					$val = ($truc*2)-1;
		                                        $val2 = $j-2;
		                    					if($row == "soldeDebut" && $val==$val2 && $truc<12){
		                    						echo "<td><b>".price($value)/*round($value, 2)*/."</b></td>";
		                    						$j++;
		                    					}
		                    				}
		                    			}
		                    		}
	                    			if($j%2==0){
	                    				if ($date_test[1] >= 12) {
											$date_test[1]=1;
											$date_test[2]++;
										}
										else{
											$date_test[1]++;
			                            }
			                            if($j<23){
											echo "<td class=\"prev\" data-id=\"soldeDebut;$date_test[2]-$date_test[1]-$date_test[0]\"><input type=\"hidden\" name=\"soldeDebut;$j\"></input></td>";
	                       				}
	                       			}
	                       			else{
	                       				echo "<td></td>";
	                       			}	                       			
	                    		}
	                    		if(isset($_GET['re'])){
	                            	$date_test = explode("/", $_GET['re']);
	                            	$date_test[1]--;
	                            }
	                            else{
	                            	$date_test = explode("-", date("d-m-Y"));
	                            	$date_test[1]--;
	                            }
                        	?>
                        	<td></td>
                        	<td></td>
                        	<td></td>
                        </tr>
                        <tr>
                            <td class="right"><b>Chiffre d'affaires (HT)</b></td>
                            <?php for ($j=0; $j < 25; $j++) {
	                            	for($i=0; $i< 25; $i++){
		                        		foreach($tresoPrev as $truc => $key){
		                    				foreach($key as $row =>$value){
		                    					$val = $truc*2;
		                                    	$val2 = $j;
												if($row == "CA" && $val==$val2 && $truc<12){
	                    							if ($date_test[1] >= 12) {
														$date_test[1]=1;
														$date_test[2]++;
													}
													else{
														$date_test[1]++;
													}
	                    							echo "<td class=\"prev\" data-id=\"$row;$date_test[2]-$date_test[1]-$date_test[0]\"><input type=\"hidden\" name=\"$row;$j\"></input>".price($value)/*round($value, 2)*/."</td>";
	                    							$j++;
	                    						}
		                    				}
		                    			}
		                    			foreach($tresoReel as $truc => $key){
		                    				foreach($key as $row =>$value){
		                    					$val = ($truc*2)-1;
		                                        $val2 = $j-2;
		                    					if($row == "CA" && $val==$val2 &&$truc<12){
		                    						echo "<td><b>".price(round($value*(100/(20+100)), 2))."</b></td>";
		                    						$j++;
		                    					}
		                    				}
		                    			}
		                    		}
	                    			if($j%2==0){
	                    				if ($date_test[1] >= 12) {
											$date_test[1]=1;
											$date_test[2]++;
										}
										else{
											$date_test[1]++;
			                            }
			                            if ($j<23) {
			                            	echo "<td class=\"prev\" data-id=\"CA;$date_test[2]-$date_test[1]-$date_test[0]\"><input type=\"hidden\" name=\"CA;$j\"></input></td>";
			                            }
			                            else{
			                            	echo "<td>".price($cumul_CA_prev)."</td>";
	                            			echo "<td><b>".price(round($cumul_CA*(100/(20+100))),2)."</b></td>";
			                            }									
	                       			}
	                       			else{
	                       				echo "<td></td>";
	                       			}	                       			
	                    		}
	                    		if(isset($_GET['re'])){
	                            	$date_test = explode("/", $_GET['re']);
	                            	$date_test[1]--;
	                            }
	                            else{
	                            	$date_test = explode("-", date("d-m-Y"));
	                            	$date_test[1]--;
	                            }
	                            if ($pourcentage_cumul_ca != 0) {
	                            	echo "<td style='color:red'>".round($pourcentage_cumul_ca,2)."</td>";
	                            }
	                            else{
	                            	echo "<td style='color:green'>".round($pourcentage_cumul_ca,2)."</td>";
	                            }
                        	?>
                        </tr>
                        <tr class="liste_titre">
                            <td><b>Charge</b></td>
                            <?php for ($i=0; $i < 27; $i++) {?><td></td> <?php } ?>
                        </tr>
                        <tr class="liste_titre" style=" opacity: 0.7;">
                            <td><b>Achats (HT)</b></td>
                             <?php for ($i=0; $i < 27; $i++) {?><td></td> <?php } ?>
                        </tr>
                        <tr>
                            <td class="right"><b>Total des achats</b></td>
                            <?php 
                        		for ($j=0; $j < 25; $j++) {
	                            	for($i=0; $i< 25; $i++){
		                        		foreach($tresoPrev as $truc => $key){
		                    				foreach($key as $row =>$value){
		                    					$val = $truc*2;
		                                    	$val2 = $j;
												if($row == "achat" && $val==$val2 && $truc<12){
	                    							if ($date_test[1] >= 12) {
														$date_test[1]=1;
														$date_test[2]++;
													}
													else{
														$date_test[1]++;
													}
	                    							echo "<td class=\"prev\" data-id=\"$row;$date_test[2]-$date_test[1]-$date_test[0]\"><input type=\"hidden\" name=\"$row;$j\"></input>".price($value)/*round($value, 2)*/."</td>";
	                    							$j++;
	                    						}
		                    				}
		                    			}
		                    			foreach($tresoReel as $truc => $key){
		                    				foreach($key as $row =>$value){
		                    					$val = ($truc*2)-1;
		                                        $val2 = $j-2;
		                    					if($row == "achat" && $val==$val2 && $truc<12){
		                    						echo "<td><b>".price(round($value*(100/(20+100)), 2))."</b></td>";
		                    						$j++;
		                    					}
		                    				}
		                    			}
		                    		}
	                    			if($j%2==0){
	                    				if ($date_test[1] >= 12) {
											$date_test[1]=1;
											$date_test[2]++;
										}
										else{
											$date_test[1]++;
			                            }
			                            if ($j<23) {
			                            	echo "<td class=\"prev\" data-id=\"achat;$date_test[2]-$date_test[1]-$date_test[0]\"><input type=\"hidden\" name=\"achat;$j\"></input></td>";
			                            }
			                            else{
			                            	echo "<td>".price(round($cumul_achat_prev, 2))."</td>";
	                           				echo "<td><b>".price(round($cumul_achat*(100/(20+100)), 2))."</b></td>";
			                            }
	                       			}
	                       			else{
	                       				echo "<td></td>";
	                       			}	                       			
	                    		}
	                    		if(isset($_GET['re'])){
	                            	$date_test = explode("/", $_GET['re']);
	                            	$date_test[1]--;
	                            }
	                            else{
	                            	$date_test = explode("-", date("d-m-Y"));
	                            	$date_test[1]--;
	                            }
	                            if ($pourcentage_cumul_achat != 0) {
	                            	echo "<td style='color:red'>".round($pourcentage_cumul_achat,2)."</td>";
	                            }
	                            else{
	                            	echo "<td style='color:green'>".round($pourcentage_cumul_achat,2)."</td>";
	                            }
                            ?>
                        </tr>
                        <tr class="liste_titre" style=" opacity: 0.7;">
                            <td><b>Charges fixes (HT)</b></td>
                             <?php for ($i=0; $i < 27; $i++) {?><td></td> <?php } ?>
                        </tr>
                        <?php
                            $index = 0;
                            $class = array("pair", "impair");
                            $search = array(',', '-', '(', ')', ' ', '/', "'", '+');
							$replace = array("");
							$j_passe = 0;
							for ($i=0; $i < sizeof($categorie); $i++) { 
								($i%2==0) ? $t = $class[0] : $t = $class[1];
								?>
                                    <tr <?php echo "class=\"$t\""; ?>>
                                    <?php
                                   		for ($j=0; $j <= 25; $j++) { 
                                   			if ($j==0) {
                                   				?><td class="right"><?php echo $categorie[$i]; ?></td><?php
                                   			}
                                   			$categorie[$i] = str_replace($search, $replace, $categorie[$i]);
	                                        if ($j <24) {
	                                        	for ($x=0; $x < 24; $x++) {
	                                        		foreach ($tresoPrev as $key => $ligne_par_mois) {
		                                        		$val = $key*2;
				                                    	$val2 = $j;
		                                        		foreach ($ligne_par_mois as $categTTC => $valueTTC) {
															if ($categTTC == $categorie[$i] && $val==$val2) {
																if ($date_test[1] >= 12) {
																	$date_test[1]=1;
																	$date_test[2]++;
																}
																else{
																	$date_test[1]++;
																}
																echo "<td class=\"prev\" data-id=\"$categorie[$i];$date_test[2]-$date_test[1]-$date_test[0]\"><input type=\"hidden\" name=\"$categorie[$i];$i;$j\"></input>".str_replace("-", "",price($valueTTC)/*round($valueTTC, 2)*/)."</td>";
																
																$j++;
															}
														}
		                                        	}
		                                        	foreach ($tresoReel as $key => $ligne_par_mois) {
		                                        		$val = ($key*2)-1;
		                                        		$val2 = $j-2;
		                                        		foreach ($ligne_par_mois as $categTTC => $valueTTC) {
															if ($categTTC == $categorie[$i] && $val==$val2) {
																echo "<td><b>".str_replace("-", "",price($valueTTC))/*round($valueTTC, 2))*/."</b></td>";
																$j_passe = $j;
																$j++;
															}
														}
		                                        	}
	                               				}
	                                   			if($j%2==0){
	                                   				if ($date_test[1] >= 12) {
														$date_test[1]=1;
														$date_test[2]++;
													}
													else{
														$date_test[1]++;
						                            }
						                            if ($j<23) {
						                            	echo "<td class=\"prev\" data-id=\"$categorie[$i];$date_test[2]-$date_test[1]-$date_test[0]\"><input type=\"hidden\" name=\"$categorie[$i];$i;$j\"></input></td>";
						                            }
				                       			}
	                                   			else{
	                                   				echo "<td></td>";
	                                   			}
                                   			}
                                   			if($j>23){
                                   				$non = false;
                                   				foreach ($cumul_Charge_prev as $categ => $value) {
				                            		if ($categ == $categorie[$i]) {
				                            			if ($j==24/* || $j_passe==23*/) {
				                            				if ($value == NULL) {
				                            					echo "<td class=\"prev\"></td>";
				                            				}else{
				                            					echo "<td class=\"prev\">".str_replace("-", "", price($value))."</td>";
				                            				}
				                            				//$j_passe++;
				                            				$j++;
				                            			}
				                            		}
				                            		elseif(!array_key_exists($categorie[$i], $cumul_Charge_prev)){
				                            			if ($j==24 && !$non) {
				                            				echo "<td class=\"prev\"></td>";
				                            				$non = true;
				                            				//$j++;
				                            			}
				                            			elseif ($i_passe == $i-1) {
				                            				$non = false;
				                            			}
				                            			$i_passe = $i;
				                            		}
				                            	}
				                            	$non = false;
                                   				foreach ($cumul_Charge as $categ => $value) {
				                            		if ($categ == $categorie[$i]) {
				                            			if ($j==25 || $j_passe==25) {
				                            				echo "<td><b>".str_replace("-", "", price($value))."</b></td>";
				                            				$j_passe++;
				                            				$j++;
				                            			}
				                            		}
				                            		elseif(!array_key_exists($categorie[$i], $cumul_Charge)){
				                            			if ($j==25 && !$non) {
				                            				echo "<td></td>";
				                            				$j++;
				                            				$non = true;
				                            			}
				                            			elseif ($i_passe == $i-1) {
				                            				$non = false;
				                            			}
				                            			$i_passe = $i;
				                            		}
				                            	}
				                            }
				                            if ($j==26) {
				                            	foreach ($pourcentage_cumul_charge as $categ => $value) {
				                            		if ($categ == $categorie[$i]) {
				                            			if ($value != 0) {
				                            				echo "<td style='color:red'>".round($value,2)."</td>";
				                            			}
				                            			else{
				                            				echo "<td style='color:green'>".round($value,2)."</td>";
				                            			}
				                            		}
				                            	}
				                            }
                                   		}
                                   		if(isset($_GET['re'])){
			                            	$date_test = explode("/", $_GET['re']);
			                            	$date_test[1]--;
			                            }
			                            else{
			                            	$date_test = explode("-", date("d-m-Y"));
			                            	$date_test[1]--;
			                            }
                               		?>
                                </tr>
                            <?php	                            	
                        }
                    ?>
                    <tr class="liste_titre" style=" opacity: 0.7;">
                    	<td><b>Total charges (HT)</b></td>
                    	<?php
                    	for ($j=0; $j < 25; $j++) {
	                            	for($i=0; $i< 24; $i++){
		                        		foreach($charge_total_prev as $truc => $key){
		                    					$val = $truc*2;
		                                    	$val2 = $j;
												if($val==$val2 && $truc<12){
	                    							if ($date_test[1] >= 12) {
														$date_test[1]=1;
														$date_test[2]++;
													}
													else{
														$date_test[1]++;
													}
													if ($key == 0) {
														echo "<td></td>";
													}else{
	                    								echo "<td>".price($key)/*round($key, 2)*/."</td>";
	                    							}
	                    							$j++;
	                    						}
		                    				
		                    			}
		                    			foreach($charge_total as $truc => $key){
		                    					$val = ($truc*2)-1;
		                                        $val2 = $j-2;
		                    					if($val==$val2 && $truc < 12){
		                    						if ($key == 0) {
														echo "<td></td>";
													}else{
	                    								echo "<td><b>".price($key)/*round($key, 2)*/."</b></td>";
	                    							}
		                    						$j++;
		                    					}
		                    				}
		                    			
		                    		}
	                    			if($j%2==0){
	                    				if ($date_test[1] >= 12) {
											$date_test[1]=1;
											$date_test[2]++;
										}
										else{
											$date_test[1]++;
			                            }
			                            if ($j<23) {
			                            	echo "<td></td>";
			                            }
			                            else{
			                            	echo "<td>".price($cumul_total_charge_prev)."</td>";
			                            	echo "<td><b>".price($cumul_total_charge)."</b></td>";
			                            }
	                       			}
	                       			else{
	                       				echo "<td></td>";
	                       			}	                       			
	                    		}
	                    		if(isset($_GET['re'])){
	                            	$date_test = explode("/", $_GET['re']);
	                            	$date_test[1]--;
	                            }
	                            else{
	                            	$date_test = explode("-", date("d-m-Y"));
	                            	$date_test[1]--;
	                            }
	                            if ($pourcentage_cumul_total_charge != 0) {
	                            	echo "<td style='color:red'>".round($pourcentage_cumul_total_charge,2)."</td>";
	                            }
	                            else{
	                            	echo "<td style='color:green'>".round($pourcentage_cumul_total_charge,2)."</td>";
	                            }
                    	?>
                    </tr>
                    <tr class="liste_titre">
                            <td class="right"><b>Solde du mois (TTC)</b></td>
                            <?php for ($j=0; $j < 25; $j++) {
	                            	for($i=0; $i< 24; $i++){
		                        		foreach($tresoPrev as $truc => $key){
		                    				foreach($key as $row =>$value){
		                    					$val = $truc*2;
		                                    	$val2 = $j;
												if($row == "soldeCourant" && $val==$val2 && $truc<12){
	                    							if ($date_test[1] >= 12) {
														$date_test[1]=1;
														$date_test[2]++;
													}
													else{
														$date_test[1]++;
													}
													if($value==0){
		                    							echo "<td></td>";
		                    						}
		                    						else{
		                    							echo "<td>".price($value)/*round($value, 2)*/."</td>";
		                    						}
		                    							
	                    							$j++;
	                    						}
		                    				}
		                    			}
		                    			foreach($tresoReel as $truc => $key){
		                    				foreach($key as $row =>$value){
		                    					$val = ($truc*2)-1;
		                                        $val2 = $j-2;
		                    					if($row == "soldeCourant" && $val==$val2){
		                    						echo "<td><b>".price($value)/*round($value, 2)*/."</b></td>";
		                    						$j++;
		                    					}
		                    				}
		                    			}
		                    		}
	                    			if($j%2==0){
	                    				if ($date_test[1] >= 12) {
											$date_test[1]=1;
											$date_test[2]++;
										}
										else{
											$date_test[1]++;
			                            }
			                            if($j<23){
											echo "<td class=\"prev\" data-id=\"soldeCourant;$date_test[2]-$date_test[1]-$date_test[0]\"><input type=\"hidden\" name=\"soldeCourant;$j\"></input></td>";
	                       				}
	                       				else{
	                       					echo "<td>".price($cumul_solde_prev)."</td>";
	                       					echo "<td><b>".price($cumul_solde)."</b></td>";
	                       				}
	                       			}
	                       			else{
	                       				echo "<td></td>";
	                       			}	                       			
	                    		}
	                    		if(isset($_GET['re'])){
	                            	$date_test = explode("/", $_GET['re']);
	                            	$date_test[1]--;
	                            }
	                            else{
	                            	$date_test = explode("-", date("d-m-Y"));
	                            	$date_test[1]--;
	                            }
	                            if ($pourcentage_cumul_total_charge != 0) {
	                            	echo "<td style='color:red'>".round($pourcentage_cumul_solde_tresorerie,2)."</td>";
	                            }
	                            else{
	                            	echo "<td style='color:green'>".round($pourcentage_cumul_solde_tresorerie,2)."</td>";
	                            }
                        	?>
                        </tr>
                        <tr class="pair">
	                    	<td>CA/N-1 (%)</td>
	                    	<?php
	                    		for ($j=0; $j < 24; $j++) {
		                        		foreach($pourcentage_ca_par_ca_n_moins_1 as $truc => $key){
		                    					$val = ($truc*2)-1;
		                                        $val2 = $j-2;
												if($val==$val2 && $truc<12){
													if ($key == 0) {
														echo "<td colspan=\"2\" class=\"center\"></td>";
													}else{
	                    								echo "<td colspan=\"2\" class=\"center\">".$key."</td>";
	                    							}
	                    							$j++;
	                    						}
		                    				
		                    			}
			                    	}
	                    	?>
	                    </tr>
	                    <tr class="impair">
	                    	<td>Taux de marge (%)</td>
	                    	<?php
	                    		for ($j=0; $j < 24; $j++) {
		                        		foreach($taux_de_marge as $truc => $key){
	                    					$val = ($truc*2)-1;
	                                        $val2 = $j-2;
											if($val==$val2 && $truc<12){
                    							echo "<td colspan=\"2\" class=\"center\">".$key."</td>";
                    							$j++;
                    						}
		                    				
		                    			}
			                    	}
	                    	?>
	                    </tr>
	                    <tr class="pair">
	                    	<td>Cumul CA/N-1 (%)</td>
	                    	<?php
	                    		for ($j=0; $j < 24; $j++) {
		                        		foreach($cumul as $truc => $key){
	                    					$val = ($truc*2)-1;
	                                        $val2 = $j-2;
											if($val==$val2 && $truc<12){
                    							echo "<td colspan=\"2\" class=\"center\">".$key."</td>";
                    							$j++;
                    						}
		                    				
		                    			}
			                    	}
	                    	?>
	                    </tr>
	                    <tr class="impair">
	                    	<td>TVA Collecté</td>
	                    	<?php
	                    		for ($j=0; $j < 24; $j++) {
		                        		foreach($tva_collecte as $truc => $key){
	                    					$val = ($truc*2)-1;
	                                        $val2 = $j-2;
											if($val==$val2 && $truc<12){
                    							echo "<td colspan=\"2\" class=\"center\">".price(str_replace("-", "", round($key,2)))."</td>";
                    							$j++;
                    						}
		                    				
		                    			}
			                    	}
	                    	?>
	                    </tr>
	                    <tr class="pair">
	                    	<td>TVA Déductible</td>
	                    	<?php
	                    		for ($j=0; $j < 24; $j++) {
		                        		foreach($tva_due as $truc => $key){
	                    					$val = ($truc*2)-1;
	                                        $val2 = $j-2;
											if($val==$val2 && $truc<12){
                    							echo "<td colspan=\"2\" class=\"center\">".price(str_replace("-", "", round($key,2)))."</td>";
                    							$j++;
                    						}
		                    				
		                    			}
			                    	}
	                    	?>
	                    </tr>
	                    <tr class="liste_titre">
	                    	<td>Solde TVA</td>
	                    	<?php
	                    		for ($j=0; $j < 24; $j++) {
		                        		foreach($tva_solde as $truc => $key){
	                    					$val = ($truc*2)-1;
	                                        $val2 = $j-2;
											if($val==$val2 && $truc<12){
                    							echo "<td colspan=\"2\" class=\"center\">".price(round($key,2))."</td>";
                    							$j++;
                    						}
		                    				
		                    			}
			                    	}
	                    	?>
	                    </tr>
                </tbody>
            </table>
            <input type="submit" id="button">
        </form>
    </div>
    </div>
<?php
if(isset($_GET['re'])){
	if (isset($_POST['generatePDF'])) {
		
	}
}
$facturestatic=new Facture($db);

if ($_REQUEST["account"])
{
	$acct = new Account($db);
	if ($_GET["account"])
	{
		$result=$acct->fetch($_GET["account"]);
	}

	$solde = $acct->solde(0);
?>
	<div class="fichecenter">
    	<div class="fichethirdleft">
			<table class="noborder nohover">
				<tbody>
					<tr class="liste_titre" >
						<th class="center" colspan="2">
							Encours
						</th>
					</tr>
					<tr>
						<td class="pair">
							Solde actuel
						</td>
						<td class="pair">
							<?php echo price($solde); ?>
						</td>
					</tr>
<?php
	

	// Customer invoices
	$sql = "SELECT 'invoice' as family, f.rowid as objid, f.facnumber as ref, f.total_ttc, f.type, f.date_lim_reglement as dlr,";
	$sql.= " s.rowid as socid, s.nom, s.fournisseur";
	$sql.= " FROM ".MAIN_DB_PREFIX."facture as f";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON f.fk_soc = s.rowid";
	$sql.= " WHERE f.entity = ".$conf->entity;
	$sql.= " AND f.paye = 0 AND f.fk_statut = 1";	// Not paid
	$sql.= " ORDER BY dlr ASC";

	// Supplier invoices
	$sql2 = " SELECT 'invoice_supplier' as family, ff.rowid as objid, ff.ref_supplier as ref, (-1*ff.total_ttc) as total_ttc, ff.type, ff.date_lim_reglement as dlr,";
	$sql2.= " s.rowid as socid, s.nom, s.fournisseur";
	$sql2.= " FROM ".MAIN_DB_PREFIX."facture_fourn as ff";
	$sql2.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON ff.fk_soc = s.rowid";
	$sql2.= " WHERE ff.entity = ".$conf->entity;
	$sql2.= " AND ff.paye = 0 AND fk_statut = 1";	// Not paid
	$sql2.= " ORDER BY dlr ASC";

	$error=0;
	$tab_sqlobjOrder=array();
	$tab_sqlobj=array();
	$tab_sql=array();

	// List customer invoices
	$result = $db->query($sql);
	if ($result)
	{
		$num = $db->num_rows($result);
		for ($i = 0;$i < $num;$i++)
		{
			$sqlobj = $db->fetch_object($result);
			$tab_sqlobj[] = $sqlobj;
			$tab_sqlobjOrder[]= $db->jdate($sqlobj->dlr);
		}
		$db->free($result);
	}
	// List supplier invoices
	$result2=$db->query($sql2);
	if ($result2)
	{
		$num = $db->num_rows($result2);
		for ($i = 0;$i < $num;$i++)
		{
			$sqlobj = $db->fetch_object($result2);
			$tab_sqlobj[] = $sqlobj;
			$tab_sqlobjOrder[]= $db->jdate($sqlobj->dlr);
		}
		$db->free($result2);
	}
		array_multisort($tab_sqlobjOrder,$tab_sqlobj);
		foreach ($tab_sqlobj as $key=>$value) {
			$tab_sqlobj[$key] = "'" . serialize($value) . "'";
		}
		$tab_sqlobj = array_unique($tab_sqlobj);
		foreach ($tab_sqlobj as $key=>$value) {
			$tab_sqlobj[$key] = unserialize(trim($value, "'"));
		}


		$num = count($tab_sqlobj);
		$i = 0;
		while ($i < $num)
		{
			$paiement = '';
			$obj = array_shift($tab_sqlobj);
			if ($obj->family == 'invoice')
			{
				$facturestatic->id=$obj->objid;
				$paiement = $facturestatic->getSommePaiement();	// Payment already done
			}
			$total_ttc = $obj->total_ttc;
			if ($paiement) $total_ttc = $obj->total_ttc - $paiement;
			$solde += $total_ttc;
    		if ($obj->total_ttc >= 0) {$tab_encours_client[] = $total_ttc; };
			$i++;
		}
		$encours_client = 0;
		if (!empty($tab_encours_client)) {
			foreach ($tab_encours_client as $value) {
				$encours_client += $value;
			}
		}
		$encours_fourn = 0;
		foreach ($encoursFourn as $value) {
			$encours_fourn += $value;
		}
	
		?>
			<tr>
				<td class="impair">
					Client
				</td>
				<td class="impair">
					<?php echo price($encours_client); ?>
				</td>
			</tr>
			<tr>
				<td class="pair">
					Fournisseur
				</td>
				<td class="pair">
					<?php echo price($encours_fourn); ?>
				</td>
			</tr>
			<tr>
				<td class="impair">
					Solde futur
				</td>
				<td class="impair">
					<?php echo price($solde); ?>
				</td>
			</tr>
			</div>
			</div>
		<?php

}
else
{
	echo "Error Bank Account not found";
}

// Example 1 : Adding jquery code
print '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	function init_myfunc()
	{
		jQuery("#myid").removeAttr(\'disabled\');
		jQuery("#myid").attr(\'disabled\',\'disabled\');
	}
	init_myfunc();
	jQuery("#mybutton").click(function() {
		init_needroot();
	});
});
</script>';


// Example 2 : Adding links to objects
// The class must extends CommonObject class to have this method available
//$somethingshown=$object->showLinkedObjectBlock();


// Example 3 : List of data
if ($action == 'list')
{
    $sql = "SELECT";
    $sql.= " t.rowid,";
    $sql.= " t.field1,";
    $sql.= " t.field2";
    $sql.= " FROM ".MAIN_DB_PREFIX."mytable as t";
    $sql.= " WHERE field3 = 'xxx'";
    $sql.= " ORDER BY field1 ASC";

    print '<table class="noborder">'."\n";
    print '<tr class="liste_titre">';
    print_liste_field_titre($langs->trans('field1'),$_SERVER['PHP_SELF'],'t.field1','',$param,'',$sortfield,$sortorder);
    print_liste_field_titre($langs->trans('field2'),$_SERVER['PHP_SELF'],'t.field2','',$param,'',$sortfield,$sortorder);
    print '</tr>';

    dol_syslog($script_file." sql=".$sql, LOG_DEBUG);
    $resql=$db->query($sql);
    if ($resql)
    {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num)
        {
            while ($i < $num)
            {
                $obj = $db->fetch_object($resql);
                if ($obj)
                {
                    // You can use here results
                    print '<tr><td>';
                    print $obj->field1;
                    print $obj->field2;
                    print '</td></tr>';
                }
                $i++;
            }
        }
    }
    else
    {
        $error++;
        dol_print_error($db);
    }

    print '</table>'."\n";
}

include('phpGraph/phpGraph.php');
//We call an instance of phpGraph() class
$ca_N_moins_1 = $tresorerie->getCA_N_moins_1();
$ca_N = $tresorerie->getCA_N();
$tab = array($ca_N, $ca_N_moins_1);
$G = new phpGraph();
$options = array(
	'steps' => 1000,
  'width' => 700,// (int) width of grid
  'height' => 700,// (int) height of grid
  'multi'=>true,
  'filled'=>false,
  'stroke' => array(
                '0'=>'red',
                '1'=>'blue'
    )
);
echo $G->draw($tab, $options);



// End of page
llxFooter();
$db->close();
?>
<?php
//	extract(unserialize(file_get_contents('datas.txt')));
	$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';					// to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../dolibarr/htdocs/main.inc.php';     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../../dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (! $res) die("Include of main fails");
	require_once 'class/tresorerie.php';
	require_once 'class/connect.php';
	$connect = new connect($dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user ,$dolibarr_main_db_pass);
	$link = $connect->link();
	$moisM = date("m");
	$annee = date("Y");
	$tresorerie = new tresorerie($link);

	$nb_lignes = $tresorerie->getNbLignes();
	$categorie = $tresorerie->getCategorie();
	$taux = $tresorerie->getTaux();
	if(isset($_GET['re'])){
		$tresoPrev = $tresorerie->getTresorerie_Prev_HT($taux, $_GET['re']);
		$tresoReel =$tresorerie->getTresorerie_Reel_HT($taux, $_GET['re']);
		$charge_total = $tresorerie->getCharge($categorie, $taux, $_GET['re']);
		$charge_total_prev = $tresorerie->getChargePrev($categorie, $taux, $_GET['re']);
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
	$tva_solde = $tresorerie->calculSoldeTVA($tva_due, $tva_collecte, $tva_payer);
	$cumul_total_charge = $tresorerie->cumul_total_charge($charge_total);
	$cumul_total_charge_prev = $tresorerie->cumul_total_charge_prev($charge_total_prev);
	$pourcentage_cumul_charge = $tresorerie->pourcentage_cumul_charge($cumul_Charge, $cumul_Charge_prev);
	$pourcentage_cumul_achat = $tresorerie->pourcentage_cumul_achat($cumul_achat, $cumul_achat_prev);
	$pourcentage_cumul_ca = $tresorerie->pourcentage_cumul_ca($cumul_CA, $cumul_CA_prev);
	$pourcentage_cumul_total_charge = $tresorerie->pourcentage_cumul_total_charge($cumul_total_charge, $cumul_total_charge_prev);
	$pourcentage_cumul_solde_tresorerie = $tresorerie->pourcentage_cumul_solde_tresorerie($cumul_solde, $cumul_solde_prev);

	$mois = array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");
	$moisPrecedent = array();
	$nom = $tresorerie->getNomEntreprise();
	ob_start();
	?>
	<page orientation="paysage" format="A2" >
 </page>
 <style type="text/css">
	table, th, td{
		border:1px;
	}
	table {
    	border-collapse: collapse;
	}
	td{
		text-align: left;
		padding: 10px 0px 0px 0px;
	}
	h1{
		text-align: center;
	}
	.center{
		text-align: center;
	}
 </style>
 	<h1>Tableau de bord - Trésorerie</h1>
	<table id="tableau" class="border nohover" width="100%">
                    <tbody>
                        <tr class="liste_titre" style="background-color:lightblue;">
                            <td class="center">
                                <b><?php echo $nom; ?></b>
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
											echo "<td class=\"prev\" data-id=\"soldeDebut;$date_test[2]-$date_test[1]-$date_test[0]\"></td>";
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
	                    							echo "<td class=\"prev\" data-id=\"$row;$date_test[2]-$date_test[1]-$date_test[0]\">".price($value)/*round($value, 2)*/."</td>";
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
			                            	echo "<td class=\"prev\" data-id=\"CA;$date_test[2]-$date_test[1]-$date_test[0]\"></td>";
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
                        <tr class="liste_titre" style="background-color:lightblue;">
                            <td><b>Charge</b></td>
                            <?php for ($i=0; $i < 27; $i++) {?><td></td> <?php } ?>
                        </tr>
                        <tr class="liste_titre" style=" opacity: 0.7;" style="background-color:lightblue;">
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
	                    							echo "<td class=\"prev\" data-id=\"$row;$date_test[2]-$date_test[1]-$date_test[0]\">".price($value)/*round($value, 2)*/."</td>";
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
			                            	echo "<td class=\"prev\" data-id=\"achat;$date_test[2]-$date_test[1]-$date_test[0]\"></td>";
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
                        <tr class="liste_titre" style=" opacity: 0.7;" style="background-color:lightblue;">
                            <td><b>Charges fixes (HT)</b></td>
                             <?php for ($i=0; $i < 27; $i++) {?><td></td> <?php } ?>
                        </tr>
                        <?php
                            $index = 0;
                            $class = array("pair", "impair");
                            $search = array(',', '-', '(', ')', ' ', '/', "'", '+');
							$replace = array("");
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
                                        	if ($j<24) {
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
																echo "<td class=\"prev\" data-id=\"$categorie[$i];$date_test[2]-$date_test[1]-$date_test[0]\">".str_replace("-", "",price($valueTTC)/*round($valueTTC, 2)*/)."</td>";
																
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
						                            	echo "<td class=\"prev\" data-id=\"$categorie[$i];$date_test[2]-$date_test[1]-$date_test[0]\"></td>";
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
                    <tr class="liste_titre" style=" opacity: 0.7;" style="background-color:lightblue;">
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
                    <tr class="liste_titre" style="background-color:lightblue;">
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
											echo "<td class=\"prev\" data-id=\"soldeCourant;$date_test[2]-$date_test[1]-$date_test[0]\"></td>";
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
	                            if ($pourcentage_cumul_solde_tresorerie != 0) {
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
	                     <tr class="liste_titre" style="background-color:lightblue;">
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

<?php
//on associe le contenu de la page a une variable
$content=ob_get_clean();
require('html2pdf/html2pdf.class.php');
	try{
	    $pdf = new HTML2PDF('P','A4','fr');
	    /**/
	    $pdf->writeHTML($content);
	    $pdf->Output('test.pdf');
	}catch(HTML2PDF_exception $e){
	    die($e);
	}
?>
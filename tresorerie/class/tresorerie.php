<?php 
/**
* 
*/
class tresorerie extends CommonObject
{
	var $solde;
	var $date;
	var $dateD;
	var $charge;
	var $charge_reel = array();
	var $charge_prev = array();
	var $montant_Categ = array();
	var $ca;
	var $achat;
	var $entity;
	var $categorie = array();
	var $tresoReel = array();
	var $tresoPrev = array();
	var $taux = array();
	var $link;

	function __construct($db, $leink)
	{
		global $conf, $langs;
		$this->link = $leink;
		$this->entity = $conf->entity;
        $this->db = $db;
        $this->date = date("Y-m-d");
        $this->dateD = date("Y-m");
	}

	//Selectionne et calcule le solde du mois courant
	public function getSolde()
	{
		$sql = "SELECT sum(b.amount) as amount FROM llx_bank as b, llx_bank_account as ba WHERE b.fk_account = 1 AND b.dateo <= '$this->date' AND ba.entity = '$this->entity' ";
		$res = mysqli_query($this->link, $sql) or die(mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$this->solde = $data["amount"];
		}
		return $this->solde;
	}

	//Selectionne et calcule la somme su chiffre d'affaire du mois courant
	public function getTotalCharge()
	{
		$sql = "SELECT DISTINCT SUM(amount) as a FROM llx_bank_categ as bcat, llx_bank_class as bclass, llx_bank_account as ba, llx_bank as b WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND bclass.lineid = b.rowid";
		$res = mysqli_query($this->link, $sql) or die(mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$this->charge = $data['a'];
		}
		return $this->charge;
	}

	//Selcetionne le label des catégories, le montant et la date selon si c'est dans le mois courant et le montant selon si il est rangé dans une catégorie
	public function getMontantCategorie()
	{
		$sql = "SELECT DISTINCT bcat.label, b.amount FROM llx_bank_categ as bcat, llx_bank_class as bclass, llx_bank_account as ba, llx_bank as b, llx_categ_tva as ct, llx_c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ct.fk_c_tva = t.rowid AND ct.fk_bank_categ = bcat.rowid";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			//calcul permettant d'avoir le montant HT
			//$amount = $data['amount']*(100/($data['taux']+100));
			$this->montant_Categ[$data['label']] = $this->montant_Categ[$data['label']]+$data['amount'];
		}

		return $this->montant_Categ;
	}

	//Selectionne le chiffre d'affaire, plus précisement le nombre d'entrée d'argent et on additionne tous cela pour obtenir le CA du mois courant
	public function getCA()
	{
		$sql = "SELECT b.amount, b.dateo FROM llx_bank as b, llx_bank_account as ba WHERE b.amount > 0 AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ba.entity = '$this->entity'";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$this->ca += $data['amount'];
		}
		return $this->ca;
	}

	//Selectionne le montant des achats, seulement si, les achats ne font pas partie d'une catégorie (charge)
	public function getAchat()
	{
		$sql = "SELECT DISTINCT b.amount FROM llx_bank as b, llx_bank_account as ba where b.rowid NOT IN (select bclass.lineid FROM llx_bank_class as bclass) AND b.amount < 0 AND b.dateo <= '$this->date'AND b.dateo >= '$this->dateD-01' AND ba.entity = '$this->entity';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$this->achat += $data['amount'];
		}
		return $this->achat;
	}

	public function getCategorie()
	{
		$query_categ = "SELECT label FROM `llx_bank_categ` ORDER BY `label` ASC;";
		$resultat = mysqli_query($this->link, $query_categ) or die (mysqli_error($this->link));
		while ($data= mysqli_fetch_assoc($resultat)) {
			$this->categorie[] = $data['label'];
		}
		return $this->categorie;
	}

	public function getNbLignes()
	{
		$query_categ = "SELECT label FROM `llx_bank_categ` ORDER BY `label` ASC;";
		$resultat = mysqli_query($this->link, $query_categ) or die (mysqli_error($this->link));
		$nb = mysqli_num_rows($resultat);
		return $nb;
	}

	public function Calcul_HT_Categ($taux, $tab_ligne_bd)
	{
		$amount = array();
		foreach ($taux as $categTaux => $valueTaux) {
			foreach ($tab_ligne_bd as $ligne_par_mois) {
				foreach ($ligne_par_mois as $categTTC => $valueTTC) {
					if ($categTTC == $categTaux) {
						$amount[$categTTC] = round($valueTTC*(100/($valueTaux+100)), 2);
						$ligne[] = $amount;
					}
					else{
						$amount[$categTTC] = $valueTTC;
						$ligne[] = $amount;
					}
					$amount = array();
				}
			}
		}
		return $amount;
	}

	//Permet de soit mettre à jour le tableau de bord (base de donnée) llx_tresorerie ou soit d'inserer une nouvelle entrée dans le tbd (bd)
	public function Upsert($Tcharge, $solde = 0, $ca = 0, $achat = 0, $Mcharge, $categ)
	{
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		$mois = array();
		$month = explode("-", $this->dateD)[1];
		$years = explode("-", $this->dateD)[0];
		$nb_jours = date("t", mktime(0,0,0,$month, 1, $years));
		$query = "SELECT t.date FROM llx_tresorerie as t WHERE t.date <= '$this->dateD-$nb_jours' AND t.date >= '$this->dateD-01' AND t.type='reel';";
		$res = mysqli_query($this->link, $query) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$m = $data['date'];
		}
		$mois = explode("-", $m);

		if ($mois[1] == date("m")) {
			$sql = "UPDATE llx_tresorerie SET ";
			foreach ($categ as $rowC) {
				foreach ($Mcharge as $rowM => $value) {
					$rowC = str_replace($search, $replace, $rowC);
					$rowM = str_replace($search, $replace, $rowM);
					if($rowC == $rowM){
						$sql .= "$rowM=$value, ";						
					}
					elseif(!strstr($sql, "$rowC")){
						$sql .= "$rowC=NULL, ";
					}
				}
			}
			$sql .= ($solde == 0) ? "soldeCourant=NULL, " : "soldeCourant=$solde, ";
			$sql .= ($ca == 0) ? "CA=NULL, " : "CA=$ca, ";
			$sql .= ($achat == 0) ? "achat=NULL, " : "achat=$achat, ";
			$sql .= "date='$this->date' where date <= '$m' AND date >= '$this->dateD-01' AND type='reel' ;";
			mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
			mysqli_commit($this->link);
		}
		else{
			$sql = "INSERT INTO llx_tresorerie (";
			foreach ($Mcharge as $row => $value) {
				$row = str_replace($search, $replace, $row);
				$sql .= "$row, ";
			}
			$sql .= "soldeCourant, CA, achat, date, type) VALUES (";
			foreach ($Mcharge as $row => $value) {
				$sql .= "'$value', ";
			}
			$sql .= ($solde == 0) ? "NULL, " : "'$solde', ";
			$sql .= ($ca == 0) ? "NULL, " : "'$ca', ";
			$sql .= ($achat == 0) ? "NULL, " : "'$achat', ";
			$sql .= "'$this->date', 'reel');";
			mysqli_query($this->link, $sql) or die (mysqli_error());
			mysqli_commit($this->link);
		}
	}

	public function getTaux()
	{
		$sql = "SELECT DISTINCT bcat.label, t.taux FROM llx_c_tva as t, llx_categ_tva as ct, llx_bank_categ as bcat where ct.fk_c_tva = t.rowid AND ct.fk_bank_categ = bcat.rowid;";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		while ($data = mysqli_fetch_assoc($res)) {
			$data['label'] = str_replace($search, $replace, $data['label']);
			$this->taux[$data['label']] = $data['taux'];
		}
		return $this->taux;
	}

	public function getTresorerie_Reel($date_param = 0)
	{
		$date_decoupe = explode("/", $date_param);
		$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
		$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]-31";
		$date_temp_a = explode("-", $this->date);
		$date_temp_courant = "$date_temp_a[0]-$date_temp_a[1]-31";
		$sql = ($date == 0) ?  "SELECT * FROM llx_tresorerie as t WHERE t.date>='$this->dateD-01' AND t.type='reel'":  "SELECT * FROM llx_tresorerie as t WHERE t.date>='$date' AND t.type='reel' ORDER BY t.date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$nb = mysqli_num_fields($res);
		$tab1 = array();
		while ($data = mysqli_fetch_row($res)) {
			for ($i=0; $i < $nb; $i++) {
				$finfo = mysqli_fetch_field_direct($res, $i);
				if ($data[$i] != NULL) {
					$tab1[$finfo->name] = $data[$i];
				}
			}
			$this->tresoReel[] = $tab1;
			$tab1 = array();
		}
		return $this->tresoReel;
	}

	public function getTresorerie_Reel_HT($taux, $date_param = 0)
	{
		$tresoReel_HT = array();
		$date_decoupe = explode("/", $date_param);
		$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
		$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]-01";
		$date_temp_a = explode("-", $this->date);
		$date_temp_courant = "$date_temp_a[0]-$date_temp_a[1]-01";
		$sql = ($date == 0) ?  "SELECT * FROM llx_tresorerie as t WHERE t.date>='$this->dateD-01' AND t.type='reel' ORDER BY t.date ASC;":  "SELECT * FROM llx_tresorerie as t WHERE t.date>='$date' AND t.type='reel' ORDER BY t.date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$nb = mysqli_num_fields($res);
		$tab1 = array();
		while ($data = mysqli_fetch_row($res)) {
			for ($i=0; $i < $nb; $i++) {
				$finfo = mysqli_fetch_field_direct($res, $i);
				if ($data[$i] != NULL) {
					if (!empty($taux)) {
						foreach ($taux as $categTaux => $valueTaux) {
							if($categTaux == $finfo->name){
								$tab1[$finfo->name] = round($data[$i]*(100/($valueTaux+100)), 2);
							}
							elseif(!array_key_exists($finfo->name, $tab1)){
								$tab1[$finfo->name] = $data[$i];
							}
						}
					}
					elseif ($finfo->name == "CA") {
						$tab1[$finfo->name] = $data[$i];
					}
					elseif ($finfo->name == "achat") {
						$tab1[$finfo->name] = $data[$i];
					}
					elseif ($finfo->name == "soldeCourant") {
						$tab1[$finfo->name] = $data[$i];
					}
					elseif ($finfo->name == "soldeDebut") {
						$tab1[$finfo->name] = $data[$i];
					}
				}
			}
			$tresoReel_HT[] = $tab1;
			$tab1 = array();
		}
		return $tresoReel_HT;
	}

	public function getTresorerie_Prev($date_param = 0)
	{
		$date_decoupe = explode("/", $date_param);
		$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
		$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]-31";
		$date_temp_a = explode("-", $this->date);
		$date_temp_courant = "$date_temp_a[0]-$date_temp_a[1]-31";
		$sql = ($date == 0) ?  "SELECT * FROM llx_tresorerie as t WHERE t.date>='$this->dateD-01' AND t.type='prev' ORDER BY t.date ASC;":  "SELECT * FROM llx_tresorerie as t WHERE t.date>='$date' AND t.type='prev' ORDER BY t.date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$nb = mysqli_num_fields($res);
		$tab1 = array();
		while ($data = mysqli_fetch_row($res)) {
			for ($i=0; $i < $nb; $i++) {
				$finfo = mysqli_fetch_field_direct($res, $i);
				if ($data[$i] != NULL) {
					$tab1[$finfo->name] = $data[$i];
				}
			}
			$this->tresoPrev[] = $tab1;
			$tab1 = array();
		}
		return $this->tresoPrev;
	}

	public function getTresorerie_Prev_HT($taux, $date_param = 0)
	{
		$tresoPrev_HT = array();
		$date_decoupe = explode("/", $date_param);
		$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
		$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]-31";
		$date_temp_a = explode("-", $this->date);
		$date_temp_courant = "$date_temp_a[0]-$date_temp_a[1]-31";
		$sql = ($date == 0) ?  "SELECT * FROM llx_tresorerie as t WHERE t.date>='$this->dateD-01' AND t.type='prev' ORDER BY t.date ASC;":  "SELECT * FROM llx_tresorerie as t WHERE t.date>='$date' AND t.type='prev' ORDER BY t.date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$nb = mysqli_num_fields($res);
		$tab1 = array();
		while ($data = mysqli_fetch_row($res)) {
			for ($i=0; $i < $nb; $i++) {
				$finfo = mysqli_fetch_field_direct($res, $i);
				if ($data[$i] != NULL) {
					if (!empty($taux)) {
						foreach ($taux as $categTaux => $valueTaux) {
							if($categTaux == $finfo->name){
								$tab1[$finfo->name] = round($data[$i]*(100/($valueTaux+100)), 2);
							}
							elseif(!array_key_exists($finfo->name, $tab1)){
								$tab1[$finfo->name] = $data[$i];
							}
						}
					}
					elseif ($finfo->name == "CA") {
						$tab1[$finfo->name] = $data[$i];
					}
					elseif ($finfo->name == "achat") {
						$tab1[$finfo->name] = $data[$i];
					}
					elseif ($finfo->name == "soldeCourant") {
						$tab1[$finfo->name] = $data[$i];
					}
					elseif ($finfo->name == "soldeDebut") {
						$tab1[$finfo->name] = $data[$i];
					}
				}
			}
			$tresoPrev_HT[] = $tab1;
			$tab1 = array();
		}
		return $tresoPrev_HT;
	}

	public function setPrevisionel($tab_prev)
	{
		if (strlen($tab_prev)>2) {
			$info = explode(";", $tab_prev);
			$info_date = explode("-", $info[2]);
			$info_date_D = $info_date[0]."-".$info_date[1]."-01";
			$info_date_F = $info_date[0]."-".$info_date[1]."-28";
			$info_date = $info_date[0]."-".$info_date[1]."-".$info_date[2];
			if (strlen($info[0])<1 || $info[0]=="" || $info[0]==" " || $info[0]==0) {
				$info[0]="NULL";
			}
			if (!strstr("-", $info[0]) && $info[0]!="NULL") {
				$info[0] = "-".$info[0];
			}
			$sql = "UPDATE llx_tresorerie SET $info[1]=$info[0], type='prev' WHERE type='prev' AND date BETWEEN '$info_date_D' AND '$info_date_F';";
			mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
			mysqli_commit($this->link);
		}
	}

	public function getCharge($categ, $taux, $date_param = 0)
	{
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}
		$sql = "SELECT * FROM llx_tresorerie where type='reel' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_treso = array();
		$i = 0;
		while($data = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			$tableau_treso[] = $data;
		}

		foreach ($tableau_treso as $key => $tableau_treso_mois) {
			foreach ($tableau_treso_mois as $categorie_du_mois => $valeur) {
				foreach ($taux as $categTaux => $valueTaux) {
					if ($categTaux == $categorie_du_mois) {
						$tableau_treso_mois[$categorie_du_mois] = round($valeur*(100/($valueTaux+100)), 2);
					}
				}
			}
			$tableau_treso[$key] = $tableau_treso_mois;
		}

		foreach ($tableau_treso as $tableau_treso_mois) {
			$_les_dates_treso = explode("-",$tableau_treso_mois['date']);
			if (($_les_dates_treso[0]."-".$_les_dates_treso[1]) >= $date_temp_demande) {
				foreach ($tableau_treso_mois as $categorie_du_mois => $valeur) {
					foreach ($categ as $key => $value) {
						$value = str_replace($search, $replace, $value);
						if ($value == $categorie_du_mois) {
							$this->charge_reel[$i] += $valeur;
						}
					}
				}
				$i++;
			}
		}
		return $this->charge_reel;
	}

	public function getChargePrev($categ, $taux, $date_param = 0)
	{
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");

		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}

		$sql = "SELECT * FROM llx_tresorerie where type='prev' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_treso = array();
		$i = 0;
		while($data = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			$tableau_treso[] = $data;
		}

		foreach ($tableau_treso as $key => $tableau_treso_mois) {
			foreach ($tableau_treso_mois as $categorie_du_mois => $valeur) {
				foreach ($taux as $categTaux => $valueTaux) {
					if ($categTaux == $categorie_du_mois) {
						$tableau_treso_mois[$categorie_du_mois] = round($valeur*(100/($valueTaux+100)), 2);
					}
				}
			}
			$tableau_treso[$key] = $tableau_treso_mois;
		}

		foreach ($tableau_treso as $tableau_treso_mois) {
			$_les_dates_treso = explode("-",$tableau_treso_mois['date']);
			if (($_les_dates_treso[0]."-".$_les_dates_treso[1]) >= $date_temp_demande) {
				foreach ($tableau_treso_mois as $categorie_du_mois => $valeur) {
					foreach ($categ as $key => $value) {
						$value = str_replace($search, $replace, $value);
						if ($value == $categorie_du_mois) {
							$this->charge_prev[$i] += $valeur;
						}
					}
				}
				$i++;
			}
		}
		return $this ->charge_prev;
	}

	public function calcul_solde_tresorerie_prev($categorie, $taux, $date_param = 0)
	{
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");

		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}

		$query_1 = "SELECT soldeDebut, date FROM llx_tresorerie WHERE date>='".date("Y")."-01-01' AND type='reel'";
		$res_1 = mysqli_query($this->link, $query_1)or die(mysqli_error($this->link));

		$tab_tout = array();
		$tab_solde_prev = array();
		$solde_courant_reel = array();
		$i = 0;
		$ok = false;
		$query = "SELECT * FROM llx_tresorerie WHERE date>='".date("Y")."-01-01' AND type ='prev' ORDER BY date ASC";
		$res = mysqli_query($this->link, $query)or die(mysqli_error($this->link));
		while ($data = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
			$tab_tout[] = $data;
		}
		while ($soldeDebut = mysqli_fetch_assoc($res_1)) {
			$solde_courant_reel[$soldeDebut['date']] = $soldeDebut['soldeDebut'];
		}
		foreach ($tab_tout as $categorie_du_mois => $valeur) {
			$_les_dates_treso = explode("-",$valeur['date']);
			if (($_les_dates_treso[0]."-".$_les_dates_treso[1]) >= $date_temp_demande) {
				foreach ($valeur as $les_categ => $value) {
					foreach ($solde_courant_reel as $key => $valueSoldeCourant) {
						$key = explode("-",$key);
						if (($key[0]."-".$key[1]) == ($_les_dates_treso[0]."-".$_les_dates_treso[1]) && !$ok) {
							$tab_solde_prev[$_les_dates_treso[0]."-".$_les_dates_treso[1]] = $valueSoldeCourant;
							$ok=true;
							$query_update_solde_debut = "UPDATE llx_tresorerie SET soldeDebut = '".$valueSoldeCourant."' WHERE date >= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-01' AND date <= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-28' AND type = 'prev';";
							mysqli_query($this->link, $query_update_solde_debut)or die(mysqli_error($this->link));
							mysqli_commit($this->link);
						}
					}
					foreach ($categorie as $categ) {
						$categ = str_replace($search, $replace, $categ);
						if ($categ == $les_categ) {
							$tab_solde_prev[$_les_dates_treso[0]."-".$_les_dates_treso[1]] += $value;
						}
					}
					if ($les_categ == "CA") {
						$tab_solde_prev[$_les_dates_treso[0]."-".$_les_dates_treso[1]] += $value;
					}
					elseif ($les_categ == "achat") {
						$tab_solde_prev[$_les_dates_treso[0]."-".$_les_dates_treso[1]] += $value;
					}
				}
				$ok = false;
				$query_update_solde_courant = "UPDATE llx_tresorerie SET soldeCourant = '".$tab_solde_prev[$_les_dates_treso[0]."-".$_les_dates_treso[1]]."' WHERE date >= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-01' AND date <= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-28' AND type = 'prev';";
				mysqli_query($this->link, $query_update_solde_courant);
				mysqli_commit($this->link);
			}
		}			
	}

	public function calcul_reel_futur()
	{
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		
		//Pour les categories
		$le_tab = array();
		$query = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM llx_bank_categ as bcat, llx_bank_class as bclass, llx_bank_account as ba, llx_bank as b, llx_facture as f, llx_categ_tva as ct, llx_c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ct.fk_c_tva = t.rowid AND ct.fk_bank_categ = bcat.rowid ORDER BY b.dateo ASC";
		$res = mysqli_query($this->link, $query)or die(mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$categorie = str_replace($search, $replace, $data['label']);
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur[$categorie] += $data['amount'];
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		foreach ($le_tab as $date_du_tab => $value) {
			foreach ($value as $catg => $la_valeur) {
				$sql = "UPDATE llx_tresorerie SET ".$catg."=".$la_valeur." where date >= '".$date_du_tab."-01' AND date <= '".$date_du_tab."-28' AND type = 'reel';";
				mysqli_query($this->link, $sql)or die(mysqli_error($this->link));
				mysqli_commit($this->link);
			}
		}

		//Pour le chiffre d'affaire
		$le_tab = array();
		$sql = "SELECT b.amount, b.dateo FROM llx_bank as b, llx_bank_account as ba WHERE b.amount > 0 AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ba.entity = '$this->entity';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["CA"] += $data['amount'];
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		foreach ($le_tab as $date_du_tab => $value) {
			foreach ($value as $catg => $la_valeur) {
				$sql = "UPDATE llx_tresorerie SET ".$catg."=".$la_valeur." where date >= '".$date_du_tab."-01' AND date <= '".$date_du_tab."-28' AND type = 'reel';";
				mysqli_query($this->link, $sql)or die(mysqli_error($this->link));
				mysqli_commit($this->link);
			}
		}

		//Pour les achats
		$le_tab = array();
		$sql = "SELECT DISTINCT b.amount, b.dateo FROM llx_bank as b, llx_bank_account as ba where b.rowid NOT IN (select bclass.lineid FROM llx_bank_class as bclass) AND b.amount < 0 AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ba.entity = '$this->entity';";
		//$sql = "SELECT DISTINCT b.amount, b.dateo FROM llx_bank as b, llx_bank_class as bclass, llx_bank_account as ba WHERE b.amount < 0 AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND bclass.lineid!=b.rowid AND ba.entity = '$this->entity';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["achat"] += $data['amount'];
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		foreach ($le_tab as $date_du_tab => $value) {
			foreach ($value as $catg => $la_valeur) {
				$sql = "UPDATE llx_tresorerie SET ".$catg."=".$la_valeur." where date >= '".$date_du_tab."-01' AND date <= '".$date_du_tab."-28' AND type = 'reel';";
				mysqli_query($this->link, $sql)or die(mysqli_error($this->link));
				mysqli_commit($this->link);
			}
		}

	}

	public function calcul_solde_tresorerie_reel_futur($categorie)
	{
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		if (date("m")<=10) {
			$date_temp_demande = date("Y")."-0".(date("m")+1);
		}
		else{
			$date_temp_demande = date("Y")."-".(date("m")+1);
		}
		
		
		$tab_tout = array();
		$tab_solde_prev = array();
		$solde_courant_reel = array();
		$ok = false;
		$query = "SELECT * FROM llx_tresorerie WHERE date>='".date("Y")."-01-01' AND type ='reel' ORDER BY date ASC";
		$res = mysqli_query($this->link, $query)or die(mysqli_error($this->link));
		while ($data = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
			$tab_tout[] = $data;
		}
		foreach ($tab_tout as $categorie_du_mois => $valeur) {
			$_les_dates_treso = explode("-",$valeur['date']);	
			if (($_les_dates_treso[0]."-".$_les_dates_treso[1]) >= $date_temp_demande) {
				if ($_les_dates_treso[1]==1) {
					$date_temp_a = $_les_dates_treso[0]."-12";
				}
				elseif ($_les_dates_treso[1]<=10) {
					$date_temp_a = $_les_dates_treso[0]."-0".($_les_dates_treso[1]-1);
				}
				elseif ($_les_dates_treso[1]>10){
					$date_temp_a = ($_les_dates_treso[0]."-".($_les_dates_treso[1]-1));
				}
				$query_1 = "SELECT soldeCourant, date FROM llx_tresorerie WHERE date>='".$date_temp_a."-01' AND type='reel'";
				$res_1 = mysqli_query($this->link, $query_1)or die(mysqli_error($this->link));
				while ($soldeDebut = mysqli_fetch_assoc($res_1)) {
					$solde_courant_reel[$soldeDebut['date']] = $soldeDebut['soldeCourant'];
				}
				foreach ($valeur as $les_categ => $value) {
					foreach ($solde_courant_reel as $key => $valueSoldeCourant) {
						$key = explode("-",$key);
						if (($key[0]."-".$key[1]) == $date_temp_a && !$ok) {
							$tab_solde_prev[$_les_dates_treso[0]."-".$_les_dates_treso[1]] = $valueSoldeCourant;
							$ok=true;
							$query_update_solde_debut = "UPDATE llx_tresorerie SET soldeDebut = '".$valueSoldeCourant."' WHERE date >= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-01' AND date <= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-28' AND type = 'reel';";
							mysqli_query($this->link, $query_update_solde_debut)or die(mysqli_error($this->link));
							mysqli_commit($this->link);
						}
					}
					foreach ($categorie as $categ) {
						$categ = str_replace($search, $replace, $categ);
						if ($categ == $les_categ) {
							$tab_solde_prev[$_les_dates_treso[0]."-".$_les_dates_treso[1]] += $value;
						}
					}
					if ($les_categ == "CA") {
						$tab_solde_prev[$_les_dates_treso[0]."-".$_les_dates_treso[1]] += $value;
					}
					elseif ($les_categ == "achat") {
						$tab_solde_prev[$_les_dates_treso[0]."-".$_les_dates_treso[1]] += $value;
					}
				}
				$ok = false;
				$query_update_solde_courant = "UPDATE llx_tresorerie SET soldeCourant = '".$tab_solde_prev[$_les_dates_treso[0]."-".$_les_dates_treso[1]]."' WHERE date >= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-01' AND date <= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-28' AND type = 'reel';";
				mysqli_query($this->link, $query_update_solde_courant);
				mysqli_commit($this->link);

			}
		}
	}

	public function calcul_pourcentage_ca_par_ca_n_moins_1($date_param = 0)
	{
		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}

		$sql = "SELECT CA, date FROM llx_tresorerie where type='reel' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_treso = array();
		$i = 0;
		while($data = mysqli_fetch_assoc($res)){
			$data['date'] = explode("-", $data['date']);
			$tableau_treso[$data['date'][0]."-".$data['date'][1]] = $data['CA'];
		}

		foreach ($tableau_treso as $date_ca => $ca) {
			$_les_dates_treso = explode("-", $date_ca);
			if ($date_ca >= $date_temp_demande) {
				if (empty($tableau_treso[($_les_dates_treso[0]-1)."-".$_les_dates_treso[1]])) {
					$pourcentage[$i] = -100;
				}else{
					$pourcentage[$i] = round((($tableau_treso[$date_ca]-$tableau_treso[($_les_dates_treso[0]-1)."-".$_les_dates_treso[1]])/$tableau_treso[($_les_dates_treso[0]-1)."-".$_les_dates_treso[1]])*100, 0);
				}
				$i++;
			}
		}
		return $pourcentage;
	}


	//Calcul du taux de marge 
	public function calcul_taux_de_marge($date_param = 0)
	{
		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}

		$sql = "SELECT CA, date FROM llx_tresorerie where type='reel' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_ca = array();
		while($data = mysqli_fetch_assoc($res)){
			$data['date'] = explode("-", $data['date']);
			$tableau_ca[$data['date'][0]."-".$data['date'][1]] = $data['CA'];
		}

		$sql = "SELECT achat, date FROM llx_tresorerie where type='reel' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_achat = array();
		$i = 0;
		while($data = mysqli_fetch_assoc($res)){
			$data['date'] = explode("-", $data['date']);
			$tableau_achat[$data['date'][0]."-".$data['date'][1]] = $data['achat'];
		}

		foreach ($tableau_ca as $date_ca => $ca) {
			$_les_dates_ca = explode("-", $date_ca);
			if ($date_ca >= $date_temp_demande) {
				if (empty($tableau_achat[$date_ca])) {
					$taux_de_marge[$i] = 0;
				}else{
					$taux_de_marge[$i] = round((($tableau_ca[$date_ca]+$tableau_achat[$date_ca])/-$tableau_achat[$date_ca])*100, 0);
				}	
				$i++;
			}
		}
		return $taux_de_marge;
	}

	//Calcul CA cumulé
	public function calcul_ca_cumule($date_param = 0)
	{
		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}

		$sql = "SELECT CA, date FROM llx_tresorerie where type='reel' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_ca = array();
		while($data = mysqli_fetch_assoc($res)){
			$data['date'] = explode("-", $data['date']);
			$tableau_ca[$data['date'][0]."-".$data['date'][1]] = $data['CA'];
		}

		foreach ($tableau_ca as $key => $value) {
			$key = explode("-", $key);
			if ($key[0] < date("Y")) {
				if($key[1]<=10){
					$cumul_N_1[$key[0]."-".$key[1]] = $tableau_ca[$key[0]."-".$key[1]]+$cumul_N_1[$key[0]."-0".($key[1]-1)];
				}
				else{
					$cumul_N_1[$key[0]."-".$key[1]] = $tableau_ca[$key[0]."-".$key[1]]+$cumul_N_1[$key[0]."-".($key[1]-1)];
				}
			}
			elseif($key[0]==date("Y")){
				if($key[1]<=10){
					$cumul_N[$key[0]."-".$key[1]] = $tableau_ca[$key[0]."-".$key[1]]+$cumul_N[$key[0]."-0".($key[1]-1)];
				}
				else{
					$cumul_N[$key[0]."-".$key[1]] = $tableau_ca[$key[0]."-".$key[1]]+$cumul_N[$key[0]."-".($key[1]-1)];
				}
			}
			elseif ($key[0]==date("Y")+1) {
				if($key[1]<=10){
					$cumul_N_1_plus[$key[0]."-".$key[1]] = $tableau_ca[$key[0]."-".$key[1]]+$cumul_N_1_plus[$key[0]."-0".($key[1]-1)];
				}
				else{
					$cumul_N_1_plus[$key[0]."-".$key[1]] = $tableau_ca[$key[0]."-".$key[1]]+$cumul_N_1_plus[$key[0]."-".($key[1]-1)];
				}
				
			}
		}
		$i = 0;
		foreach ($cumul_N_1 as $date_ca => $ca) {
			$_les_dates_ca = explode("-", $date_ca);
			if ($date_ca >= $date_temp_demande) {
				$cumul[$i] = 0;
				$i++;
			}
		}
		foreach ($cumul_N as $date_ca => $ca) {
			$_les_dates_ca = explode("-", $date_ca);
			if ($date_ca >= $date_temp_demande) {
				foreach ($cumul_N_1 as $key => $value) {
					$ret = explode("-", $key);
					if($ret[1]==$_les_dates_ca[1]){
						if ($cumul_N_1[$key]==0) {
							$cumul[$i] = -100;
						}else{
							$cumul[$i] = round((($cumul_N[$date_ca]-$cumul_N_1[$key])/$cumul_N_1[$key])*100, 0);
						}
					}
				}
				$i++;
			}
		}
		foreach ($cumul_N_1_plus as $date_ca => $ca) {
			$_les_dates_ca = explode("-", $date_ca);
			if ($date_ca >= $date_temp_demande) {
				foreach ($cumul_N as $key => $value) {
					$ret = explode("-", $key);
					if($ret[1]==$_les_dates_ca[1]){
						if ($cumul_N[$key] == 0) {
							$cumul[$i] = -100;
						}else{
							$cumul[$i] = round((($cumul_N_1_plus[$date_ca]-$cumul_N[$key])/$cumul_N[$key])*100, 0);
						}
					}
				}
				$i++;
			}
		}
		return $cumul;
	}

	public function up_tresorerie_charge_fixe($categ)
	{
		$tableau_montant_categorie = array();
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		$tab1 = array();
		$sql = "SELECT DISTINCT b.rowid, bcat.label, b.amount, b.dateo FROM llx_bank_categ as bcat, llx_bank_class as bclass, llx_bank_account as ba, llx_bank as b, llx_facture as f, llx_categ_tva as ct, llx_c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND ct.fk_c_tva = t.rowid AND ct.fk_bank_categ = bcat.rowid AND b.amount<=0 ORDER BY b.dateo ASC;";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while($data = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			$tab1[] = $data['label'];
			$tableau_montant_categorie[] = $data;
		}
		foreach ($categ as $value) {
			$value = str_replace($search, $replace, $value);
			$query = "UPDATE llx_tresorerie SET ".$value."=NULL where type ='reel';";
			mysqli_query($this->link ,$query);
		}

		$sql2 = "SELECT DISTINCT date FROM llx_tresorerie order by date asc;";
		$res2 = mysqli_query($this->link ,$sql2) or die (mysqli_error($this->link));
		while($data = mysqli_fetch_assoc($res2)){
			$_la_date = explode("-", $data['date']);
			$tableau_des_dates[] = $_la_date[0]."-".$_la_date[1];
		}

		$tab_insert = array();
		$tableau_des_dates = array();
		$tableau_des_categories = array();
		foreach ($tableau_montant_categorie as $tableau_categ) {
			$tableau_categ['label'] = str_replace($search, $replace, $tableau_categ['label']);
			$date_mois = explode("-" ,$tableau_categ['dateo']);
			$date_par_mois = $date_mois[0]."-".$date_mois[1];
			if (in_array($date_par_mois, $tableau_des_dates)) {
				$tab_insert[$tableau_categ['label']] += $tableau_categ['amount'];
				$query = "UPDATE llx_tresorerie SET ".$tableau_categ['label']." = ".$tab_insert[$tableau_categ['label']]." WHERE date >='".$date_par_mois."-01' AND date<='".$date_par_mois."-28' AND type='reel';";
			}
			else{
				$tab_insert = array();
				$tab_insert[$tableau_categ['label']] += $tableau_categ['amount'];
				$query = "UPDATE llx_tresorerie SET ".$tableau_categ['label']." = ".$tab_insert[$tableau_categ['label']]." WHERE date >='".$date_par_mois."-01' AND date<='".$date_par_mois."-28' AND type='reel';";
			}
			array_push($tableau_des_dates, $date_par_mois);
			mysqli_query($this->link, $query) or die (mysqli_error($this->link));
		}
		mysqli_commit($this->link);
	}

	public function getEncoursFournisseur()
	{
		$sql3 = "SELECT f.total_ttc, f.date_lim_reglement as dlr, s.nom FROM llx_facture_fourn as f LEFT JOIN llx_societe as s ON f.fk_soc = s.rowid WHERE f.entity = 1 AND f.paye = 0 AND f.fk_statut = 1 ORDER BY dlr ASC";
		$res = mysqli_query($this->link ,$sql3) or die (mysqli_error($this->link));
		$tableau_encours_fourn = array();
		while($data = mysqli_fetch_assoc($res)){
			$tableau_encours_fourn[$data['dlr']] = $data['total_ttc'];
		}
		return $tableau_encours_fourn;
		mysqli_commit($this->link);
	}

	public function getTVACollecte($date_param = 0)
	{
		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}

		$sql = "SELECT CA, date FROM llx_tresorerie where type='reel' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_ca = array();
		while($data = mysqli_fetch_assoc($res)){
			$data['date'] = explode("-", $data['date']);
			$tableau_ca[$data['date'][0]."-".$data['date'][1]] = $data['CA'];
		}

		$tva_collecte = array();
		foreach ($tableau_ca as $date_ca => $ca) {
			$_les_dates_ca = explode("-", $date_ca);
			if ($date_ca >= $date_temp_demande) {
				$tva_collecte[$i] = $ca * 0.2;
				$i++;
			}
		}
		return $tva_collecte;
	}

	public function getTVADeductible($date_param = 0)
	{
		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}

		$sql = "SELECT achat, date FROM llx_tresorerie where type='reel' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_achat = array();
		while($data = mysqli_fetch_assoc($res)){
			$data['date'] = explode("-", $data['date']);
			$tableau_achat[$data['date'][0]."-".$data['date'][1]] = $data['achat'];
		}
		
		$tva_due = array();
		foreach ($tableau_achat as $date_achat => $achat) {
			$_les_dates_achat = explode("-", $date_achat);
			if ($date_achat >= $date_temp_demande) {
				$tva_due[$i] = $achat * 0.2;
				$i++;
			}
		}
		return $tva_due;
	}

	public function cumul_CA($date_param = 0)
	{
		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}

		$sql = "SELECT CA, date FROM llx_tresorerie where type='reel' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_ca = array();
		while($data = mysqli_fetch_assoc($res)){
			$data['date'] = explode("-", $data['date']);
			$tableau_ca[$data['date'][0]."-".$data['date'][1]] = $data['CA'];
		}

		$cumul_CA = 0;
		$i=0;
		foreach ($tableau_ca as $date_ca => $ca) {
			$_les_dates_ca = explode("-", $date_ca);
			if ($date_ca >= $date_temp_demande && $i<12) {
				$cumul_CA += $ca;
				$i++;
			}
		}
		return $cumul_CA;
	}

	public function cumul_CA_prev($date_param = 0)
	{
		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}

		$sql = "SELECT CA, date FROM llx_tresorerie where type='prev' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_ca = array();
		while($data = mysqli_fetch_assoc($res)){
			$data['date'] = explode("-", $data['date']);
			$tableau_ca[$data['date'][0]."-".$data['date'][1]] = $data['CA'];
		}

		$cumul_CA = 0;
		$i=0;
		foreach ($tableau_ca as $date_ca => $ca) {
			$_les_dates_ca = explode("-", $date_ca);
			if ($date_ca >= $date_temp_demande && $i<12) {
				$cumul_CA += $ca;
				$i++;
			}
		}
		return $cumul_CA;
	}

	public function cumul_achat($date_param = 0)
	{
		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}

		$sql = "SELECT achat, date FROM llx_tresorerie where type='reel' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_achat = array();
		while($data = mysqli_fetch_assoc($res)){
			$data['date'] = explode("-", $data['date']);
			$tableau_achat[$data['date'][0]."-".$data['date'][1]] = $data['achat'];
		}

		$cumul_achat = 0;
		$i = 0;
		foreach ($tableau_achat as $date_achat => $achat) {
			$_les_dates_achat = explode("-", $date_achat);
			if ($date_achat >= $date_temp_demande && $i<12) {
				$cumul_achat += $achat;
				$i++;
			}
		}
		return $cumul_achat;
	}

	public function cumul_achat_prev($date_param = 0)
	{
		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}

		$sql = "SELECT achat, date FROM llx_tresorerie where type='prev' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_achat = array();
		while($data = mysqli_fetch_assoc($res)){
			$data['date'] = explode("-", $data['date']);
			$tableau_achat[$data['date'][0]."-".$data['date'][1]] = $data['achat'];
		}

		$cumul_achat = 0;
		$i = 0;
		foreach ($tableau_achat as $date_achat => $achat) {
			$_les_dates_achat = explode("-", $date_achat);
			if ($date_achat >= $date_temp_demande && $i<12) {
				$cumul_achat += $achat;
				$i++;
			}
		}
		return $cumul_achat;
	}

	public function cumul_Charge($taux, $date_param = 0)
	{
		$tresoReel_HT = array();
		$date_decoupe = explode("/", $date_param);
		$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
		$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]-01";
		$date_temp_a = explode("-", $this->date);
		$date_temp_courant = "$date_temp_a[0]-$date_temp_a[1]-01";
		$sql = ($date == 0) ?  "SELECT * FROM llx_tresorerie as t WHERE t.date>='$this->dateD-01' AND t.type='reel' ORDER BY t.date ASC;":  "SELECT * FROM llx_tresorerie as t WHERE t.date>='$date' AND t.type='reel' ORDER BY t.date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$nb = mysqli_num_fields($res);
		$tab1 = array();
		$j = array();
		while ($data = mysqli_fetch_row($res)) {
			for ($i=0; $i < $nb; $i++) {
				$finfo = mysqli_fetch_field_direct($res, $i);
				foreach ($taux as $categTaux => $valueTaux) {
					if($categTaux == $finfo->name){
						if ($j[$finfo->name]<12) {
							if ($data[$i] != NULL) {
								$tab1[$finfo->name] += round($data[$i]*(100/($valueTaux+100)), 2);
							}
						}
						$j[$finfo->name]++;
					}
				}
			}
		}
		return $tab1;
	}

	public function cumul_Charge_prev($taux, $date_param = 0)
	{
		$tresoReel_HT = array();
		$date_decoupe = explode("/", $date_param);
		$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
		$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]-01";
		$date_temp_a = explode("-", $this->date);
		$date_temp_courant = "$date_temp_a[0]-$date_temp_a[1]-01";
		$sql = ($date == 0) ?  "SELECT * FROM llx_tresorerie as t WHERE t.date>='$this->dateD-01' AND t.type='prev' ORDER BY t.date ASC;":  "SELECT * FROM llx_tresorerie as t WHERE t.date>='$date' AND t.type='prev' ORDER BY t.date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$nb = mysqli_num_fields($res);
		$tab1 = array();
		$j = array();
		while ($data = mysqli_fetch_row($res)) {
			for ($i=0; $i < $nb; $i++) {
				$finfo = mysqli_fetch_field_direct($res, $i);
				foreach ($taux as $categTaux => $valueTaux) {
					if($categTaux == $finfo->name){
						if ($j[$finfo->name]<12) {
							//if ($data[$i] != NULL) {
								$tab1[$finfo->name] += round($data[$i], 2);
							//}
						}
						$j[$finfo->name]++;
					}
				}
			}
		}
		return $tab1;
	}

	public function cumul_total_charge($total_charge)
	{
		$cumul_total_charge = 0;
		foreach ($total_charge as $valeur) {
			$cumul_total_charge += $valeur;
		}
		return $cumul_total_charge;
	}

	public function cumul_total_charge_prev($total_charge_prev)
	{
		$cumul_total_charge_prev = 0;
		foreach ($total_charge_prev as $valeur) {
			$cumul_total_charge_prev += $valeur;
		}
		return $cumul_total_charge_prev;
	}

	public function cumul_solde_tresorerie($date_param = 0)
	{
		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}

		$sql = "SELECT soldeCourant, date FROM llx_tresorerie where type='reel' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_solde = array();
		while($data = mysqli_fetch_assoc($res)){
			$data['date'] = explode("-", $data['date']);
			$tableau_solde[$data['date'][0]."-".$data['date'][1]] = $data['soldeCourant'];
		}
		$cumul_solde_tresorerie = 0;
		$i = 0;
		foreach ($tableau_solde as $date_solde => $solde) {
			$_les_dates_solde = explode("-", $date_solde);
			if ($date_solde >= $date_temp_demande && $i<12) {
				$cumul_solde_tresorerie += $solde;
				$i++;
			}
		}
		return $cumul_solde_tresorerie;
	}

	public function cumul_solde_tresorerie_prev($date_param = 0)
	{
		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}

		$sql = "SELECT soldeCourant, date FROM llx_tresorerie where type='prev' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_solde = array();
		while($data = mysqli_fetch_assoc($res)){
			$data['date'] = explode("-", $data['date']);
			$tableau_solde[$data['date'][0]."-".$data['date'][1]] = $data['soldeCourant'];
		}
		$cumul_solde_tresorerie = 0;
		$i = 0;
		foreach ($tableau_solde as $date_solde => $solde) {
			$_les_dates_solde = explode("-", $date_solde);
			if ($date_solde >= $date_temp_demande && $i<12) {
				$cumul_solde_tresorerie += $solde;
				$i++;
			}
		}
		return $cumul_solde_tresorerie;
	}

	//3 methode en desous pas utile pour l'instant, la methode getSolde() fait cela !!
/*	public function up_tresorerie_Achat()
	{
		$sql = "SELECT b.amount, b.dateo FROM llx_bank as b, llx_bank_account as ba WHERE b.amount < 0 AND ba.entity = '$this->entity' order by b.dateo ASC";
		$res = mysqli_query($sql) or die (mysqli_error());
		while($data = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			$tableau_montant_categorie[] = $data;
		}
		$tableau_des_dates[] = array();
		$tab_select = array();
		foreach ($tableau_montant_categorie as $tableau_categ) {
			$date_mois = explode("-" ,$tableau_categ['dateo']);
			$date_par_mois = $date_mois[0]."-".$date_mois[1];
			if ($date_par_mois == date("Y-m")) {
				$tab_select[$date_par_mois] += $tableau_categ['amount'];
			}
			else{
				$tab_select[$date_par_mois] += $tableau_categ['amount'];
			}
			
			$tableau_des_dates[] = $date_par_mois;
		}
		return $tab_select;
	}

	public function up_tresorerie_CA()
	{
		$sql = "SELECT b.amount, b.dateo FROM llx_bank as b, llx_bank_account as ba WHERE b.amount > 0 AND ba.entity = '$this->entity' order by b.dateo ASC";
		$res = mysqli_query($sql) or die (mysqli_error());
		while($data = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			$tableau_montant_categorie[] = $data;
		}
		$tableau_des_dates[] = array();
		$tab_select = array();
		foreach ($tableau_montant_categorie as $tableau_categ) {
			$date_mois = explode("-" ,$tableau_categ['dateo']);
			$date_par_mois = $date_mois[0]."-".$date_mois[1];
			if ($date_par_mois == date("Y-m")) {
				$tab_select[$date_par_mois] += $tableau_categ['amount'];
			}
			else{
				$tab_select[$date_par_mois] += $tableau_categ['amount'];
			}
			
			$tableau_des_dates[] = $date_par_mois;
		}
		return $tab_select;
	}



	public function calcul_solde_tresorerie($tab_ca, $tab_achat)
	{
		if (date("Y-m") == date("Y")."-01") {
			$query = "SELECT b.amount, b.dateo FROM llx_bank as b where fk_type='SOLD' order by dateo ASC;";
			$res = mysqli_query($query)or die(mysqli_error());
			while ($data = mysqli_fetch_assoc($res)) {
				$dat = explode("-", $data['dateo']);
				$tab_sold_init[$dat[0]."-".$dat[1]] = $data['amount'];
			}
			$le_calcul = round($tab_sold_init[$date]+$tab_ca[$date]+$tab_achat[$date], 2);
		}
		else{
			$query = "SELECT soldeCourant FROM llx_tresorerie WHERE date >= '".date("Y")."-".(date("m")-1)."-01' AND date <= '".date("Y")."-".(date("m")-1)."-28' AND type='reel'";
			$res = mysqli_query($query)or die(mysqli_error());
			while ($data = mysqli_fetch_assoc($res)) {
				$le_solde_mois_precedent = $data['soldeCourant'];
			}
			$le_calcul = round($le_solde_mois_precedent+$tab_ca[$date]+$tab_achat[$date], 2);
		}
	}*/
	
/*
	public function getCharge_test($categ, $taux, $date_param = 0)
	{
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}
		$sql = "SELECT * FROM llx_tresorerie where type='reel' ORDER BY date ASC";
		$res = mysqli_query($sql) or die (mysqli_error());
		$tableau_treso = array();
		$i = 0;
		while($data = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			$tableau_treso[] = $data;
		}

		foreach ($tableau_treso as $key => $tableau_treso_mois) {
			foreach ($tableau_treso_mois as $categorie_du_mois => $valeur) {
				foreach ($taux as $categTaux => $valueTaux) {
					if ($categTaux == $categorie_du_mois) {
						$tableau_treso_mois[$categorie_du_mois] = round($valeur*(100/($valueTaux+100)), 2);
					}
				}
			}
			$tableau_treso[$key] = $tableau_treso_mois;
		}

		foreach ($tableau_treso as $tableau_treso_mois) {
			$_les_dates_treso = explode("-",$tableau_treso_mois['date']);
			//if (($_les_dates_treso[0]."-".$_les_dates_treso[1]) != $date_temp_demande) {
				foreach ($tableau_treso_mois as $categorie_du_mois => $valeur) {
					foreach ($categ as $key => $value) {
						$value = str_replace($search, $replace, $value);
						if ($value == $categorie_du_mois) {
							$charge_reel[$_les_dates_treso[0]."-".$_les_dates_treso[1]] += $valeur;
						}
					}
				}
				$i++;
			//}
		}
		return $charge_reel;
	}*/
}

?>
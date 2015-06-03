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
 *	\file       htdocs/tresorerie/class/initialisation.php
 *	\ingroup    tresorerie
 *	\brief      File to initialisation the module of tresorerie
 */
class initialisation extends CommonObject
{
	function __construct($leink)
	{
		global $conf, $langs;
		$this->link = $leink;
		$this->entity = $conf->entity;
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

	public function createTable()
	{
		$sql2 = "SELECT label FROM `llx_bank_categ` ORDER BY `label` ASC;";

		// on envoie la requête
		$req = mysqli_query($this->link, $sql2) or die('Erreur SQL !<br>'.$sql2.'<br>'.mysqli_error($this->link));

		$test = "create table if not exists llx_tresorerie(rowid integer AUTO_INCREMENT PRIMARY KEY,";
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		// on fait une boucle qui va faire un tour pour chaque enregistrement
		while($data = mysqli_fetch_assoc($req))
	    {
	      	$res = str_replace($search, $replace, $data['label']);
	      	$test .= $res." double,";      
	    }

		$test.="soldeDebut double, soldeCourant double, CA double, achat double, date DATE, type VARCHAR(24))ENGINE=innodb;";
		$req2 = mysqli_query($this->link, $test) or die('Erreur SQL !<br>'.$sql2.'<br>'.mysqli_error($this->link));

		$query = "SELECT bc.rowid FROM llx_bank_categ as bc WHERE bc.rowid NOT IN (SELECT ct.fk_bank_categ FROM llx_categ_tva as ct);";
		$req3 = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		$query2;
		while ($data = mysqli_fetch_assoc($req3)) {
			$query2 = "INSERT INTO llx_categ_tva (fk_c_tva, fk_bank_categ) VALUES (11, ".$data['rowid'].");";
			mysqli_query($this->link, $query2);
		}
	}

	public function getCharge_test($categ)
	{
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		$sql = "SELECT * FROM llx_tresorerie where type='reel' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_treso = array();
		while($data = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			$tableau_treso[] = $data;
		}
		$charge_reel = array();
		if (!empty($categ)) {
			foreach ($tableau_treso as $tableau_treso_mois) {
				$_les_dates_treso = explode("-",$tableau_treso_mois['date']);
				foreach ($tableau_treso_mois as $categorie_du_mois => $valeur) {
					foreach ($categ as $key => $value) {
						$value = str_replace($search, $replace, $value);
						if ($value == $categorie_du_mois) {
							$charge_reel[$_les_dates_treso[0]."-".$_les_dates_treso[1]] += $valeur;
						}
					}
				}
			}
		}
		return $charge_reel;
	}

	public function up_tresorerie_charge_fixe()
	{
		$tableau_montant_categorie = array();
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		$sql = "SELECT DISTINCT b.rowid, bcat.label, b.amount, b.dateo FROM llx_bank_categ as bcat, llx_bank_class as bclass, llx_bank_account as ba, llx_bank as b, llx_facture as f, llx_categ_tva as ct, llx_c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND ct.fk_c_tva = t.rowid AND ct.fk_bank_categ = bcat.rowid AND b.amount<=0 ORDER BY b.dateo ASC;";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while($data = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			$tableau_montant_categorie[] = $data;
		}
		$sql2 = "SELECT DISTINCT date FROM llx_tresorerie order by date asc;";
		$res2 = mysqli_query($this->link, $sql2) or die (mysqli_error($this->link));
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
				$query = "UPDATE llx_tresorerie SET ".$tableau_categ['label']." = ".$tab_insert[$tableau_categ['label']]." WHERE date >= '".$date_par_mois."-01' AND date <= '".$date_par_mois."-28' AND type='reel';";
			}
			else{
				$tab_insert = array();
				$tab_insert[$tableau_categ['label']] += $tableau_categ['amount'];
				$query = "UPDATE llx_tresorerie SET ".$tableau_categ['label']." = ".$tab_insert[$tableau_categ['label']]." WHERE date >= '".$date_par_mois."-01' AND date <= '".$date_par_mois."-28' AND type='reel';";
			}
			array_push($tableau_des_dates, $date_par_mois);
			mysqli_query($this->link, $query) or die (mysqli_error($this->link));
		} 
	}

	public function up_tresorerie_CA()
	{
		$sql = "SELECT DISTINCT(b.rowid), b.amount, b.dateo FROM llx_bank as b, llx_bank_account as ba WHERE b.amount > 0 AND ba.entity = '$this->entity' ORDER BY b.dateo ASC;";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while($data = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			$tableau_montant_categorie[] = $data;
		}
		$tableau_des_dates[] = array();
		$tab_insert = array();
		$tab_select = array();
		foreach ($tableau_montant_categorie as $tableau_categ) {
			$date_mois = explode("-" ,$tableau_categ['dateo']);
			$date_par_mois = $date_mois[0]."-".$date_mois[1];
			if (in_array($date_par_mois, $tableau_des_dates)) {
				$tab_insert['ca'] += $tableau_categ['amount'];
				$tab_select[$date_par_mois] += $tableau_categ['amount'];
				if (!isset($tab_insert['ca'])) {
					$query = "UPDATE llx_tresorerie SET CA = NULL WHERE "."date >='".$date_par_mois."-01' AND date <= '".$date_par_mois."-28' AND type='reel';";
				}else{
					$query = "UPDATE llx_tresorerie SET CA = ".$tab_insert['ca']." WHERE date >= '".$date_par_mois."-01' AND date <= '".$date_par_mois."-28' AND type='reel';";
				}
			}
			else{
				$tab_insert = array();
				$tab_select[$date_par_mois] += $tableau_categ['amount'];
				$tab_insert['ca'] += $tableau_categ['amount'];
				if (!isset($tab_insert['ca'])) {
					$query = "UPDATE llx_tresorerie SET CA = NULL WHERE "." date >='".$date_par_mois."-01' AND date<='".$date_par_mois."-28' AND type='reel';";
				}else{
					$query = "UPDATE llx_tresorerie SET CA = ".$tab_insert['ca']." WHERE date >='".$date_par_mois."-01' AND date<='".$date_par_mois."-28' AND type='reel';";
				}
			}
			mysqli_query($this->link, $query);
			$tableau_des_dates[] = $date_par_mois;
		}
		return $tab_select;
	}

	public function up_tresorerie_Achat()
	{
		$sql = "SELECT b.amount, b.dateo FROM llx_bank as b, llx_bank_account as ba where b.rowid NOT IN (select bclass.lineid FROM llx_bank_class as bclass) AND b.amount < 0 AND ba.entity = '$this->entity' ORDER BY b.dateo ASC;";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_montant_categorie = array();
		while($data = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			$tableau_montant_categorie[] = $data;
		}
		$tableau_des_dates[] = array();
		$tab_insert = array();
		$tab_select = array();
		foreach ($tableau_montant_categorie as $tableau_categ) {
			$date_mois = explode("-" ,$tableau_categ['dateo']);
			$date_par_mois = $date_mois[0]."-".$date_mois[1];
			if (in_array($date_par_mois, $tableau_des_dates)) {
				$tab_insert['achat'] += $tableau_categ['amount'];
				$tab_select[$date_par_mois] += $tableau_categ['amount'];
				if (!isset($tab_insert['achat'])) {
					$query = "UPDATE llx_tresorerie SET achat = NULL WHERE "." date >='".$date_par_mois."-01' AND date<='".$date_par_mois."-28' AND type='reel';";
				}else{
					$query = "UPDATE llx_tresorerie SET achat = ".$tab_insert['achat']." WHERE date >='".$date_par_mois."-01' AND date<='".$date_par_mois."-28' AND type='reel';";
				}
			}
			else{
				$tab_insert = array();
				$tab_select[$date_par_mois] += $tableau_categ['amount'];
				$tab_insert['achat'] += $tableau_categ['amount'];
				if (!isset($tab_insert['achat'])) {
					$query = "UPDATE llx_tresorerie SET achat = NULL WHERE "." date >='".$date_par_mois."-01' AND date<='".$date_par_mois."-28' AND type='reel';";
				}else{
					$query = "UPDATE llx_tresorerie SET achat = ".$tab_insert['achat']." WHERE date >='".$date_par_mois."-01' AND date<='".$date_par_mois."-28' AND type='reel';";
				}
			}
			mysqli_query($this->link, $query);
			$tableau_des_dates[] = $date_par_mois;
		}
		return $tab_select;
	}

	public function calcul_solde_tresorerie($tab_ca, $tab_achat, $tab_charge)
	{
		$annee = date("Y");
		$annee--;
		$query = "SELECT b.amount, b.dateo FROM llx_bank as b where fk_type='SOLD' order by dateo ASC;";
		$res = mysqli_query($this->link, $query)or die(mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$dat = explode("-", $data['dateo']);
			$tab_sold_init[$dat[0]."-".$dat[1]] = $data['amount'];
		}
		for ($i=$annee; $i <= date("Y") ; $i++) { 
			for ($j=1; $j <= 12 ; $j++) { 
				if ($j<10) {
					$date= $i."-0".$j;
				}
				else{
					$date= $i."-".$j;
				}
				if ($date == $i."-01") {
					$le_calcul = round($tab_sold_init[$date]+$tab_ca[$date]+$tab_achat[$date]+$tab_charge[$date], 2);
					$queryCourant = "UPDATE llx_tresorerie SET soldeCourant = ".$le_calcul." WHERE date >= '".$date."-01' AND date <= '".$date."-28' AND type='reel';";
					$queryDebut = "UPDATE llx_tresorerie SET soldeDebut = ".$tab_sold_init[$date]." WHERE date >= '".$date."-01' AND date <= '".$date."-28' AND type='reel';";

				}
				elseif ($date<= date("Y-m")) {
					$queryDebut = "UPDATE llx_tresorerie SET soldeDebut = ".$le_calcul." WHERE date >= '".$date."-01' AND date <= '".$date."-28' AND type='reel';";
					$le_calcul = round($le_calcul+$tab_ca[$date]+$tab_achat[$date]+$tab_charge[$date], 2);
					$queryCourant = "UPDATE llx_tresorerie SET soldeCourant = ".$le_calcul." WHERE date >= '".$date."-01' AND date <= '".$date."-28' AND type='reel';";
				}
				mysqli_query($this->link, $queryCourant)or die(mysqli_error($this->link));
				mysqli_query($this->link, $queryDebut)or die(mysqli_error($this->link));
			}
		}
	}

	public function ajout_date_tresorerie()
	{
		$mois_prev_annee_courante = array();
		$mois_prev_annee_suivante = array();
		$mois_prev_annee_precedente = array();
		$mois_reel_annee_courante = array();
		$mois_reel_annee_suivante = array();
		$mois_reel_annee_precedente = array();

        $anneeCourante = date("Y");
		$anneeSuivante = $anneeCourante+1;
		$anneePrecedente = $anneeCourante-1;
		$query_verif_prev = "SELECT DISTINCT date FROM llx_tresorerie WHERE type='prev' ORDER BY 'date' ASC";
		$rep_prev = mysqli_query($this->link, $query_verif_prev)or die(mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($rep_prev)) {
			if ($data['date']< $anneeSuivante."-01-01" && $data['date'] >= $anneeCourante."-01-01") {
				$mois_prev_annee_courante[] = explode("-", $data['date'])[1];
			}
			elseif($data['date']< $anneeCourante."-01-01") {
				$mois_prev_annee_precedente[] = explode("-", $data['date'])[1];
			}
			else{
				$mois_prev_annee_suivante[] = explode("-", $data['date'])[1];
			}
		}
		$les_dates = array("01","02","03","04","05","06","07","08","09","10","11","12");
		$_bg1 = array_diff($les_dates, $mois_prev_annee_courante);
		$_bg2 = array_diff($les_dates, $mois_prev_annee_suivante);
		$_bg3 = array_diff($les_dates, $mois_prev_annee_precedente);

		foreach ($_bg1 as $key => $value) {
			$query_rempli_prev = "INSERT into llx_tresorerie (date, type) VALUES ('".$anneeCourante."-".$value."-28', 'prev')";
			mysqli_query($this->link, $query_rempli_prev)or die(mysqli_error($this->link));	
		}
		foreach ($_bg2 as $key => $value) {
			$query_rempli_prev = "INSERT into llx_tresorerie (date, type) VALUES ('".$anneeSuivante."-".$value."-28', 'prev')";
			mysqli_query($this->link, $query_rempli_prev)or die(mysqli_error($this->link));	
		}
		foreach ($_bg3 as $key => $value) {
			$query_rempli_prev = "INSERT into llx_tresorerie (date, type) VALUES ('".$anneePrecedente."-".$value."-28', 'prev')";
			mysqli_query($this->link, $query_rempli_prev)or die(mysqli_error($this->link));	
		}

		$query_verif_reel = "SELECT DISTINCT date FROM llx_tresorerie WHERE type='reel' ORDER BY 'date' ASC";
		$rep_reel = mysqli_query($this->link, $query_verif_reel)or die(mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($rep_reel)) {
			if($data['date']< $anneeSuivante."-01-01" && $data['date'] >= $anneeCourante."-01-01"){
				$mois_reel_annee_courante[] = explode("-", $data['date'])[1];
			}
			elseif($data['date']< $anneeCourante."-01-01") {
				$mois_reel_annee_precedente[] = explode("-", $data['date'])[1];
			}
			else{
				$mois_reel_annee_suivante[] = explode("-", $data['date'])[1];
			}
		}
		$_mois_ajout_courant = array_diff($les_dates, $mois_reel_annee_courante);
		$_mois_ajout_suivant = array_diff($les_dates, $mois_reel_annee_suivante);
		$_mois_ajout_precedente = array_diff($les_dates, $mois_reel_annee_precedente);

		foreach ($_mois_ajout_courant as $key => $value) {
			$query_rempli_reel = "INSERT into llx_tresorerie (date, type) VALUES ('".$anneeCourante."-".$value."-28', 'reel')";
			mysqli_query($this->link, $query_rempli_reel)or die(mysqli_error($this->link));	
		}
		foreach ($_mois_ajout_suivant as $key => $value) {
			$query_rempli_reel = "INSERT into llx_tresorerie (date, type) VALUES ('".$anneeSuivante."-".$value."-28', 'reel')";
			mysqli_query($this->link, $query_rempli_reel)or die(mysqli_error($this->link));	
		}
		foreach ($_mois_ajout_precedente as $key => $value) {
			$query_rempli_reel = "INSERT into llx_tresorerie (date, type) VALUES ('".$anneePrecedente."-".$value."-28', 'reel')";
			mysqli_query($this->link, $query_rempli_reel)or die(mysqli_error($this->link));	
		}
	}

	//Mettre cette méthode dans un autre fichier et créer l'autre pour supprimer une colonne et enfin créer un bouton pour l'ajout et la suppression!!!
	public function ajoutCategorie($categ)
	{
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		$sql = "SELECT * FROM llx_tresorerie LIMIT 1";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$nb = mysqli_num_fields($res);
		$row = array();
		while ($data = mysqli_fetch_row($res)) {
			for ($i=0; $i < $nb; $i++) {
				$finfo = mysqli_fetch_field_direct($res, $i);
				$row[] = $finfo->name;
			}
		}
		foreach ($categ as $value) {
			if ($value != NULL) {
				if (!in_array($value, $row)) {
					$sql2 = "ALTER TABLE llx_tresorerie ADD $value DOUBLE AFTER rowid;";
					mysqli_query($this->link, $sql2) or die (mysqli_error($this->link));
				}
			}
		}
	}

	public function supprmierCategorie($categ)
	{
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		$sql = "SELECT * FROM llx_tresorerie LIMIT 1";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$nb = mysqli_num_fields($res);
		$row = array();
		while ($data = mysqli_fetch_row($res)) {
			for ($i=0; $i < $nb; $i++) {
				$finfo = mysqli_fetch_field_direct($res, $i);
				$row[] = $finfo->name;
			}
		}
		foreach ($row as $value) {
			if ($value != NULL) {
				if (!in_array($value, $categ) && $value != "CA" && $value != "rowid" && $value != "date" && $value != "soldeCourant" && $value != "soldeDebut" && $value != "type" && $value != "achat") {
					$sql2 = "ALTER TABLE llx_tresorerie DROP $value;";
					mysqli_query($this->link, $sql2) or die (mysqli_error($this->link));
				}
			}
		}
	}


}

?>
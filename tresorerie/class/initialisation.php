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
 *	This file is used for the module initialization
 */

/**
 *	This class is initialization of module, it create a new table in database, and insert the elements necessary at module
 *
 *	@filesource /htdocs/tresorerie/class/initialisation.php
 *	@package Class
 *	@licence http://www.gnu.org/licenses/ GPL
 *	@version Version 1.0
 *	@author Maël Quémard
 */
class initialisation extends CommonObject
{
	/**
	 *	This is the contructor of class, get the link of connection (database)
	 *	
	 *	@param Object $leink
	 *	@global object $conf
	 *	@global object $langs
	 */
	function __construct($leink)
	{
		global $conf, $langs;
		$this->link = $leink;
		$this->entity = $conf->entity;
	}

	/**
	 *	This method get all category (fixed charges)
	 *	
	 *	@return array $this->categorie
	 */
	public function getCategorie()
	{
		$query_categ = "SELECT label FROM `llx_bank_categ` ORDER BY `label` ASC;";
		$resultat = mysqli_query($this->link, $query_categ) or die (mysqli_error($this->link));
		while ($data= mysqli_fetch_assoc($resultat)) {
			if ($data['label'] != "CA Ventes 0" && $data['label'] != "CA Ventes 10" && $data['label'] != "CA Ventes 20" && $data['label'] != "Achats 0" && $data['label'] != "Achats 10" && $data['label'] != "Achats 20") {
				$this->categorie[] = $data['label'];
			}
		}
		return $this->categorie;
	}

	/**
	*	This method get lines numbers
	*	
	*	@return int $nb
	*/
	public function getNbLignes()
	{
		$query_categ = "SELECT label FROM `llx_bank_categ` ORDER BY `label` ASC;";
		$resultat = mysqli_query($this->link, $query_categ) or die (mysqli_error($this->link));
		$nb = mysqli_num_rows($resultat);
		return $nb;
	}

	/**
	*	This method create table need to module, and insert values in the table categ_tva
	*
	*	@return void
	*/
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

		$query = "SELECT bc.label, bc.rowid FROM llx_bank_categ as bc where bc.label ='CA Ventes 0'";
		$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		$data = mysqli_fetch_array($rep);
		if (empty($data)) {
			$query = "INSERT INTO llx_bank_categ (label, entity) VALUES ('CA Ventes 0', $this->entity)";
			$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		}
		$query = "SELECT bc.rowid FROM llx_bank_categ  as bc where bc.label ='CA Ventes 0' AND bc.rowid NOT IN (SELECT ct.fk_bank_categ FROM llx_categ_tva as ct);";
		$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		$data = mysqli_fetch_array($rep);
		if (!empty($data)) {
			$query2 = "INSERT INTO llx_categ_tva (fk_c_tva, fk_bank_categ) VALUES (15, ".$data['rowid'].");";
			$rep = mysqli_query($this->link, $query2) or die(mysqli_error($this->link));
		}

		$query = "SELECT bc.label, bc.rowid FROM llx_bank_categ  as bc where bc.label ='CA Ventes 10'";
		$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		$data = mysqli_fetch_array($rep);
		if (empty($data)) {
			$query = "INSERT INTO llx_bank_categ (label, entity) VALUES ('CA Ventes 10', $this->entity)";
			$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		}
		$query = "SELECT bc.rowid FROM llx_bank_categ  as bc where bc.label ='CA Ventes 10' AND bc.rowid NOT IN (SELECT ct.fk_bank_categ FROM llx_categ_tva as ct);";
		$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		$data = mysqli_fetch_array($rep);
		if (!empty($data)) {
			$query2 = "INSERT INTO llx_categ_tva (fk_c_tva, fk_bank_categ) VALUES (17, ".$data['rowid'].");";
			$rep = mysqli_query($this->link, $query2) or die(mysqli_error($this->link));
		}

		$query = "SELECT bc.label, bc.rowid FROM llx_bank_categ as bc where bc.label ='CA Ventes 20'";
		$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		$data = mysqli_fetch_array($rep);
		if (empty($data)) {
			$query = "INSERT INTO llx_bank_categ (label, entity) VALUES ('CA Ventes 20', $this->entity)";
			$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		}
		$query = "SELECT bc.rowid FROM llx_bank_categ  as bc where bc.label ='CA Ventes 20' AND bc.rowid NOT IN (SELECT ct.fk_bank_categ FROM llx_categ_tva as ct);";
		$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		$data = mysqli_fetch_array($rep);
		if (!empty($data)) {
			$query2 = "INSERT INTO llx_categ_tva (fk_c_tva, fk_bank_categ) VALUES (11, ".$data['rowid'].");";
			$rep = mysqli_query($this->link, $query2) or die(mysqli_error($this->link));
		}

		$query = "SELECT bc.label, bc.rowid FROM llx_bank_categ as bc where bc.label ='Achats 0'";
		$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		$data = mysqli_fetch_array($rep);
		if (empty($data)) {
			$query = "INSERT INTO llx_bank_categ (label, entity) VALUES ('Achats 0', $this->entity)";
			$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		}
		$query = "SELECT bc.rowid FROM llx_bank_categ  as bc where bc.label ='Achats 0' AND bc.rowid NOT IN (SELECT ct.fk_bank_categ FROM llx_categ_tva as ct);";
		$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		$data = mysqli_fetch_array($rep);
		if (!empty($data)) {
			$query2 = "INSERT INTO llx_categ_tva (fk_c_tva, fk_bank_categ) VALUES (15, ".$data['rowid'].");";
			$rep = mysqli_query($this->link, $query2) or die(mysqli_error($this->link));
		}

		$query = "SELECT bc.label, bc.rowid FROM llx_bank_categ as bc where bc.label ='Achats 10'";
		$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		$data = mysqli_fetch_array($rep);
		if (empty($data)) {
			$query = "INSERT INTO llx_bank_categ (label, entity) VALUES ('Achats 10', $this->entity)";
			$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		}
		$query = "SELECT bc.rowid FROM llx_bank_categ  as bc where bc.label ='Achats 10' AND bc.rowid NOT IN (SELECT ct.fk_bank_categ FROM llx_categ_tva as ct);";
		$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		$data = mysqli_fetch_array($rep);
		if (!empty($data)) {
			$query2 = "INSERT INTO llx_categ_tva (fk_c_tva, fk_bank_categ) VALUES (17, ".$data['rowid'].");";
			$rep = mysqli_query($this->link, $query2) or die(mysqli_error($this->link));
		}

		$query = "SELECT bc.label, bc.rowid FROM llx_bank_categ as bc where bc.label ='Achats 20'";
		$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		$data = mysqli_fetch_array($rep);
		if (empty($data)) {
			$query = "INSERT INTO llx_bank_categ (label, entity) VALUES ('Achats 20', $this->entity)";
			$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		}
		$query = "SELECT bc.rowid FROM llx_bank_categ  as bc where bc.label ='Achats 20' AND bc.rowid NOT IN (SELECT ct.fk_bank_categ FROM llx_categ_tva as ct);";
		$rep = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		$data = mysqli_fetch_array($rep);
		if (!empty($data)) {
			$query2 = "INSERT INTO llx_categ_tva (fk_c_tva, fk_bank_categ) VALUES (11, ".$data['rowid'].");";
			$rep = mysqli_query($this->link, $query2) or die(mysqli_error($this->link));
		}

		$query = "SELECT bc.rowid FROM llx_bank_categ as bc WHERE bc.rowid NOT IN (SELECT ct.fk_bank_categ FROM llx_categ_tva as ct);";
		$req3 = mysqli_query($this->link, $query) or die(mysqli_error($this->link));
		$query2;
		while ($data = mysqli_fetch_assoc($req3)) {
			$query2 = "INSERT INTO llx_categ_tva (fk_c_tva, fk_bank_categ) VALUES (11, ".$data['rowid'].");";
			mysqli_query($this->link, $query2);
		}


		mysqli_commit($this->link);
	}

	/**
	*	This method get amount associate with category
	*	@param array $categ array with category
	*	@return array $charge_reel the fixed charges
	*/
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

	/**
	*	This method update the fixed charges, since the started activity
	*
	*	@return void
	*/
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

	/**
	*	This method update the turnover, since the started activity
	*
	*	@return array $tab_select
	*/
	public function up_tresorerie_CA()
	{
		$tableau_montant_categorie = array();
		$tableau_ca_20 = array();
		$sql = "SELECT b.rowid, b.dateo, b.amount, b.fk_type FROM ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."bank_account as ba where b.rowid NOT IN (select bclass.lineid FROM ".MAIN_DB_PREFIX."bank_class as bclass) AND b.amount >= 0 AND ba.entity = '$this->entity' AND b.fk_account = ba.rowid ORDER BY b.dateo ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while($data = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			$date = explode("-", $data['dateo']);
			$dates = $date[0]."-".$date[1];
			if ($data['fk_type'] != "SOLD") {
				$tab_select[$dates] += $data['amount'];
				$tableau_montant_categorie[$dates] += $data['amount']*(100/(20+100));
				$tableau_ca_20[$dates] += $data['amount'];
			}
		}

		$sql1 = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid  AND ct.fk_c_tva = t.rowid AND bcat.label = 'CA Ventes 20';";
		$resul = mysqli_query($this->link, $sql1) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($resul)) {
			$date = explode("-", $data['dateo']);
			$dates = $date[0]."-".$date[1];
			$tab_select[$dates] += $data['amount'];
			$tableau_montant_categorie[$dates] += $data['amount']*(100/(20+100));
			$tableau_ca_20[$dates] += $data['amount'];
		}
		$tableau_ca_10 = array();
		$sql2 = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND ct.fk_c_tva = t.rowid AND bcat.label = 'CA Ventes 10';";
		$rest = mysqli_query($this->link, $sql2) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($rest)) {
			$date = explode("-", $data['dateo']);
			$dates = $date[0]."-".$date[1];
			$tableau_montant_categorie[$dates] += $data['amount']*(100/(10+100));
			$tableau_ca_10[$dates] += $data['amount'];
			$tab_select[$dates] += $data['amount'];
		}
		$tableau_ca_0 = array();
		$sql2 = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND ct.fk_c_tva = t.rowid AND bcat.label = 'CA Ventes 0';";
		$rest = mysqli_query($this->link, $sql2) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($rest)) {
			$date = explode("-", $data['dateo']);
			$dates = $date[0]."-".$date[1];
			$tableau_montant_categorie[$dates] += $data['amount'];
			$tableau_ca_0[$dates] += $data['amount'];
			$tab_select[$dates] += $data['amount'];
		}
		$tableau_des_dates[] = array();
		$tab_insert = array();
		foreach ($tableau_montant_categorie as $date => $tableau_categ) {
			$month = explode("-", $date)[1];
			$years = explode("-", $date)[0];
			$nb_jours = date("t", mktime(0,0,0,$month, 1, $years));
			if (empty($tableau_categ)) {
				$query = "UPDATE llx_tresorerie SET CA = NULL WHERE "."date >='".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}else{
				$query = "UPDATE llx_tresorerie SET CA = ".$tableau_categ." WHERE date >= '".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}
			mysqli_query($this->link, $query);
		}
		foreach ($tableau_ca_10 as $date => $value) {
			$month = explode("-", $date)[1];
			$years = explode("-", $date)[0];
			$nb_jours = date("t", mktime(0,0,0,$month, 1, $years));
			if (empty($value)) {
				$query = "UPDATE llx_tresorerie SET CAVentes10 = NULL WHERE "."date >='".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}else{
				$query = "UPDATE llx_tresorerie SET CAVentes10 = ".$value." WHERE date >= '".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}
			mysqli_query($this->link, $query);
		}
		foreach ($tableau_ca_20 as $date => $value) {
			$month = explode("-", $date)[1];
			$years = explode("-", $date)[0];
			$nb_jours = date("t", mktime(0,0,0,$month, 1, $years));
			if (empty($value)) {
				$query = "UPDATE llx_tresorerie SET CAVentes20 = NULL WHERE "."date >='".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}else{
				$query = "UPDATE llx_tresorerie SET CAVentes20 = ".$value." WHERE date >= '".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}
			mysqli_query($this->link, $query);
		}
		foreach ($tableau_ca_0 as $date => $value) {
			$month = explode("-", $date)[1];
			$years = explode("-", $date)[0];
			$nb_jours = date("t", mktime(0,0,0,$month, 1, $years));
			if (empty($value)) {
				$query = "UPDATE llx_tresorerie SET CAVentes0 = NULL WHERE "."date >='".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}else{
				$query = "UPDATE llx_tresorerie SET CAVentes0 = ".$value." WHERE date >= '".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}
			mysqli_query($this->link, $query);
		}
		return $tab_select;
	}

	/**
	*	This method update the purchase, since the started activity
	*
	*	@return array $tab_select
	*/
	public function up_tresorerie_Achat()
	{
		$sql = "SELECT b.amount, b.dateo FROM llx_bank as b, llx_bank_account as ba where b.rowid NOT IN (select bclass.lineid FROM llx_bank_class as bclass) AND b.amount < 0 AND ba.entity = '$this->entity' ORDER BY b.dateo ASC;";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_montant_categorie = array();
		$tab_20 = array();
		$tableau_achat_20 = array();
		$tableau_achat_10 = array();
		$tab_select = array();
		while($data = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			$date = explode("-", $data['dateo']);
			$dates = $date[0]."-".$date[1];
			$tab_select[$dates] += $data['amount'];
			$tableau_montant_categorie[$dates] += $data['amount']*(100/(20+100));
			$tableau_achat_20[$dates] += $data['amount'];
			$tab_20[$dates] += $data['amount'];
		}
		$sql = "SELECT b.amount, b.dateo, bc.fk_categ FROM llx_bank as b, llx_bank_account as ba, llx_bank_class as bc where b.amount <= 0 AND ba.entity = '1' AND b.rowid = bc.lineid AND  bc.fk_categ NOT IN (select bcat.rowid FROM llx_bank_categ as bcat) ORDER BY b.dateo ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while($data = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			$date = explode("-", $data['dateo']);
			$dates = $date[0]."-".$date[1];
			$tab_select[$dates] += $data['amount'];
			$tableau_montant_categorie[$dates] += $data['amount']*(100/(20+100));
			$tableau_achat_20[$dates] += $data['amount'];
			$tab_20[$dates] += $data['amount'];
		}

		$sql1 = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid  AND ct.fk_c_tva = t.rowid AND bcat.label = 'Achats 20';";
		$resul = mysqli_query($this->link, $sql1) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($resul)) {
			$date = explode("-", $data['dateo']);
			$dates = $date[0]."-".$date[1];
			$tab_select[$dates] += $data['amount'];
			$tableau_montant_categorie[$dates] += $data['amount']*(100/(20+100));
			$tableau_achat_20[$dates] += $data['amount'];
			$tab_20[$dates] += $data['amount'];
		}
		$sql2 = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND ct.fk_c_tva = t.rowid AND bcat.label = 'Achats 10';";
		$rest = mysqli_query($this->link, $sql2) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($rest)) {
			$date = explode("-", $data['dateo']);
			$dates = $date[0]."-".$date[1];
			$tab_select[$dates] += $data['amount'];
			$tableau_montant_categorie[$dates] += $data['amount']*(100/(10+100));
			$tableau_achat_10[$dates] += $data['amount'];
			$tab_20[$dates] += $data['amount'];
		}
		$tableau_achat_0 = array();
		$sql2 = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND ct.fk_c_tva = t.rowid AND bcat.label = 'Achats 0';";
		$rest = mysqli_query($this->link, $sql2) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($rest)) {
			$date = explode("-", $data['dateo']);
			$dates = $date[0]."-".$date[1];
			$tab_select[$dates] += $data['amount'];
			$tableau_montant_categorie[$dates] += $data['amount'];
			$tableau_achat_0[$dates] += $data['amount'];
			$tab_20[$dates] += $data['amount'];
		}
		$tableau_des_dates[] = array();
		$tab_insert = array();
		foreach ($tableau_montant_categorie as $date => $tableau_categ) {
			$month = explode("-", $date)[1];
			$years = explode("-", $date)[0];
			$nb_jours = date("t", mktime(0,0,0,$month, 1, $years));
			if (empty($tableau_categ)) {
				$query = "UPDATE llx_tresorerie SET achat = NULL WHERE "."date >='".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}else{
				$query = "UPDATE llx_tresorerie SET achat = ".$tableau_categ." WHERE date >= '".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}
			mysqli_query($this->link, $query);
		}
		foreach ($tableau_achat_10 as $date => $value) {
			$month = explode("-", $date)[1];
			$years = explode("-", $date)[0];
			$nb_jours = date("t", mktime(0,0,0,$month, 1, $years));
			if (empty($value)) {
				$query = "UPDATE llx_tresorerie SET Achats10 = NULL WHERE "."date >='".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}else{
				$query = "UPDATE llx_tresorerie SET Achats10 = ".$value." WHERE date >= '".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}
			mysqli_query($this->link, $query);
		}
		foreach ($tableau_achat_20 as $date => $value) {
			$month = explode("-", $date)[1];
			$years = explode("-", $date)[0];
			$nb_jours = date("t", mktime(0,0,0,$month, 1, $years));
			if (empty($value)) {
				$query = "UPDATE llx_tresorerie SET Achats20 = NULL WHERE "."date >='".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}else{
				$query = "UPDATE llx_tresorerie SET Achats20 = ".$value." WHERE date >= '".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}
			mysqli_query($this->link, $query);
		}
		foreach ($tableau_achat_0 as $date => $value) {
			$month = explode("-", $date)[1];
			$years = explode("-", $date)[0];
			$nb_jours = date("t", mktime(0,0,0,$month, 1, $years));
			if (empty($value)) {
				$query = "UPDATE llx_tresorerie SET Achats0 = NULL WHERE "."date >='".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}else{
				$query = "UPDATE llx_tresorerie SET Achats0 = ".$value." WHERE date >= '".$date."-01' AND date <= '".$date."-".$nb_jours."' AND type='reel';";
			}
			mysqli_query($this->link, $query);
		}
/*
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
		}*/
		return $tab_select;
	}

	/**
	*	This method retrieves the values ​​superiors zero associate has a category
	*
	*	@return array $tab_select
	*/
	public function recuperer_valeur_sup_categ()
	{
		$sql = "SELECT b.amount, b.dateo FROM llx_bank as b, llx_bank_account as ba where b.rowid IN (select bclass.lineid FROM llx_bank_class as bclass) AND b.amount >= 0 AND ba.entity = '$this->entity' ORDER BY b.dateo ASC;";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_montant = array();
		$tableau_des_dates[] = array();
		$tab_select = array();
		while($data = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			$tableau_montant[] = $data;
		}
		foreach ($tableau_montant as $tableau_categ) {
			$date_mois = explode("-" ,$tableau_categ['dateo']);
			$date_par_mois = $date_mois[0]."-".$date_mois[1];
			if (in_array($date_par_mois, $tableau_des_dates)) {
				$tab_select[$date_par_mois] += $tableau_categ['amount'];
			}
			else{
				$tab_select[$date_par_mois] += $tableau_categ['amount'];
			}
			$tableau_des_dates[] = $date_par_mois;
		}
		return $tab_select;
	}

	/**
	 *	This method calculate the cash balance and update database
	 *
	 *	@param array $tab_ca array with amount of turnover
	 *	@param array $tab_achat array with amount of purchase
	 *	@param array $tab_charge array with amount of fixed charges
	 *	@param array $tab_achat array with higher amount  and have category
	 *	@return void
	 */
	public function calcul_solde_tresorerie($tab_ca, $tab_achat, $tab_charge, $tab_valeur_sup)
	{
		$annee = date("Y");
		$annee--;
		$query = "SELECT b.amount, b.dateo FROM llx_bank as b, llx_bank_account as ba where fk_type='SOLD' AND ba.rowid = b.fk_account AND ba.entity = '$this->entity' order by dateo ASC;";
		$res = mysqli_query($this->link, $query)or die(mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$dat = explode("-", $data['dateo']);
			$tab_sold_init[$dat[0]."-".$dat[1]] = $data['amount'];
			$query_init_solde = "UPDATE llx_tresorerie SET soldeDebut = '".$data['amount']."' WHERE date >= '".$dat[0]."-".$dat[1]."-01' AND date <= '".$dat[0]."-".$dat[1]."-28' AND type='reel';";
			mysqli_query($this->link, $query_init_solde)or die(mysqli_error($this->link));
		}
		for ($i=$annee; $i <= date("Y") ; $i++) { 
			for ($j=1; $j <= 12 ; $j++) { 
				if ($j<10) {
					$date= $i."-0".$j;
				}
				else{
					$date= $i."-".$j;
				}
				foreach ($tab_sold_init as $key => $value) {
					if ($date == $key) {
						$le_calcul = round($tab_sold_init[$date]+$tab_ca[$date]+$tab_achat[$date]+$tab_charge[$date]+$tab_valeur_sup[$date], 2);
						$queryCourant = "UPDATE llx_tresorerie SET soldeCourant = '".$le_calcul."' WHERE date >= '".$date."-01' AND date <= '".$date."-28' AND type='reel';";
						$queryDebut = "UPDATE llx_tresorerie SET soldeDebut = '".$tab_sold_init[$date]."' WHERE date >= '".$date."-01' AND date <= '".$date."-28' AND type='reel';";
					}
					elseif ($date<= date("Y-m")) {
						//echo "<br> val : ".$tab_valeur_sup[$date]." ca : ".$tab_ca[$date]." achat : ".$tab_achat[$date]." charge :".$tab_charge[$date]." date : ".$date."<br>";
						$queryDebut = "UPDATE llx_tresorerie SET soldeDebut = ".$le_calcul." WHERE date >= '".$date."-01' AND date <= '".$date."-28' AND type='reel';";
						$le_calcul = round($le_calcul+$tab_ca[$date]+$tab_valeur_sup[$date]+$tab_achat[$date]+$tab_charge[$date], 2);
						$queryCourant = "UPDATE llx_tresorerie SET soldeCourant = ".$le_calcul." WHERE date >= '".$date."-01' AND date <= '".$date."-28' AND type='reel';";
					}
				}
				
				/*if ($date<= date("Y-m")) {
					foreach ($tab_sold_init as $key => $value) {
						if ($key == $date) {
							$le_calcul = round($tab_sold_init[$date]+$tab_ca[$date]+$tab_achat[$date]+$tab_charge[$date]+$tab_valeur_sup[$date], 2);
							$queryCourant = "UPDATE llx_tresorerie SET soldeCourant = '".$le_calcul."' WHERE date >= '".$date."-01' AND date <= '".$date."-28' AND type='reel';";
							echo $tab_sold_init[$date];
							$queryDebut = "UPDATE llx_tresorerie SET soldeDebut = '".$tab_sold_init[$date]."' WHERE date >= '".$date."-01' AND date <= '".$date."-28' AND type='reel';";

							//$queryDebut = "UPDATE llx_tresorerie SET soldeDebut = '".$le_calcul."' WHERE date >= '".$date."-01' AND date <= '".$date."-28' AND type='reel';";
						}
					}
					//$queryDebut = "UPDATE llx_tresorerie SET soldeDebut = '".$le_calcul."' WHERE date >= '".$date."-01' AND date <= '".$date."-28' AND type='reel';";
					$le_calcul = round($le_calcul+$tab_ca[$date]+$tab_achat[$date]+$tab_charge[$date]+$tab_valeur_sup[$date], 2);
					$queryCourant = "UPDATE llx_tresorerie SET soldeCourant = '".$le_calcul."' WHERE date >= '".$date."-01' AND date <= '".$date."-28' AND type='reel';";
				}*/
				mysqli_query($this->link, $queryCourant)or die(mysqli_error($this->link));
				mysqli_query($this->link, $queryDebut)or die(mysqli_error($this->link));
			}
		}
	}

	/**
	 *	This method add date on table tresorerie if not exists
	 *
	 *	@return void
	 */
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
/*
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

*/
}

?>
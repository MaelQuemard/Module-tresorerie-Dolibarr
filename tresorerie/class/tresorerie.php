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
 *	Class tresorerie is used for everything related to the dashboard
 *
 *	@filesource /htdocs/tresorerie/class/tresorerie.php
 *	@package Class
 *	@licence http://www.gnu.org/licenses/ GPL
 *	@version Version 1.0
 *	@author Maël Quémard
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

	/**
	 *	This is the contructor of class, get the link of connection (database)
	 *	
	 *	@param Object $leink
	 *	@global object $conf
	 *	@global object $langs
	 *	@var int $this->entity number of entity
	 *	@var date $this->date this var includes year, month, day
	 *	@var date $this->dateD this var includes year, month
	 */
	function __construct($leink)
	{
		global $conf, $langs;
		$this->link = $leink;
		$this->entity = $conf->entity;
        $this->date = date("Y-m-d");
        $this->dateD = date("Y-m");
	}

	//Selectionne et calcule le solde du mois courant
	/**
	 * This method get cash balance for the current month
	 *
	 *	@return double $this->solde
	 */
	public function getSolde()
	{
		$sql = "SELECT sum(b.amount) as amount FROM ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."bank_account as ba WHERE b.fk_account = ba.rowid AND b.dateo <= '$this->date' AND ba.entity = '$this->entity' ";
		$res = mysqli_query($this->link, $sql) or die(mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$this->solde = $data["amount"];
		}
		return $this->solde;
	}

	//Selectionne et calcule la somme du chiffre d'affaire du mois courant
	/**
	 *	This method get sum of fixed charges
	 *
	 *	@return int $this->charge
	 */
	public function getTotalCharge()
	{
		$sql = "SELECT DISTINCT SUM(amount) as a FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND bclass.lineid = b.rowid";
		$res = mysqli_query($this->link, $sql) or die(mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$this->charge = $data['a'];
		}
		return $this->charge;
	}

	//Selcetionne le label des catégories, le montant et la date selon si c'est dans le mois courant et le montant selon si il est rangé dans une catégorie
	/**
	 *	This method get amount by category (fixed charges)
	 *
	 *	@return array $this->montant_Categ
	 */
	public function getMontantCategorie()
	{
		$sql = "SELECT DISTINCT bcat.label, b.amount FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ct.fk_c_tva = t.rowid AND ct.fk_bank_categ = bcat.rowid";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			//calcul permettant d'avoir le montant HT
			//$amount = $data['amount']*(100/($data['taux']+100));
			if ($data['label']!="CA Ventes 10" && $data['label']!="CA Ventes 20") {
				$this->montant_Categ[$data['label']] = $this->montant_Categ[$data['label']]+$data['amount'];
			}
		}

		return $this->montant_Categ;
	}

	//Selectionne le chiffre d'affaire, plus précisement le nombre d'entrée d'argent et on additionne tous cela pour obtenir le CA du mois courant
	/**
	 *	This method get turnover
	 *
	 *	@return double $this->ca
	 */
	public function getCA()
	{
		//$sql = "SELECT b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."bank_account as ba WHERE b.amount > 0 AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ba.entity = '$this->entity'";
		$sql = "SELECT DISTINCT b.amount FROM ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."bank_account as ba where b.rowid NOT IN (select bclass.lineid FROM ".MAIN_DB_PREFIX."bank_class as bclass) AND b.amount >= 0 AND b.dateo <= '$this->date'AND b.dateo >= '$this->dateD-01' AND ba.entity = '$this->entity';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$this->ca += round($data['amount']*(100/(20+100)), 2);
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'CA Ventes 20';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$this->ca += round($data['amount']*(100/(20+100)), 2);
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'CA Ventes 10';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$this->ca += round($data['amount']*(100/(10+100)), 2);
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'CA Ventes 0';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$this->ca += round($data['amount'], 2);
		}
		return $this->ca;
	}

	public function getCA_10()
	{
		$sql = "SELECT DISTINCT bcat.label, b.amount FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'CA Ventes 10';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$ca_10 += $data['amount'];
		}
		return $ca_10;
	}

	public function getCA_20()
	{
		$sql = "SELECT DISTINCT b.amount FROM ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."bank_account as ba where b.rowid NOT IN (select bclass.lineid FROM ".MAIN_DB_PREFIX."bank_class as bclass) AND b.amount >= 0 AND b.dateo <= '$this->date'AND b.dateo >= '$this->dateD-01' AND ba.entity = '$this->entity';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$ca_20 += $data['amount'];
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'CA Ventes 20';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$ca_20 += $data['amount'];
		}
		return $ca_20;
	}

	public function getCA_0()
	{
		$sql = "SELECT DISTINCT bcat.label, b.amount FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'CA Ventes 0';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$ca_0 += $data['amount'];
		}
		return $ca_0;
	}	

	//Selectionne le montant des achats, seulement si, les achats ne font pas partie d'une catégorie (charge)
	/**
	 *	This method get purchase
	 *
	 *	@return double $this->achat
	 */
	public function getAchat()
	{
		$sql = "SELECT DISTINCT b.amount FROM ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."bank_account as ba where b.rowid NOT IN (select bclass.lineid FROM ".MAIN_DB_PREFIX."bank_class as bclass) AND b.amount < 0 AND b.dateo <= '$this->date'AND b.dateo >= '$this->dateD-01' AND ba.entity = '$this->entity';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$this->achat += round($data['amount']*(100/(20+100)), 2);
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'Achats 20';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$this->achat += round($data['amount']*(100/(20+100)), 2);
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'Achats 10';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$this->achat += round($data['amount']*(100/(10+100)), 2);
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'Achats 0';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$this->achat += round($data['amount'], 2);
		}
		return $this->achat;
	}

	public function getAchat_10()
	{
		$sql = "SELECT DISTINCT bcat.label, b.amount FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'Achats 10';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$achat_10 += $data['amount'];
		}
		return $achat_10;
	}

	public function getAchat_20()
	{
		$sql = "SELECT DISTINCT b.amount FROM ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."bank_account as ba where b.rowid NOT IN (select bclass.lineid FROM ".MAIN_DB_PREFIX."bank_class as bclass) AND b.amount < 0 AND b.dateo <= '$this->date'AND b.dateo >= '$this->dateD-01' AND ba.entity = '$this->entity';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$achat_20 += $data['amount'];
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'Achats 20';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$achat_20 += $data['amount'];
		}
		return $achat_20;
	}

	public function getAchat_0()
	{
		$sql = "SELECT DISTINCT bcat.label, b.amount FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo <= '$this->date' AND b.dateo >= '$this->dateD-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'Achats 0';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$achat_0 += $data['amount'];
		}
		return $achat_0;
	}	

	/**
	 *	This method get name of category (fixed charges)
	 *
	 *	@return array $this->categorie
	 */
	public function getCategorie()
	{
		$query_categ = "SELECT label FROM `".MAIN_DB_PREFIX."bank_categ` ORDER BY `label` ASC;";
		$resultat = mysqli_query($this->link, $query_categ) or die (mysqli_error($this->link));
		while ($data= mysqli_fetch_assoc($resultat)) {
			if ($data['label']!="CA Ventes 20" && $data['label'] != "CA Ventes 10" && $data['label'] != "CA Ventes 0" && $data['label']!="Achats 20" && $data['label'] != "Achats 10" && $data['label'] != "Achats 0") {
				$this->categorie[] = $data['label'];
			}
		}
		return $this->categorie;
	}

	/**
	 *	This method get lines numbers in table bank_categ
	 *
	 *	@return int $nb
	 */
	public function getNbLignes()
	{
		$query_categ = "SELECT label FROM `".MAIN_DB_PREFIX."bank_categ` ORDER BY `label` ASC;";
		$resultat = mysqli_query($this->link, $query_categ) or die (mysqli_error($this->link));
		$nb = mysqli_num_rows($resultat);
		return $nb;
	}

	/**
	 *	This method calculate amount ex VAT of fixed charges
	 *
	 *	@param array $taux
	 *	@param array $tab_ligne_bd
	 *	@return array $amount
	 */
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

	//Permet de soit mettre à jour le tableau de bord (base de donnée) ".MAIN_DB_PREFIX."tresorerie ou soit d'inserer une nouvelle entrée dans le tbd (bd)
	/**
	 *	This method update or insert the new value when click on button sychronize
	 *
	 *	@param array $Tcharge
	 *	@param double $solde
	 *	@param double $ca
	 *	@param double $achat
	 *	@param array $Mcharge
	 *
	 *	@param array $categ
	 *	@return void
	 */
	public function Upsert($Tcharge, $solde = 0, $ca = 0, $ca_0 = 0, $ca_10 = 0, $ca_20 = 0, $achat = 0, $achat_0 = 0, $achat_10 = 0, $achat_20 = 0, $Mcharge, $categ)
	{
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		$mois = array();
		$month = explode("-", $this->dateD)[1];
		$years = explode("-", $this->dateD)[0];
		$nb_jours = date("t", mktime(0,0,0,$month, 1, $years));
		$query = "SELECT t.date FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date <= '$this->dateD-$nb_jours' AND t.date >= '$this->dateD-01' AND t.type='reel';";
		$res = mysqli_query($this->link, $query) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$m = $data['date'];
		}
		$mois = explode("-", $m);

		if ($mois[1] == date("m")) {
			$sql = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET ";
			$sql .= ($ca_0 == 0) ? "CAVentes0=NULL, " : "CAVentes0=$ca_0, ";
			$sql .= ($ca_10 == 0) ? "CAVentes10=NULL, " : "CAVentes10=$ca_10, ";
			$sql .= ($ca_20 == 0) ? "CAVentes20=NULL, " : "CAVentes20=$ca_20, ";
			$sql .= ($achat_0 == 0) ? "Achats0=NULL, " : "Achats0=$achat_0, ";
			$sql .= ($achat_10 == 0) ? "Achats10=NULL, " : "Achats10=$achat_10, ";
			$sql .= ($achat_20 == 0) ? "Achats20=NULL, " : "Achats20=$achat_20, ";
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
			$sql = "INSERT INTO ".MAIN_DB_PREFIX."tresorerie (";
			foreach ($Mcharge as $row => $value) {
				$row = str_replace($search, $replace, $row);
				$sql .= "$row, ";
			}
			$sql .= "CAVentes0, CAVentes10, CAVentes20, Achats0, Achats10, Achats20, soldeCourant, CA, achat, date, type) VALUES (";
			foreach ($Mcharge as $row => $value) {
				$sql .= "'$value', ";
			}
			$sql .= ($ca_0 == 0) ? "NULL, " : "'$ca_0', ";
			$sql .= ($ca_10 == 0) ? "NULL, " : "'$ca_10', ";
			$sql .= ($ca_20 == 0) ? "NULL, " : "'$ca_20', ";
			$sql .= ($ca_0 == 0) ? "NULL, " : "'$achat_0', ";
			$sql .= ($ca_10 == 0) ? "NULL, " : "'$achat_10', ";
			$sql .= ($ca_20 == 0) ? "NULL, " : "'$achat_20', ";
			$sql .= ($solde == 0) ? "NULL, " : "'$solde', ";
			$sql .= ($ca == 0) ? "NULL, " : "'$ca', ";
			$sql .= ($achat == 0) ? "NULL, " : "'$achat', ";
			$sql .= "'$this->date', 'reel');";
			mysqli_query($this->link, $sql) or die (mysqli_error());
			mysqli_commit($this->link);
		}
	}

	/**
	 *	This method get taux of VAT
	 *
	 *	@return array $this->taux
	 */
	public function getTaux()
	{
		$sql = "SELECT DISTINCT bcat.label, t.taux FROM ".MAIN_DB_PREFIX."c_tva as t, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."bank_categ as bcat where ct.fk_c_tva = t.rowid AND ct.fk_bank_categ = bcat.rowid;";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		while ($data = mysqli_fetch_assoc($res)) {
			$data['label'] = str_replace($search, $replace, $data['label']);
			$this->taux[$data['label']] = $data['taux'];
		}
		return $this->taux;
	}

	/**
	 *	This method get real treasury since the current month or the month specify on parameter
	 *	
	 *	@param string $date_param
	 *	@return array $this->tresoReel
	 */
	public function getTresorerie_Reel($date_param = 0)
	{
		$date_decoupe = explode("/", $date_param);
		$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
		$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]-31";
		$date_temp_a = explode("-", $this->date);
		$date_temp_courant = "$date_temp_a[0]-$date_temp_a[1]-31";
		$sql = ($date == 0) ?  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$this->dateD-01' AND t.type='reel'":  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$date' AND t.type='reel' ORDER BY t.date ASC";
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

	/**
	 *	This method get real treasury ex VAT since the current month or the month specify on parameter
	 *	
	 *	@param array $taux
	 *	@param string $date_param
	 *	@return array $this->tresoReel_HT
	 */
	public function getTresorerie_Reel_HT($taux, $date_param = 0)
	{
		$tresoReel_HT = array();
		$date_decoupe = explode("/", $date_param);
		$date = "$date_decoupe[2]-$date_decoupe[1]"/*$date_decoupe[0]"*/;
		$sql = ($date == 0) ?  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$this->dateD-01' AND t.type='reel' ORDER BY t.date ASC;":  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$date-01' AND t.type='reel' ORDER BY t.date ASC";
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
								if ($categTaux != "TVA") {
									$tab1[$finfo->name] = round($data[$i]*(100/($valueTaux+100)), 2);
								}
								else{
									$tab1[$finfo->name] = round($data[$i], 2);
								}
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

	/**
	 *	This method get projected treasury since the current month or the month specify on parameter
	 *	
	 *	@param string $date_param
	 *	@return array $this->tresoReel
	 */
	public function getTresorerie_Prev($date_param = 0)
	{
		$date_decoupe = explode("/", $date_param);
		$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
		$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]-31";
		$date_temp_a = explode("-", $this->date);
		$date_temp_courant = "$date_temp_a[0]-$date_temp_a[1]-31";
		$sql = ($date == 0) ?  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$this->dateD-01' AND t.type='prev' ORDER BY t.date ASC;":  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$date' AND t.type='prev' ORDER BY t.date ASC";
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

	/**
	 *	This method get projected treasury ex VAT since the current month or the month specify on parameter
	 *	
	 *	@param array $taux
	 *	@param string $date_param
	 *	@return array $this->tresoReel_HT
	 */
	public function getTresorerie_Prev_HT($taux, $date_param = 0)
	{
		$tresoPrev_HT = array();
		$date_decoupe = explode("/", $date_param);
		$date = "$date_decoupe[2]-$date_decoupe[1]"/*$date_decoupe[0]"*/;
		$sql = ($date == 0) ?  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$this->dateD-01' AND t.type='prev' ORDER BY t.date ASC;":  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$date-01' AND t.type='prev' ORDER BY t.date ASC";
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
								$tab1[$finfo->name] = round($data[$i], 2);
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

	/**
	 *	This method set projected treasury, and update table tresorerie
	 *
	 *	@param array $tab_prev
	 *	@return void
	 */
	public function setPrevisionel($tab_prev)
	{
		if (strlen($tab_prev)>2) {
			$info = explode(";", $tab_prev);
			$info_date = explode("-", $info[2]);
			$info_date_D = $info_date[0]."-".$info_date[1]."-01";
			$info_date_F = $info_date[0]."-".$info_date[1]."-28";
			$info_date = $info_date[0]."-".$info_date[1]."-".$info_date[2];
			$info[0] = str_replace(" ", "", $info[0]);
			$info[0] = str_replace(",", ".", $info[0]);
			if (strlen($info[0])<1 || $info[0]=="" || $info[0]==" " || $info[0]==0) {
				$info[0]="NULL";
			}
			if (!strstr("-", $info[0]) && $info[0]!="NULL" && $info[1]!="CA"  && $info[1]!="CAVentes0"  && $info[1]!="CAVentes10"  && $info[1]!="CAVentes20") {
				$info[0] = "-".$info[0];
			}
			$sql = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET $info[1]=$info[0], type='prev' WHERE type='prev' AND date BETWEEN '$info_date_D' AND '$info_date_F';";
			mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
			mysqli_commit($this->link);
		}
	}

	/**
	 *	This method get real fixed charges since the current month or the month specify on parameter
	 *
	 *	@param array $categ
	 *	@param array $taux
	 *	@param string $date_param
	 *	@return array $this->charge_reel
	 */
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
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie where type='reel' ORDER BY date ASC";
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
						if ($categorie_du_mois != "CA Ventes 20" && $categorie_du_mois != "CA Ventes 10" && $categorie_du_mois != "CA Ventes 0" && $categorie_du_mois != "Achats 20" && $categorie_du_mois != "Achats 10" && $categorie_du_mois != "Achats 0") {
							if ($categTaux != "TVA") {
								$tableau_treso_mois[$categorie_du_mois] = round($valeur*(100/($valueTaux+100)), 2);
							}
							else{
								$tableau_treso_mois[$categorie_du_mois] = round($valeur, 2);
							}
						}
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

	/**
	 *	This method get projected fixed charges since the current month or the month specify on parameter
	 *
	 *	@param array $categ
	 *	@param array $taux
	 *	@param string $date_param
	 *	@return array $this->charge_prev
	 */
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

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie where type='prev' ORDER BY date ASC";
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
						if ($categorie_du_mois != "CA Ventes 10" && $categorie_du_mois != "CA Ventes 20" && $categorie_du_mois != "CA Ventes 0" && $categorie_du_mois != "Achats 20" && $categorie_du_mois != "Achats 10" && $categorie_du_mois != "Achats 0") {
							$tableau_treso_mois[$categorie_du_mois] = round($valeur/**(100/($valueTaux+100))*/, 2);
						}
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
		return $this->charge_prev;
	}

	/**
	 *	This method calculate the projected cash balance of treasury
	 *
	 *	@param array $categorie
	 *	@param array $taux
	 *	@param string $date_param
	 *	@return void
	 */
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

		$query_1 = "SELECT soldeDebut, date FROM ".MAIN_DB_PREFIX."tresorerie WHERE date>='".date("Y")."-01-01' AND type='reel'";
		$res_1 = mysqli_query($this->link, $query_1)or die(mysqli_error($this->link));

		$tab_tout = array();
		$tab_solde_prev = array();
		$solde_courant_reel = array();
		$i = 0;
		$ok = false;
		$query = "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie WHERE date>='".date("Y")."-01-01' AND type ='prev' ORDER BY date ASC";
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
							$query_update_solde_debut = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET soldeDebut = '".$valueSoldeCourant."' WHERE date >= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-01' AND date <= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-28' AND type = 'prev';";
							mysqli_query($this->link, $query_update_solde_debut)or die(mysqli_error($this->link));
							mysqli_commit($this->link);
						}
					}
					foreach ($categorie as $categ) {
						$categ = str_replace($search, $replace, $categ);
						if ($categ == $les_categ && $categ != "CAVentes10" && $categ != "CAVentes20" && $categ != "CAVentes0" && $categ != "Achats10" && $categ != "Achats20" && $categ != "Achats0") {
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
				$query_update_solde_courant = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET soldeCourant = '".$tab_solde_prev[$_les_dates_treso[0]."-".$_les_dates_treso[1]]."' WHERE date >= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-01' AND date <= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-28' AND type = 'prev';";
				mysqli_query($this->link, $query_update_solde_courant);
				mysqli_commit($this->link);
			}
		}			
	}

	/**
	 *	This method calculate the future treasury, and update table tresorerie
	 *
	 *	@return void
	 */
	public function calcul_reel_futur()
	{
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		
		//Pour les categories
		$le_tab = array();
		$query = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."facture as f, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ct.fk_c_tva = t.rowid AND ct.fk_bank_categ = bcat.rowid ORDER BY b.dateo ASC";
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
				if ($catg != "CAVentes20" && $catg != "CAVentes10" && $catg != "CAVentes0" && $catg != "Achats20" && $catg != "Achats10" && $catg != "Achats0") {
					$sql = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET ".$catg."=".$la_valeur." where date >= '".$date_du_tab."-01' AND date <= '".$date_du_tab."-28' AND type = 'reel';";
					mysqli_query($this->link, $sql)or die(mysqli_error($this->link));
					mysqli_commit($this->link);
				}
			}
		}

		//Pour le chiffre d'affaire
		$le_tab = array();
		$sql = "SELECT b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."bank_account as ba WHERE b.amount > 0 AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ba.entity = '$this->entity';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["CA"] += round($data['amount']*(100/(20+100)), 2);
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'CA Ventes 20';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["CA"] += round($data['amount']*(100/(20+100)), 2);
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'CA Ventes 0';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["CA"] += round($data['amount'], 2);
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'CA Ventes 10';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["CA"] += round($data['amount']*(100/(10+100)), 2);
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		foreach ($le_tab as $date_du_tab => $value) {
			foreach ($value as $catg => $la_valeur) {
				$sql = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET ".$catg."=".$la_valeur." where date >= '".$date_du_tab."-01' AND date <= '".$date_du_tab."-28' AND type = 'reel';";
				mysqli_query($this->link, $sql)or die(mysqli_error($this->link));
				mysqli_commit($this->link);
			}
		}

		$le_tab = array();
		$sql = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'CA Ventes 10';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["CAVentes10"] += round($data['amount'], 2);
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		foreach ($le_tab as $date_du_tab => $value) {
			foreach ($value as $catg => $la_valeur) {
				$sql = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET ".$catg."=".$la_valeur." where date >= '".$date_du_tab."-01' AND date <= '".$date_du_tab."-28' AND type = 'reel';";
				mysqli_query($this->link, $sql)or die(mysqli_error($this->link));
				mysqli_commit($this->link);
			}
		}

		$le_tab = array();
		$sql = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'CA Ventes 0';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["CAVentes0"] += round($data['amount'], 2);
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		foreach ($le_tab as $date_du_tab => $value) {
			foreach ($value as $catg => $la_valeur) {
				$sql = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET ".$catg."=".$la_valeur." where date >= '".$date_du_tab."-01' AND date <= '".$date_du_tab."-28' AND type = 'reel';";
				mysqli_query($this->link, $sql)or die(mysqli_error($this->link));
				mysqli_commit($this->link);
			}
		}

		$le_tab = array();
		$sql = "SELECT b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."bank_account as ba WHERE b.amount > 0 AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ba.entity = '$this->entity';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["CAVentes20"] += round($data['amount'], 2);
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'CA Ventes 20';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["CAVentes20"] += round($data['amount'], 2);
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		foreach ($le_tab as $date_du_tab => $value) {
			foreach ($value as $catg => $la_valeur) {
				$sql = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET ".$catg."=".$la_valeur." where date >= '".$date_du_tab."-01' AND date <= '".$date_du_tab."-28' AND type = 'reel';";
				mysqli_query($this->link, $sql)or die(mysqli_error($this->link));
				mysqli_commit($this->link);
			}
		}



		//Pour les achats
		$le_tab = array();
		$sql = "SELECT DISTINCT b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."bank_account as ba where b.rowid NOT IN (select bclass.lineid FROM ".MAIN_DB_PREFIX."bank_class as bclass) AND b.amount < 0 AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ba.entity = '$this->entity';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["achat"] += $data['amount'];
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'Achats 20';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["achat"] += round($data['amount']*(100/(20+100)), 2);
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'Achats 0';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["achat"] += round($data['amount'], 2);
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'Achats 10';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["achat"] += round($data['amount']*(100/(10+100)), 2);
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		foreach ($le_tab as $date_du_tab => $value) {
			foreach ($value as $catg => $la_valeur) {
				$sql = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET ".$catg."=".$la_valeur." where date >= '".$date_du_tab."-01' AND date <= '".$date_du_tab."-28' AND type = 'reel';";
				mysqli_query($this->link, $sql)or die(mysqli_error($this->link));
				mysqli_commit($this->link);
			}
		}

		$le_tab = array();
		$sql = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'Achats 10';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["Achats10"] += round($data['amount'], 2);
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		foreach ($le_tab as $date_du_tab => $value) {
			foreach ($value as $catg => $la_valeur) {
				$sql = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET ".$catg."=".$la_valeur." where date >= '".$date_du_tab."-01' AND date <= '".$date_du_tab."-28' AND type = 'reel';";
				mysqli_query($this->link, $sql)or die(mysqli_error($this->link));
				mysqli_commit($this->link);
			}
		}

		$le_tab = array();
		$sql = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'Achats 0';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["Achats0"] += round($data['amount'], 2);
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		foreach ($le_tab as $date_du_tab => $value) {
			foreach ($value as $catg => $la_valeur) {
				$sql = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET ".$catg."=".$la_valeur." where date >= '".$date_du_tab."-01' AND date <= '".$date_du_tab."-28' AND type = 'reel';";
				mysqli_query($this->link, $sql)or die(mysqli_error($this->link));
				mysqli_commit($this->link);
			}
		}

		$le_tab = array();
		//$sql = "SELECT b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."bank_account as ba WHERE b.amount < 0 AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ba.entity = '$this->entity';";
		$sql = "SELECT DISTINCT b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."bank_account as ba where b.rowid NOT IN (select bclass.lineid FROM ".MAIN_DB_PREFIX."bank_class as bclass) AND b.amount < 0 AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ba.entity = '$this->entity';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["Achats20"] += round($data['amount'], 2);
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		$sql = "SELECT DISTINCT bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND b.dateo >= '".date("Y")."-".(date("m")+1)."-01' AND ct.fk_c_tva = t.rowid AND bcat.label = 'Achats 20';";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while ($data = mysqli_fetch_assoc($res)) {
			$date = explode("-", $data['dateo']);
			if (!array_key_exists(($date[0]."-".$date[1]), $le_tab)) {
				$tableau_reel_futur = array();
			}
			$tableau_reel_futur["Achats20"] += round($data['amount'], 2);
			$le_tab[$date[0]."-".$date[1]] = $tableau_reel_futur;
		}
		foreach ($le_tab as $date_du_tab => $value) {
			foreach ($value as $catg => $la_valeur) {
				$sql = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET ".$catg."=".$la_valeur." where date >= '".$date_du_tab."-01' AND date <= '".$date_du_tab."-28' AND type = 'reel';";
				mysqli_query($this->link, $sql)or die(mysqli_error($this->link));
				mysqli_commit($this->link);
			}
		}

	}

	/**
	 *	This method caluclate the future cash balance and update table tresorerie
	 *
	 *	@param array $categorie
	 *	@return void
	 */
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
		$query = "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie WHERE date>='".date("Y")."-01-01' AND type ='reel' ORDER BY date ASC";
		$res = mysqli_query($this->link, $query)or die(mysqli_error($this->link));
		while ($data = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
			$tab_tout[] = $data;
		}
		foreach ($tab_tout as $categorie_du_mois => $valeur) {
			$_les_dates_treso = explode("-",$valeur['date']);	
			if (($_les_dates_treso[0]."-".$_les_dates_treso[1]) >= $date_temp_demande) {
				if ($_les_dates_treso[1]==1) {
					$date_temp_a = ($_les_dates_treso[0]-1)."-12";
				}
				elseif ($_les_dates_treso[1]<=10) {
					$date_temp_a = $_les_dates_treso[0]."-0".($_les_dates_treso[1]-1);
				}
				elseif ($_les_dates_treso[1]>10){
					$date_temp_a = ($_les_dates_treso[0]."-".($_les_dates_treso[1]-1));
				}
				$query_1 = "SELECT soldeCourant, date FROM ".MAIN_DB_PREFIX."tresorerie WHERE date>='".$date_temp_a."-01' AND type='reel'";
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
							$query_update_solde_debut = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET soldeDebut = '".$valueSoldeCourant."' WHERE date >= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-01' AND date <= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-28' AND type = 'reel';";
							mysqli_query($this->link, $query_update_solde_debut)or die(mysqli_error($this->link));
							mysqli_commit($this->link);
						}
					}
					foreach ($categorie as $categ) {
						$categ = str_replace($search, $replace, $categ);
						if ($categ == $les_categ && $categ != "CAVentes10" && $categ != "CAVentes20" && $categ != "CAVentes0" && $categ != "Achats10" && $categ != "Achats20" && $categ != "Achats0") {
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
				$query_update_solde_courant = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET soldeCourant = '".$tab_solde_prev[$_les_dates_treso[0]."-".$_les_dates_treso[1]]."' WHERE date >= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-01' AND date <= '".($_les_dates_treso[0]."-".$_les_dates_treso[1])."-28' AND type = 'reel';";
				mysqli_query($this->link, $query_update_solde_courant);
				mysqli_commit($this->link);

			}
		}
	}

	/**
	 *	This method calculate the percentage by turnover of year N and year N-1 by date
	 *
	 *	@param string $date_param
	 *	@return array $pourcentage
	 */
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

		$sql = "SELECT CA, date FROM ".MAIN_DB_PREFIX."tresorerie where type='reel' ORDER BY date ASC";
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
	/**
	 *	This method calculate margin rate by date
	 *
	 *	@param string $date_param
	 *	@return array $taux_de_marge
	 */
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

		$sql = "SELECT CA, date FROM ".MAIN_DB_PREFIX."tresorerie where type='reel' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_ca = array();
		while($data = mysqli_fetch_assoc($res)){
			$data['date'] = explode("-", $data['date']);
			$tableau_ca[$data['date'][0]."-".$data['date'][1]] = $data['CA'];
		}

		$sql = "SELECT achat, date FROM ".MAIN_DB_PREFIX."tresorerie where type='reel' ORDER BY date ASC";
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
					$taux_de_marge[$i] = 100;
				}else{
					$taux_de_marge[$i] = round((($tableau_ca[$date_ca]+$tableau_achat[$date_ca])/-$tableau_achat[$date_ca])*100, 0);
				}	
				$i++;
			}
		}
		return $taux_de_marge;
	}

	//Calcul CA cumulé
	/**
	 *	This method calculate total turnover on twelve month
	 *
	 *	@param string $date_param
	 *	@return array $cumul
	 */
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

		$sql = "SELECT CA, date FROM ".MAIN_DB_PREFIX."tresorerie where type='reel' ORDER BY date ASC";
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

	/**
	*	This method update the fixed charges, since the started activity
	*
	*	@param array $categ
	*	@return void
	*/
	public function up_tresorerie_charge_fixe($categ)
	{
		$tableau_montant_categorie = array();
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		$tab1 = array();
		$sql = "SELECT DISTINCT b.rowid, bcat.label, b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank_categ as bcat, ".MAIN_DB_PREFIX."bank_class as bclass, ".MAIN_DB_PREFIX."bank_account as ba, ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."facture as f, ".MAIN_DB_PREFIX."categ_tva as ct, ".MAIN_DB_PREFIX."c_tva as t WHERE ba.rowid=b.fk_account AND bcat.rowid = bclass.fk_categ AND ba.entity = '$this->entity' AND bclass.lineid = b.rowid AND ct.fk_c_tva = t.rowid AND ct.fk_bank_categ = bcat.rowid AND b.amount<=0 ORDER BY b.dateo ASC;";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		while($data = mysqli_fetch_array($res, MYSQLI_ASSOC)){
			//if ($data['label'] != "CA Ventes 10" && $data['label'] != "CA Ventes 20") {
				$tab1[] = $data['label'];
				$tableau_montant_categorie[] = $data;
			//}
		}
		foreach ($categ as $value) {
			$value = str_replace($search, $replace, $value);
			//if ($value != "CAVentes20" && $value != "CAVentes10") {
				$query = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET ".$value."=NULL where type ='reel';";
				mysqli_query($this->link ,$query);
			//}
		}

		$sql2 = "SELECT DISTINCT date FROM ".MAIN_DB_PREFIX."tresorerie order by date asc;";
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
			$nb_jours = date("t", mktime(0,0,0,$date_mois[0], 1, $date_mois[1]));
			if (in_array($date_par_mois, $tableau_des_dates)) {
				$tab_insert[$tableau_categ['label']] += $tableau_categ['amount'];
				$query = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET ".$tableau_categ['label']." = ".$tab_insert[$tableau_categ['label']]." WHERE date >='".$date_par_mois."-01' AND date<='".$date_par_mois."-".$nb_jours."' AND type='reel';";
			}
			else{
				$tab_insert = array();
				$tab_insert[$tableau_categ['label']] += $tableau_categ['amount'];
				$query = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET ".$tableau_categ['label']." = ".$tab_insert[$tableau_categ['label']]." WHERE date >='".$date_par_mois."-01' AND date<='".$date_par_mois."-".$nb_jours."' AND type='reel';";
			}
			array_push($tableau_des_dates, $date_par_mois);
			mysqli_query($this->link, $query) or die (mysqli_error($this->link));
		}
		mysqli_commit($this->link);
	}

	/**
	 *	This method get outstanding supplier
	 *
	 *	@return array $tableau_encours_fourn
	 */
	public function getEncoursFournisseur()
	{
		$sql3 = "SELECT f.total_ttc, f.date_lim_reglement as dlr, s.nom FROM ".MAIN_DB_PREFIX."facture_fourn as f LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON f.fk_soc = s.rowid WHERE f.entity = 1 AND f.paye = 0 AND f.fk_statut = 1 ORDER BY dlr ASC";
		$res = mysqli_query($this->link ,$sql3) or die (mysqli_error($this->link));
		$tableau_encours_fourn = array();
		while($data = mysqli_fetch_assoc($res)){
			$tableau_encours_fourn[$data['dlr']] = $data['total_ttc'];
		}
		mysqli_commit($this->link);
		return $tableau_encours_fourn;
	}
	/**
	 *	This method get VAT collected by date
	 *
	 *	@param string $date_param
	 *	@return array $tva_collecte
	 */
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

		$sql1 = "SELECT CAVentes10, date FROM ".MAIN_DB_PREFIX."tresorerie where type='reel' ORDER BY date ASC;";
		$res = mysqli_query($this->link, $sql1) or die (mysqli_error($this->link));
		$tab_ca_10 = array();
		while ($data = mysqli_fetch_assoc($res)) {
			$data['date'] = explode("-", $data['date']);
			$tab_ca_10[$data['date'][0]."-".$data['date'][1]] = $data['CAVentes10'];
		}

		$sql = "SELECT CA, date FROM ".MAIN_DB_PREFIX."tresorerie where type='reel' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_ca = array();
		while($data = mysqli_fetch_assoc($res)){
			$data['date'] = explode("-", $data['date']);
			$tableau_ca[$data['date'][0]."-".$data['date'][1]] = ($data['CA'] - $tab_ca_10[$data['date'][0]."-".$data['date'][1]]);
		}

		$tva_collecte = array();
		foreach ($tableau_ca as $date_ca => $ca) {
			$_les_dates_ca = explode("-", $date_ca);
			if ($date_ca >= $date_temp_demande) {
				$tva_collecte[$i] = ($ca * 0.2)+($tab_ca_10[$date_ca] * 0.1);
				$i++;
			}
		}
		return $tva_collecte;
	}

	/**
	 *	This method get VAT deductible by date
	 *
	 *	@param string $date_param
	 *	@return array $tva_due
	 */
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

		$sql1 = "SELECT Achats10, date FROM ".MAIN_DB_PREFIX."tresorerie where type='reel' ORDER BY date ASC;";
		$res = mysqli_query($this->link, $sql1) or die (mysqli_error($this->link));
		$tab_ca_10 = array();
		while ($data = mysqli_fetch_assoc($res)) {
			$data['date'] = explode("-", $data['date']);
			$tab_achat_10[$data['date'][0]."-".$data['date'][1]] = $data['Achats10'];
		}

		$sql = "SELECT achat, date FROM ".MAIN_DB_PREFIX."tresorerie where type='reel' ORDER BY date ASC";
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
				$tva_due[$i] = ($achat * 0.2)+($tab_achat_10[$date_achat] * 0.1);;
				$i++;
			}
		}
		return $tva_due;
	}

	/**
	 *	This method get VAT paid by date
	 *
	 *	@param string $date_param
	 *	@return array $tva_payer
	 */
	public function getTVAPayer($date_param = 0)
	{
		if ($date_param == 0) {
			$date_temp_demande = $this->dateD;
		}
		else{
			$date_decoupe = explode("/", $date_param);
			$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
			$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]";
		}

		$sql = "SELECT TVA, date from ".MAIN_DB_PREFIX."tresorerie where type = 'reel' order by date asc";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$tableau_tva = array();
		while($data = mysqli_fetch_assoc($res)){
			$data['date'] = explode("-", $data['date']);
			$tableau_tva[$data['date'][0]."-".$data['date'][1]] = $data['TVA'];
		}
		
		$tva_payer = array();
		foreach ($tableau_tva as $date_tva => $tva) {
			$_les_dates_tva = explode("-", $date_tva);
			if ($date_tva >= $date_temp_demande) {
				$tva_payer[$i] = $tva;
				$i++;
			}
		}
		return $tva_payer;
	}

	/**
	 *	This method calculate cash balance of VAT
	 *	
	 *	@param array $tva_due
	 *	@param array $tva_collecte
	 *	@param array $tva_payer
	 *	@return array $tab_solde_tva
	 */
	public function calculSoldeTVA($tva_due, $tva_collecte, $tva_payer)
	{
		$tab_solde_tva = array();
		foreach ($tva_collecte as $key => $tva_col) {
			$tab_solde_tva[$key] = $tva_collecte[$key] + $tva_due[$key] + $tva_payer[$key];
		}
		return $tab_solde_tva;
	}

	/**
	 *	This method calculate total of turnover on twelve month
	 *
	 *	@param string $date_param
	 *	@return double $cumul_CA
	 */
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

		$sql = "SELECT CA, date FROM ".MAIN_DB_PREFIX."tresorerie where type='reel' ORDER BY date ASC";
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

	/**
	 *	This method calculate projected total of turnover on twelve month
	 *
	 *	@param string $date_param
	 *	@return double $cumul_CA
	 */
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

		$sql = "SELECT CA, date FROM ".MAIN_DB_PREFIX."tresorerie where type='prev' ORDER BY date ASC";
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

	/**
	 *	This method calculate percentage of total real turnover and projected
	 *
	 *	@param double $cumul_ca
	 *	@param double $cumul_ca_prev
	 *	@return double $pourcentage 
	 */
	public function pourcentage_cumul_ca($cumul_ca, $cumul_ca_prev)
	{
		if ($cumul_ca_prev == 0) {
			$pourcentage = "";
		}
		else{
			$pourcentage = ((-($cumul_ca*(100/(20+100))) + $cumul_ca_prev)/-$cumul_ca_prev)*100;
		}
		return $pourcentage;
	}

	/**
	 *	This method calculate total purchase since twelve month
	 *
	 *	@param string $date_param
	 *	@return double $cumul_achat
	 */
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

		$sql = "SELECT achat, date FROM ".MAIN_DB_PREFIX."tresorerie where type='reel' ORDER BY date ASC";
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

	/**
	 *	This method calculate projected total purchase since twelve month
	 *
	 *	@param string $date_param
	 *	@return double $cumul_achat
	 */
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

		$sql = "SELECT achat, date FROM ".MAIN_DB_PREFIX."tresorerie where type='prev' ORDER BY date ASC";
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

	/**
	 *	This method calculate percentage of total real purchase and projected
	 *
	 *	@param double $cumul_achat
	 *	@param double $cumul_achat_prev
	 *	@return double $pourcentage 
	 */
	public function pourcentage_cumul_achat($cumul_achat, $cumul_achat_prev)
	{
		if ($cumul_achat_prev == 0) {
			$pourcentage = "";
		}
		else{
			$pourcentage = ((-($cumul_achat*(100/(20+100))) + $cumul_achat_prev)/-$cumul_achat_prev)*100;
		}
		return $pourcentage;
	}

	/**
	 *	This method calculate real total fixed charges since twelve month by date
	 *
	 *	@param array $taux
	 *	@param string $date_param
	 *	@return double $tab1
	 */
	public function cumul_Charge($taux, $date_param = 0)
	{
		$tresoReel_HT = array();
		$date_decoupe = explode("/", $date_param);
		$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
		$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]-01";
		$date_temp_a = explode("-", $this->date);
		$date_temp_courant = "$date_temp_a[0]-$date_temp_a[1]-01";
		$sql = ($date == 0) ?  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$this->dateD-01' AND t.type='reel' ORDER BY t.date ASC;":  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$date' AND t.type='reel' ORDER BY t.date ASC";
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
								if ($finfo->name == "TVA") {
									$tab1[$finfo->name] += round($data[$i], 2);
								}
								else{
									$tab1[$finfo->name] += round($data[$i]*(100/($valueTaux+100)), 2);
								}
							}
						}
						$j[$finfo->name]++;
					}
				}
			}
		}
		return $tab1;
	}

	/**
	 *	This method calculate projected total fixed charges since twelve month by date
	 *
	 *	@param array $taux
	 *	@param string $date_param
	 *	@return double $tab1
	 */
	public function cumul_Charge_prev($taux, $date_param = 0)
	{
		$tresoReel_HT = array();
		$date_decoupe = explode("/", $date_param);
		$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
		$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]-01";
		$date_temp_a = explode("-", $this->date);
		$date_temp_courant = "$date_temp_a[0]-$date_temp_a[1]-01";
		$sql = ($date == 0) ?  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$this->dateD-01' AND t.type='prev' ORDER BY t.date ASC;":  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$date' AND t.type='prev' ORDER BY t.date ASC";
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

	/**
	 *	This method calculate real total fixed charges since twelve month by date with other key in return array
	 *
	 *	@param array $taux
	 *	@param string $date_param
	 *	@return double $tab1
	 */
	public function cumul_Charge_2($taux, $date_param = 0)
	{
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		$tresoReel_HT = array();
		$date_decoupe = explode("/", $date_param);
		$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
		$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]-01";
		$date_temp_a = explode("-", $this->date);
		$date_temp_courant = "$date_temp_a[0]-$date_temp_a[1]-01";
		$query = "SELECT bcat.rowid, bcat.label FROM ".MAIN_DB_PREFIX."bank_categ as bcat where bcat.entity = '$this->entity'";
		$result = mysqli_query($this->link, $query) or die (mysqli_error($this->link));
		$row = array();
		while($data = mysqli_fetch_assoc($result)){
			$value = str_replace($search, $replace, $data['label']);
			$row[$value] = $data['rowid']; 
		}
		$sql = ($date == 0) ?  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$this->dateD-01' AND t.type='reel' ORDER BY t.date ASC;":  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$date' AND t.type='reel' ORDER BY t.date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$nb = mysqli_num_fields($res);
		$tab1 = array();
		$j = array();
		while ($data = mysqli_fetch_row($res)) {
			for ($i=0; $i < $nb; $i++) {
				$finfo = mysqli_fetch_field_direct($res, $i);
				foreach ($taux as $categTaux => $valueTaux) {
					if ($categTaux != "CAVentes10" && $categTaux != "CAVentes20" && $categTaux != "CAVentes0" && $categTaux != "Achats10" && $categTaux != "Achats20" && $categTaux != "Achats0") {
						if($categTaux == $finfo->name){
							if ($j[$row[$categTaux]]<12) {
								if ($finfo->name == "TVA") {
									$tab1[$row[$categTaux]] += round(-$data[$i], 2);
								}
								else{
									$tab1[$row[$categTaux]] += round(-$data[$i]*(100/($valueTaux+100)), 2);
								}
							}
							$j[$row[$categTaux]]++;
						}
					}
				}
			}
		}
		return $tab1;
	}

	/**
	 *	This method calculate projected total fixed charges since twelve month by date with other key in return array
	 *
	 *	@param array $taux
	 *	@param string $date_param
	 *	@return double $tab1
	 */
	public function cumul_Charge_prev_2($taux, $date_param = 0)
	{
		$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
		$replace = array("");
		$tresoReel_HT = array();
		$date_decoupe = explode("/", $date_param);
		$date = "$date_decoupe[2]-$date_decoupe[1]-$date_decoupe[0]";
		$date_temp_demande = "$date_decoupe[2]-$date_decoupe[1]-01";
		$date_temp_a = explode("-", $this->date);
		$date_temp_courant = "$date_temp_a[0]-$date_temp_a[1]-01";
		$query = "SELECT bcat.rowid, bcat.label FROM ".MAIN_DB_PREFIX."bank_categ as bcat where bcat.entity = '$this->entity'";
		$result = mysqli_query($this->link, $query) or die (mysqli_error($this->link));
		$row = array();
		while($data = mysqli_fetch_assoc($result)){
			$value = str_replace($search, $replace, $data['label']);
			$row[$value] = $data['rowid']; 
		}
		$sql = ($date == 0) ?  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$this->dateD-01' AND t.type='prev' ORDER BY t.date ASC;":  "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie as t WHERE t.date>='$date' AND t.type='prev' ORDER BY t.date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$nb = mysqli_num_fields($res);
		$tab1 = array();
		$j = array();
		while ($data = mysqli_fetch_row($res)) {
			for ($i=0; $i < $nb; $i++) {
				$finfo = mysqli_fetch_field_direct($res, $i);
				foreach ($taux as $categTaux => $valueTaux) {
					if ($categTaux != "CAVentes10" && $categTaux != "CAVentes20" && $categTaux != "CAVentes0" && $categTaux != "Achats10" && $categTaux != "Achats20" && $categTaux != "Achats0") {
						if($categTaux == $finfo->name){
							if ($j[$row[$categTaux]]<12) {
								$tab1[$row[$categTaux]] += round(-$data[$i], 2);
							}
							$j[$row[$categTaux]]++;
						}
					}
				}
			}
		}
		return $tab1;
	}

	/**
	 *	This method calculate percentage of total real fixed charges and projected
	 *
	 *	@param double $cumul_charge
	 *	@param double $cumul_charge_prev
	 *	@return double $pourcentage 
	 */
	public function pourcentage_cumul_charge($cumul_Charge, $cumul_Charge_prev)
	{
		$pourcentage = array();
		foreach ($cumul_Charge_prev as $key => $value) {
			if ($value==0) {
				$pourcentage[$key] = "";
			}
			else{
				$pourcentage[$key] = ((-$cumul_Charge[$key]+$cumul_Charge_prev[$key])/$cumul_Charge_prev[$key])*100;
			}
		}
		return $pourcentage;
	}

	/**
	 *	This method calculate real total fixed charges 
	 *	
	 *	@param array $total_charge
	 *	@return double $cumul_total_charge
	 */
	public function cumul_total_charge($total_charge)
	{
		$cumul_total_charge = 0;
		foreach ($total_charge as $key => $valeur) {
			if($key<12){
				$cumul_total_charge += $valeur;
			}
		}
		return $cumul_total_charge;
	}

	/**
	 *	This method calculate projected total fixed charges 
	 *	
	 *	@param array $total_charge_prev
	 *	@return double $cumul_total_charge_prev
	 */
	public function cumul_total_charge_prev($total_charge_prev)
	{
		$cumul_total_charge_prev = 0;
		foreach ($total_charge_prev as $key => $valeur) {
			if ($key < 12) {
				$cumul_total_charge_prev += $valeur;
			}
		}
		return $cumul_total_charge_prev;
	}

	/**
	 *	This method calculate percentage of total real fixed charges and projected
	 *
	 *	@param double $total_charge
	 *	@param double $total_charge_prev
	 *	@return double $pourcentage 
	 */
	public function pourcentage_cumul_total_charge($total_charge, $total_charge_prev)
	{
		if ($total_charge_prev == 0) {
			$pourcentage = "";
		}
		else{
			$pourcentage = ((-($total_charge) + $total_charge_prev)/(-$total_charge_prev))*100;
		}
		return $pourcentage;
	}

	/**
	 *	This method calculate real total cash balance since twelve month by date 
	 *	
	 *	@param array $date_param
	 *	@return double $cumul_solde_tresorerie
	 */
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

		$sql = "SELECT soldeCourant, date FROM ".MAIN_DB_PREFIX."tresorerie where type='reel' ORDER BY date ASC";
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

	/**
	 *	This method calculate projected total cash balance since twelve month by date 
	 *	
	 *	@param array $date_param
	 *	@return double $cumul_solde_tresorerie
	 */
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

		$sql = "SELECT soldeCourant, date FROM ".MAIN_DB_PREFIX."tresorerie where type='prev' ORDER BY date ASC";
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

	/**
	 *	This method calculate percentage of total real cash balance and projected
	 *
	 *	@param double $cumul_solde_tresorerie
	 *	@param double $cumul_solde_tresorerie_prev
	 *	@return double $pourcentage 
	 */
	public function pourcentage_cumul_solde_tresorerie($cumul_solde_tresorerie, $cumul_solde_tresorerie_prev)
	{
		if ($cumul_solde_tresorerie_prev == 0) {
			$pourcentage = "";
		}
		else{
			$pourcentage = ((-($cumul_solde_tresorerie) + $cumul_solde_tresorerie_prev)/(-$cumul_solde_tresorerie_prev))*100;
		}
		return $pourcentage;
	}

	/**
	 *	This method get the name of company
	 *
	 *	@return string $nom
	 */
	public function getNomEntreprise()
	{
		$sql = "SELECT label FROM ".MAIN_DB_PREFIX."entity where rowid='$this->entity'";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$nom = mysqli_fetch_row($res)[0];
		return $nom;
	}

	/**
	 *	This method get turnover of year N-1
	 *
	 *	@return double $ca_N_moins_1
	 */
	public function getCA_N_moins_1()
	{
		$date_N_1 = date("Y")-1;
		$date_N = date("Y");
		$sql = "SELECT date, CA FROM ".MAIN_DB_PREFIX."tresorerie where type='reel' AND date >='$date_N_1-01-01' AND date<'$date_N-01-01' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$ca_N_moins_1 = array();
		while ($data = mysqli_fetch_assoc($res)) {
			$la_date = explode("-", $data['date']);
			$ca_N_moins_1[$la_date[1]] = $data['CA']*(100/(20+100));
		}
		return $ca_N_moins_1;
	}

	/**
	 *	This method get turnover of year N
	 *
	 *	@return double $ca_N
	 */
	public function getCA_N()
	{
		$date_N_1 = date("Y")+1;
		$date_N = date("Y");
		$sql = "SELECT date, CA FROM ".MAIN_DB_PREFIX."tresorerie where type='reel' AND date <'$date_N_1-01-01' AND date>='$date_N-01-01' ORDER BY date ASC";
		$res = mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
		$ca_N = array();
		while ($data = mysqli_fetch_assoc($res)) {
			$la_date = explode("-", $data['date']);
			$ca_N[$la_date[1]] = $data['CA']*(100/(20+100));
		}
		return $ca_N;
	}

	/**
	 *	This method make it possible to put back and add new year when the current year is past
	 *
	 *	@return void
	 */
	public function remise_a_zero()
	{
		$sql_verif = "SELECT date FROM llx_tresorerie ORDER BY date ASC LIMIT 1;";
		$res = mysqli_query($this->link, $sql_verif) or die (mysqli_error($this->link));
		$date_verif = mysqli_fetch_row($res)[0];
		if ($date_verif == (date("Y")-2)) {
			$sql = "DELETE FROM llx_tresorerie WHERE date < '".(date("Y")-1)."-01-01';";
			mysqli_query($this->link, $sql) or die (mysqli_error($this->link));
			for ($i=0; $i < 12; $i++) { 
				$sql_reel = "INSERT into llx_tresorerie (date, type) VALUES ('".(date("Y")+1)."-$i-28', 'reel');";
				$sql_prev = "INSERT into llx_tresorerie (date, type) VALUES ('".(date("Y")+1)."-$i-28', 'prev');";
				mysqli_query($this->link, $sql_reel) or die (mysqli_error($this->link));
				mysqli_query($this->link, $sql_prev) or die (mysqli_error($this->link));
			}
			?>
                <div class="jnotify-container">
                    <div class="jnotify-notification jnotify-notification-success">
                        <div class="jnotify-background"></div>
                            <a onclick="fonction()" class="jnotify-close">
                               ×
                            </a>
                        <div class="jnotify-message">
                            <div>
                                Opération éffectué avec succès.
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
		}
		else{
			?>
                <div class="jnotify-container">
                    <div class="jnotify-notification jnotify-notification-error">
                        <div class="jnotify-background"></div>
                            <a onclick="fonction()" class="jnotify-close">
                               ×
                            </a>
                        <div class="jnotify-message">
                            <div>
                                L'opération ne peut être effectuée, puisque vous êtes toujours dans l'année comprise entre <?php echo (date("Y")-1)." et ".(date("Y")+1); ?> 
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
		}
	}

	//3 methode en desous pas utile pour l'instant, la methode getSolde() fait cela !!
/*	public function up_tresorerie_Achat()
	{
		$sql = "SELECT b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."bank_account as ba WHERE b.amount < 0 AND ba.entity = '$this->entity' order by b.dateo ASC";
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
		$sql = "SELECT b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank as b, ".MAIN_DB_PREFIX."bank_account as ba WHERE b.amount > 0 AND ba.entity = '$this->entity' order by b.dateo ASC";
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
			$query = "SELECT b.amount, b.dateo FROM ".MAIN_DB_PREFIX."bank as b where fk_type='SOLD' order by dateo ASC;";
			$res = mysqli_query($query)or die(mysqli_error());
			while ($data = mysqli_fetch_assoc($res)) {
				$dat = explode("-", $data['dateo']);
				$tab_sold_init[$dat[0]."-".$dat[1]] = $data['amount'];
			}
			$le_calcul = round($tab_sold_init[$date]+$tab_ca[$date]+$tab_achat[$date], 2);
		}
		else{
			$query = "SELECT soldeCourant FROM ".MAIN_DB_PREFIX."tresorerie WHERE date >= '".date("Y")."-".(date("m")-1)."-01' AND date <= '".date("Y")."-".(date("m")-1)."-28' AND type='reel'";
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
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."tresorerie where type='reel' ORDER BY date ASC";
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
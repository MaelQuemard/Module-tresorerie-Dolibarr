<?php 
/* Copyright (C) 2015	Mael Quemard
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
 *	This file is used for change the table of database
 */

/**
 *	Class tresorerie is used for everything related to the dashboard
 *
 *	@filesource /htdocs/tresorerie/class/modificationTable.php
 *	@package Class
 *	@licence http://www.gnu.org/licenses/ GPL
 *	@version Version 1.0
 *	@author Maël Quémard
 */
class modificationTable extends CommonObject
{
	/**
	 *	This is the contructor of class, get the link of connection (database)
	 *	
	 *	@param Object $leink
	 *	@global object $conf
	 *	@global object $langs
	 *	@var int $this->entity number of entity
	 */
	function __construct($leink)
	{
		global $conf, $langs;
		$this->link = $leink;
		$this->entity = $conf->entity;
	}

	/**
	 *	This method get name of category (fixed charges)
	 *
	 *	@return array $this->categorie
	 */
	public function getCategorie()
	{
		$query_categ = "SELECT label FROM `llx_bank_categ` ORDER BY `label` ASC;";
		$resultat = mysqli_query($this->link, $query_categ) or die (mysqli_error($this->link));
		while ($data= mysqli_fetch_assoc($resultat)) {
			$this->categorie[] = $data['label'];
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
		$query_categ = "SELECT label FROM `llx_bank_categ` ORDER BY `label` ASC;";
		$resultat = mysqli_query($this->link, $query_categ) or die (mysqli_error($this->link));
		$nb = mysqli_num_rows($resultat);
		return $nb;
	}

	/**
	 *	This method adds the category on the table llx_tresorerie
	 *
	 *	@param array $categ
	 */
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

	/**
	 *	This method remove the category on the table llx_tresorerie
	 *
	 *	@param array $categ
	 */
	public function supprimerCategorie($categ)
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
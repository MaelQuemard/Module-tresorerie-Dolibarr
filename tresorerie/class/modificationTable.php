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
 *	\file       htdocs/tresorerie/class/initialisation.php
 *	\ingroup    tresorerie
 *	\brief      File to initialisation the module of tresorerie
 */
class modificationTable extends CommonObject
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
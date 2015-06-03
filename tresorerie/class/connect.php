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
 *	\file       htdocs/tresorerie/class/connect.php
 *	\ingroup    tresorerie
 *	\brief      File of class to connect into database
 */
class connect
{
	var $link;

	function __construct($dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user ,$dolibarr_main_db_pass)
	{
		if (!empty($dolibarr_main_db_name) && !empty($dolibarr_main_db_host) && !empty($dolibarr_main_db_user)) {
			$this->link = mysqli_connect($dolibarr_main_db_host, $dolibarr_main_db_user, $dolibarr_main_db_pass);
			mysqli_select_db($this->link, $dolibarr_main_db_name);
			mysqli_set_charset($this->link, 'utf8' );
		}
	}

	public function link()
	{
		return $this->link;
	}
}
?>
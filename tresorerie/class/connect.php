<?php 
/**
* 
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
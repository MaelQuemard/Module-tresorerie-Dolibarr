<?php
/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 *   	\file       dev/skeletons/skeleton_page.php
 *		\ingroup    mymodule othermodule1 othermodule2
 *		\brief      This file is an example of a php page
 *					Put here some comments
 */

//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');			// Do not check anti CSRF attack test
//if (! defined('NOSTYLECHECK'))   define('NOSTYLECHECK','1');			// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');		// Do not check anti POST attack test
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');			// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');			// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined("NOLOGIN"))        define("NOLOGIN",'1');				// If this page is public (can be called outside logged session)

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';					// to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../dolibarr/htdocs/main.inc.php';     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../../dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (! $res) die("Include of main fails");
// Change this following line to use the correct relative path from htdocs
require_once 'class/connect.php';
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

llxHeader('','MyPageName','');

$form=new Form($db);
// Put here content of your page3
$connect = new connect($dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user ,$dolibarr_main_db_pass);
$link = $connect->link();
?>
    <script>
        function ReloadPage() { 
           location.reload(true);
        };

        function test(){
          setTimeout("ReloadPage()", 100);
          $(".modal").show();
        };
    </script>
    <div class="modal" width="1100" height="3100" style="display:none; position:fixed; top:50%; left:50%;">
        <img src="loader.gif">
    </div>
    <table class="notopnoleftnoright" border="0" style="margin-bottom: 2px;">
        <tbody>
            <tr>
                <td class="nobordernopadding hideonsmartphone" width="40" valign="middle" align="left">
                    <img border="0" title="" alt="" src="/dolibarr/htdocs/theme/eldy/img/title.png"></img>
                </td>
                <td class="nobordernopadding">
                    <div class="titre">Espace d'ajout d'information sur les categories</div>
                </td>
            </tr>
        </tbody>
    </table>
<?php
    $sql = "SELECT DISTINCT bcat.label, ct.taux FROM llx_bank_categ as bcat, llx_c_tva as ct, llx_categ_tva as c WHERE ct.rowid = c.fk_c_tva AND bcat.rowid = c.fk_bank_categ;";
    $result = mysqli_query($link, $sql) or die (mysqli_error());
    $index = 0;
    $id = array("20","10", "5.5", "2.1", "0");
    $prin = "";
    $label;
    $class = array("pair", "impair");
    ?>
        <form action="#" method="post" onsubmit="test()">
            <table id="tableau" class="noborder nohover" width="100%">
                <tbody> 
                    <tr class="liste_titre">
                        <td>Categories</td>
                        <td>20</td>
                        <td>10</td>
                        <td>5.5</td>
                        <td>2.1</td>
                        <td>0</td>
                    </tr>

    <?php      
    while($data = mysqli_fetch_assoc($result)) {
        $index++;
        ($index%2==0) ? $t = $class[0] : $t =$class[1];
        $label .= $data['label'].";";
        $prin .= "<tr class=\"$t\" ><td name=\"$index\">".$data['label']."</td>";
        for ($i=0; $i < 5; $i++) { 
            if ($id[$i] == $data['taux']) {
                $prin .= "<td><input type=\"radio\" name=\"$index\" value=\"$id[$i]\" checked></td>";
            }
            else{
                $prin .= "<td><input type=\"radio\" name=\"$index\" value=\"$id[$i]\"></td>";
            }
        }
        $prin .= "</tr>";
    }
    print ($prin);
    ?>
                </tbody>
            </table>
           <input type="submit" id="boutton">
        </form>
    <?php
    $lab = explode(";", $label);
    for ($i=0; $i <= $index; $i++) { 
        if (isset($_POST[$i])) {
            $sql2 = "SELECT t.rowid FROM llx_c_tva as t WHERE t.fk_pays = 1 AND t.active=1 AND t.taux=".$_POST[$i].";";
            $res = mysqli_query($link, $sql2);
            $sql3 = "SELECT bc.rowid from llx_bank_categ as bc WHERE bc.label =\"".$lab[$i-1]."\";";
            $res2 = mysqli_query($link, $sql3);
            while($data = mysqli_fetch_assoc($res)) {
                while($rep = mysqli_fetch_assoc($res2)){
                        $sql1 = "UPDATE llx_categ_tva SET fk_c_tva = \"".$data['rowid']."\" WHERE fk_bank_categ=\"".$rep['rowid']."\";";
                        mysqli_query($link, $sql1) or die (mysqli_error());
                }
            }
        }
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



// End of page
llxFooter();
$db->close();
?>

<?php
/* Copyright (C) 2015 Mael Quemard  <quemard.mael@gmail.com>
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
 *  \file       htdocs/treorerie/pageAcceuil.php
 *  \ingroup    tresorerie
 *  \brief      Page to initialisation the module
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';					// to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../dolibarr/htdocs/main.inc.php';     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../../dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (! $res) die("Include of main fails");
// Change this following line to use the correct relative path from htdocs
require_once 'class/connect.php';
include_once DOL_DOCUMENT_ROOT .'/tresorerie/class/initialisation.php';
include_once DOL_DOCUMENT_ROOT .'/tresorerie/class/modificationTable.php';
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

llxHeader('','Page d\'initialisation et d\'ajout de rubrique' ,'');

$form=new Form($db);
// Put here content of your page
$connect = new connect($dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user ,$dolibarr_main_db_pass);
$link = $connect->link();
?>
<link rel="stylesheet" type="text/css" href="style/style.css">
      <table class="notopnoleftnoright" border="0" style="margin-bottom: 2px;">
        <tbody>
            <tr>
                <td class="nobordernopadding hideonsmartphone" width="40" valign="middle" align="left">
                    <img border="0" title="" alt="" src="/dolibarr/htdocs/theme/eldy/img/title.png"></img>
                </td>
                <td class="nobordernopadding">
                    <div class="titre">Espace d'initialisation du module de trésorerie, ainsi que récupération de catégorie ajoutées</div>
                </td>
            </tr>
        </tbody>
    </table>
    <center>
    <div class="form">
        <form action="#" method="get">
            <p class="mycss">Après avoir activé le module veuillez cliquer sur le boutton ci-dessous pour initialiser le module et donc pouvoir utiliser les pages associées</p>
            <input type="submit" class="btn" name="init" value="Initialisation"></input>
        </form>
    </div>
    </center>

    <center>
    <div class="form">
        <form action="#" method="get">
            <p class="mycss">Avez vous ajouté ou supprimé une categorie ?</p>
            <input type="submit" class="btnAjout" name="maj" value="Mettre à jour"></input>
        </form>
    </div>
    </center>


<?php
if (isset($_GET['init'])) {
    $init = new initialisation($link);
    $init->createTable();
    $init->ajout_date_tresorerie();
    $init->up_tresorerie_charge_fixe();
    $tab_ca = $init->up_tresorerie_CA();
    $tab_achat = $init->up_tresorerie_Achat();
    $categ = $init->getCategorie();
    $tab_charge = $init->getCharge_test($categ);
    $init->calcul_solde_tresorerie($tab_ca, $tab_achat, $tab_charge);
}

$modif = new modificationTable($link);
$nb_lignes = $modif->getNbLignes();
$categorie = $modif->getCategorie();
$search = array(',', '-', '(', ')', ' ', '/', "'", '+');
$replace = array("");
$categorie2 = array();
for ($i=0; $i <= $nb_lignes; $i++) { 
  $categorie2[$i] = str_replace($search, $replace, $categorie[$i]);
}

if (isset($_GET['maj'])) {
  $modif->ajoutCategorie($categorie2);
  $modif->supprimerCategorie($categorie2);
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

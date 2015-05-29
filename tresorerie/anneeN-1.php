<?php
/* Copyright (C) 2015   Mael Quemard
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
 *   	\file       htdocs/tresorerie/anneeN-1.php
 *		\ingroup    tresorerie
 *		\brief      Generate file to import tresorerie 
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
// Put here content of your page
$connect = new connect($dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user ,$dolibarr_main_db_pass);
$link = $connect->link();
?>
    <script type="text/javascript">
        function fonction(){
            $(".jnotify-container").css("display", "none");
        }
    </script>
    <table class="notopnoleftnoright" border="0" style="margin-bottom: 2px;">
        <tbody>
            <tr>
                <td class="nobordernopadding hideonsmartphone" width="40" valign="middle" align="left">
                    <img border="0" title="" alt="" src="/dolibarr/htdocs/theme/eldy/img/title.png"></img>
                </td>
                <td class="nobordernopadding">
                    <div class="titre">Espace d'importation des données de l'année N-1</div>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="fichecenter">
        <div class="fichethirdleft">
            <form action="" method="get">
                <table class="noborder nohover" width="100%">
                    <tbody>
                        <tr class="liste_titre">
                            <td class="center" colspan="2">
                                Importer votre fichier csv
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="file" name="file">
                            </td>
                            <td>
                                <input class="button" type="submit">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
        <div class="fichethirdleft">
            <form action="" method="get">
                <table class="noborder nohover" width="100%">
                    <tbody>
                        <tr class="liste_titre">
                            <td class="center" colspan="2">
                                Générer votre fichier csv
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="text" name="nom">
                            </td>
                            <td>
                                <input class="button" type="submit">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
<?php
    if (isset($_GET['file'])) {
         $fichier = dirname(__FILE__).'/'.$_GET['file'];
  
        /* On ouvre le fichier à importer en lecture seulement */
        if (file_exists($fichier))
            $fp = fopen("$fichier", "r"); 
        else
            { /* le fichier n'existe pas */
                echo "Fichier introuvable !<br>Importation stoppée.";
                exit();
            }
            $ligne0 = false;
            while (!feof($fp))
            { /* Tant qu'on n'atteint pas la fin du fichier */ 
                $ligne = fgets($fp,4096); /* On lit une ligne */ 

                if ($ligne0) {
                    /* On récupère les champs séparés par ; dans liste*/
                    if (strstr(",", $ligne)) {
                        $liste = explode(",",$ligne);
                    }else{
                        $liste = explode(";",$ligne);
                    }
                    /* Ajouter un nouvel enregistrement dans la table */ 
                    if (sizeof($liste)>1) {
                        $sql = "SELECT * FROM llx_tresorerie LIMIT 1";
                        $res = mysqli_query($link, $sql) or die (mysqli_error($link));
                        $nb = mysqli_num_fields($res);
                        $categ = array();
                        while ($data = mysqli_fetch_row($res)) {
                            for ($i=0; $i < $nb; $i++) {
                                $finfo = mysqli_fetch_field_direct($res, $i);
                                $categ[] = $finfo->name;
                            }
                        }
                        $sql = "UPDATE llx_tresorerie SET ";
                        foreach ($liste as $key => $value) {
                            foreach ($categ as $cle => $rowC) {
                                if ($key == $cle) {
                                   if ($key < (sizeof($liste))-1) {
                                        if (strlen($value <1)) {
                                            if ($rowC != "rowid") {
                                                $sql .= " $rowC=NULL,";
                                            }                                            
                                        }
                                        elseif ($rowC == "date") {
                                            $la_cle_date = $key;
                                        }
                                        else{
                                            $sql .= " $rowC='$value',";
                                        }
                                        
                                    }
                                    else{
                                        if (strstr("-", $liste[$la_cle_date])) {
                                            $la_date = explode("-", $liste[$la_cle_date]);
                                            $nb_jours = date("t", mktime(0,0,0, $la_date[1], 1, $la_date[0]));
                                            $row2 = preg_replace('/\s/', '', $value); 
                                            $sql .= " $rowC='$row2' WHERE date>='$la_date[0]-$la_date[1]-01' AND date<='$la_date[0]-$la_date[1]-$nb_jours' AND type='$row2';";
                                        }
                                        else{
                                            $la_date = explode("/", $liste[$la_cle_date]);
                                            $nb_jours = date("t", mktime(0,0,0, $la_date[1], 1, $la_date[2]));
                                            $row2 = preg_replace('/\s/', '', $value); 
                                            $sql .= " $rowC='$row2' WHERE date>='$la_date[2]-$la_date[1]-01' AND date<='$la_date[2]-$la_date[1]-$nb_jours' AND type='$row2';";
                                        }
                                    }
                                }
                            }
                        }
                        $result= mysqli_query($link, $sql);
                    }
                    if(mysqli_error($link))
                    { /* Erreur dans la base de donnees, surement la table qu'il faut créer */
                        ?>
                            <div class="jnotify-container">
                                <div class="jnotify-notification jnotify-notification-error">
                                    <div class="jnotify-background"></div>
                                    <a onclick="fonction()" class="jnotify-close">
                                        ×
                                    </a>
                                    <div class="jnotify-message">
                                        <div>
                                            Erreur dans la base de données : <?php echo mysqli_error($link);?>
                                            <br>Importation stoppée
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                        
                        exit();
                    }
                }
                else{
                    $ligne0 = true;
                }
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
                                Importation terminée avec succès.
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
            fclose($fp);
    }
    if (isset($_GET['nom'])) {
        $sql = "SELECT * FROM llx_tresorerie LIMIT 1";
        $res = mysqli_query($link, $sql) or die (mysqli_error($link));
        $nb = mysqli_num_fields($res);
        $string = "";
        while ($data = mysqli_fetch_row($res)) {
            for ($i=0; $i < $nb; $i++) {
                $finfo = mysqli_fetch_field_direct($res, $i);
                $string .= $finfo->name.",";
            }
        }
        //!!!
        //!!!!!! Pour pouvoir écrire il faut donner les permissions de lecture et d'ériture au dossier !!!!!!
        //!!!
        $file = $_GET['nom'].".csv";
        $f = fopen($file, "w");
        fwrite($f, $string);
        ?>
            <div class="jnotify-container">
                <div class="jnotify-notification jnotify-notification-success">
                    <div class="jnotify-background"></div>
                        <a onclick="fonction()" class="jnotify-close">
                           ×
                        </a>
                    <div class="jnotify-message">
                        <div>
                            Votre fichier à été généré au chemin suivant : <?php echo realpath($_GET['nom'].'.csv'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
        fclose($f);
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

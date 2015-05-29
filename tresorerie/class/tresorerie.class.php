<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *  \file       dev/skeletons/tresorerie.class.php
 *  \ingroup    mymodule othermodule1 othermodule2
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *				Initialy built by build_class_from_table on 2015-04-21 16:45
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class Tresorerie extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='tresorerie';			//!< Id that identify managed objects
	var $table_element='tresorerie';		//!< Name of table without prefix where object is stored

    var $id;
    
	var $VentesPrestations;
	var $VentesMatériels;
	var $VentesLogiciels;
	var $VentesPortsdemarchandises;
	var $AchatPrestation;
	var $AchatMatériels;
	var $AchatLogiciels;
	var $AchatPortdemarchandises;
	var $Emprunts;
	var $ElectricitéEauGaz;
	var $Fournituresadministratives;
	var $EquipementsMobilierInformatique;
	var $Locations;
	var $AssurancesRCP;
	var $AssurancesMutuellePrevoyanceRetraite;
	var $Maintenancematérielle;
	var $Maintenancelogicielle;
	var $Entretienlocauxmatérielvéhicule;
	var $FraisdecréationEntreprise;
	var $HonorairescomptablesCLG;
	var $AutresHonorairesCLS;
	var $Fraisdacteetdecontentieux;
	var $Affranchissements;
	var $Téléphonefixe;
	var $Téléphonemobile;
	var $ADSLFax;
	var $CopieurRicoh;
	var $Nomdedomaine;
	var $HebergementLinux;
	var $HebergementWindows;
	var $Publicité;
	var $Fraisdetransport;
	var $Pagesjaunes;
	var $Voyagesetdéplacements;
	var $IndemnitésKilométriques;
	var $Impôtsettaxes;
	var $RémunérationGérant1;
	var $RémunérationGérant2;
	var $CotisationssocialesGérant1;
	var $CotisationssocialesGérant2;
	var $Fraisbancaires;
	var $Rémunérationssalariés;
	var $Cotisationssocialessalariés;
	var $Agiosetintérêtspayés;
	var $RéseauxdentrepriseBNILinkedInViadeo;
	var $TVA;
	var $Formation;
	var $test;
	var $CA;
	var $achat;
	var $date='';
	var $type;

    


    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
        return 1;
    }


    /**
     *  Create object into database
     *
     *  @param	User	$user        User that creates
     *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
     *  @return int      		   	 <0 if KO, Id of created object if OK
     */
    function create($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->VentesPrestations)) $this->VentesPrestations=trim($this->VentesPrestations);
		if (isset($this->VentesMatériels)) $this->VentesMatériels=trim($this->VentesMatériels);
		if (isset($this->VentesLogiciels)) $this->VentesLogiciels=trim($this->VentesLogiciels);
		if (isset($this->VentesPortsdemarchandises)) $this->VentesPortsdemarchandises=trim($this->VentesPortsdemarchandises);
		if (isset($this->AchatPrestation)) $this->AchatPrestation=trim($this->AchatPrestation);
		if (isset($this->AchatMatériels)) $this->AchatMatériels=trim($this->AchatMatériels);
		if (isset($this->AchatLogiciels)) $this->AchatLogiciels=trim($this->AchatLogiciels);
		if (isset($this->AchatPortdemarchandises)) $this->AchatPortdemarchandises=trim($this->AchatPortdemarchandises);
		if (isset($this->Emprunts)) $this->Emprunts=trim($this->Emprunts);
		if (isset($this->ElectricitéEauGaz)) $this->ElectricitéEauGaz=trim($this->ElectricitéEauGaz);
		if (isset($this->Fournituresadministratives)) $this->Fournituresadministratives=trim($this->Fournituresadministratives);
		if (isset($this->EquipementsMobilierInformatique)) $this->EquipementsMobilierInformatique=trim($this->EquipementsMobilierInformatique);
		if (isset($this->Locations)) $this->Locations=trim($this->Locations);
		if (isset($this->AssurancesRCP)) $this->AssurancesRCP=trim($this->AssurancesRCP);
		if (isset($this->AssurancesMutuellePrevoyanceRetraite)) $this->AssurancesMutuellePrevoyanceRetraite=trim($this->AssurancesMutuellePrevoyanceRetraite);
		if (isset($this->Maintenancematérielle)) $this->Maintenancematérielle=trim($this->Maintenancematérielle);
		if (isset($this->Maintenancelogicielle)) $this->Maintenancelogicielle=trim($this->Maintenancelogicielle);
		if (isset($this->Entretienlocauxmatérielvéhicule)) $this->Entretienlocauxmatérielvéhicule=trim($this->Entretienlocauxmatérielvéhicule);
		if (isset($this->FraisdecréationEntreprise)) $this->FraisdecréationEntreprise=trim($this->FraisdecréationEntreprise);
		if (isset($this->HonorairescomptablesCLG)) $this->HonorairescomptablesCLG=trim($this->HonorairescomptablesCLG);
		if (isset($this->AutresHonorairesCLS)) $this->AutresHonorairesCLS=trim($this->AutresHonorairesCLS);
		if (isset($this->Fraisdacteetdecontentieux)) $this->Fraisdacteetdecontentieux=trim($this->Fraisdacteetdecontentieux);
		if (isset($this->Affranchissements)) $this->Affranchissements=trim($this->Affranchissements);
		if (isset($this->Téléphonefixe)) $this->Téléphonefixe=trim($this->Téléphonefixe);
		if (isset($this->Téléphonemobile)) $this->Téléphonemobile=trim($this->Téléphonemobile);
		if (isset($this->ADSLFax)) $this->ADSLFax=trim($this->ADSLFax);
		if (isset($this->CopieurRicoh)) $this->CopieurRicoh=trim($this->CopieurRicoh);
		if (isset($this->Nomdedomaine)) $this->Nomdedomaine=trim($this->Nomdedomaine);
		if (isset($this->HebergementLinux)) $this->HebergementLinux=trim($this->HebergementLinux);
		if (isset($this->HebergementWindows)) $this->HebergementWindows=trim($this->HebergementWindows);
		if (isset($this->Publicité)) $this->Publicité=trim($this->Publicité);
		if (isset($this->Fraisdetransport)) $this->Fraisdetransport=trim($this->Fraisdetransport);
		if (isset($this->Pagesjaunes)) $this->Pagesjaunes=trim($this->Pagesjaunes);
		if (isset($this->Voyagesetdéplacements)) $this->Voyagesetdéplacements=trim($this->Voyagesetdéplacements);
		if (isset($this->IndemnitésKilométriques)) $this->IndemnitésKilométriques=trim($this->IndemnitésKilométriques);
		if (isset($this->Impôtsettaxes)) $this->Impôtsettaxes=trim($this->Impôtsettaxes);
		if (isset($this->RémunérationGérant1)) $this->RémunérationGérant1=trim($this->RémunérationGérant1);
		if (isset($this->RémunérationGérant2)) $this->RémunérationGérant2=trim($this->RémunérationGérant2);
		if (isset($this->CotisationssocialesGérant1)) $this->CotisationssocialesGérant1=trim($this->CotisationssocialesGérant1);
		if (isset($this->CotisationssocialesGérant2)) $this->CotisationssocialesGérant2=trim($this->CotisationssocialesGérant2);
		if (isset($this->Fraisbancaires)) $this->Fraisbancaires=trim($this->Fraisbancaires);
		if (isset($this->Rémunérationssalariés)) $this->Rémunérationssalariés=trim($this->Rémunérationssalariés);
		if (isset($this->Cotisationssocialessalariés)) $this->Cotisationssocialessalariés=trim($this->Cotisationssocialessalariés);
		if (isset($this->Agiosetintérêtspayés)) $this->Agiosetintérêtspayés=trim($this->Agiosetintérêtspayés);
		if (isset($this->RéseauxdentrepriseBNILinkedInViadeo)) $this->RéseauxdentrepriseBNILinkedInViadeo=trim($this->RéseauxdentrepriseBNILinkedInViadeo);
		if (isset($this->TVA)) $this->TVA=trim($this->TVA);
		if (isset($this->Formation)) $this->Formation=trim($this->Formation);
		if (isset($this->test)) $this->test=trim($this->test);
		if (isset($this->CA)) $this->CA=trim($this->CA);
		if (isset($this->achat)) $this->achat=trim($this->achat);
		if (isset($this->type)) $this->type=trim($this->type);

        

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."tresorerie(";
		
		$sql.= "VentesPrestations,";
		$sql.= "VentesMatériels,";
		$sql.= "VentesLogiciels,";
		$sql.= "VentesPortsdemarchandises,";
		$sql.= "AchatPrestation,";
		$sql.= "AchatMatériels,";
		$sql.= "AchatLogiciels,";
		$sql.= "AchatPortdemarchandises,";
		$sql.= "Emprunts,";
		$sql.= "ElectricitéEauGaz,";
		$sql.= "Fournituresadministratives,";
		$sql.= "EquipementsMobilierInformatique,";
		$sql.= "Locations,";
		$sql.= "AssurancesRCP,";
		$sql.= "AssurancesMutuellePrevoyanceRetraite,";
		$sql.= "Maintenancematérielle,";
		$sql.= "Maintenancelogicielle,";
		$sql.= "Entretienlocauxmatérielvéhicule,";
		$sql.= "FraisdecréationEntreprise,";
		$sql.= "HonorairescomptablesCLG,";
		$sql.= "AutresHonorairesCLS,";
		$sql.= "Fraisdacteetdecontentieux,";
		$sql.= "Affranchissements,";
		$sql.= "Téléphonefixe,";
		$sql.= "Téléphonemobile,";
		$sql.= "ADSLFax,";
		$sql.= "CopieurRicoh,";
		$sql.= "Nomdedomaine,";
		$sql.= "HebergementLinux,";
		$sql.= "HebergementWindows,";
		$sql.= "Publicité,";
		$sql.= "Fraisdetransport,";
		$sql.= "Pagesjaunes,";
		$sql.= "Voyagesetdéplacements,";
		$sql.= "IndemnitésKilométriques,";
		$sql.= "Impôtsettaxes,";
		$sql.= "RémunérationGérant1,";
		$sql.= "RémunérationGérant2,";
		$sql.= "CotisationssocialesGérant1,";
		$sql.= "CotisationssocialesGérant2,";
		$sql.= "Fraisbancaires,";
		$sql.= "Rémunérationssalariés,";
		$sql.= "Cotisationssocialessalariés,";
		$sql.= "Agiosetintérêtspayés,";
		$sql.= "RéseauxdentrepriseBNILinkedInViadeo,";
		$sql.= "TVA,";
		$sql.= "Formation,";
		$sql.= "test,";
		$sql.= "CA,";
		$sql.= "achat,";
		$sql.= "date,";
		$sql.= "type";

		
        $sql.= ") VALUES (";
        
		$sql.= " ".(! isset($this->VentesPrestations)?'NULL':"'".$this->VentesPrestations."'").",";
		$sql.= " ".(! isset($this->VentesMatériels)?'NULL':"'".$this->VentesMatériels."'").",";
		$sql.= " ".(! isset($this->VentesLogiciels)?'NULL':"'".$this->VentesLogiciels."'").",";
		$sql.= " ".(! isset($this->VentesPortsdemarchandises)?'NULL':"'".$this->VentesPortsdemarchandises."'").",";
		$sql.= " ".(! isset($this->AchatPrestation)?'NULL':"'".$this->AchatPrestation."'").",";
		$sql.= " ".(! isset($this->AchatMatériels)?'NULL':"'".$this->AchatMatériels."'").",";
		$sql.= " ".(! isset($this->AchatLogiciels)?'NULL':"'".$this->AchatLogiciels."'").",";
		$sql.= " ".(! isset($this->AchatPortdemarchandises)?'NULL':"'".$this->AchatPortdemarchandises."'").",";
		$sql.= " ".(! isset($this->Emprunts)?'NULL':"'".$this->Emprunts."'").",";
		$sql.= " ".(! isset($this->ElectricitéEauGaz)?'NULL':"'".$this->ElectricitéEauGaz."'").",";
		$sql.= " ".(! isset($this->Fournituresadministratives)?'NULL':"'".$this->Fournituresadministratives."'").",";
		$sql.= " ".(! isset($this->EquipementsMobilierInformatique)?'NULL':"'".$this->EquipementsMobilierInformatique."'").",";
		$sql.= " ".(! isset($this->Locations)?'NULL':"'".$this->Locations."'").",";
		$sql.= " ".(! isset($this->AssurancesRCP)?'NULL':"'".$this->AssurancesRCP."'").",";
		$sql.= " ".(! isset($this->AssurancesMutuellePrevoyanceRetraite)?'NULL':"'".$this->AssurancesMutuellePrevoyanceRetraite."'").",";
		$sql.= " ".(! isset($this->Maintenancematérielle)?'NULL':"'".$this->Maintenancematérielle."'").",";
		$sql.= " ".(! isset($this->Maintenancelogicielle)?'NULL':"'".$this->Maintenancelogicielle."'").",";
		$sql.= " ".(! isset($this->Entretienlocauxmatérielvéhicule)?'NULL':"'".$this->Entretienlocauxmatérielvéhicule."'").",";
		$sql.= " ".(! isset($this->FraisdecréationEntreprise)?'NULL':"'".$this->FraisdecréationEntreprise."'").",";
		$sql.= " ".(! isset($this->HonorairescomptablesCLG)?'NULL':"'".$this->HonorairescomptablesCLG."'").",";
		$sql.= " ".(! isset($this->AutresHonorairesCLS)?'NULL':"'".$this->AutresHonorairesCLS."'").",";
		$sql.= " ".(! isset($this->Fraisdacteetdecontentieux)?'NULL':"'".$this->Fraisdacteetdecontentieux."'").",";
		$sql.= " ".(! isset($this->Affranchissements)?'NULL':"'".$this->Affranchissements."'").",";
		$sql.= " ".(! isset($this->Téléphonefixe)?'NULL':"'".$this->Téléphonefixe."'").",";
		$sql.= " ".(! isset($this->Téléphonemobile)?'NULL':"'".$this->Téléphonemobile."'").",";
		$sql.= " ".(! isset($this->ADSLFax)?'NULL':"'".$this->ADSLFax."'").",";
		$sql.= " ".(! isset($this->CopieurRicoh)?'NULL':"'".$this->CopieurRicoh."'").",";
		$sql.= " ".(! isset($this->Nomdedomaine)?'NULL':"'".$this->Nomdedomaine."'").",";
		$sql.= " ".(! isset($this->HebergementLinux)?'NULL':"'".$this->HebergementLinux."'").",";
		$sql.= " ".(! isset($this->HebergementWindows)?'NULL':"'".$this->HebergementWindows."'").",";
		$sql.= " ".(! isset($this->Publicité)?'NULL':"'".$this->Publicité."'").",";
		$sql.= " ".(! isset($this->Fraisdetransport)?'NULL':"'".$this->Fraisdetransport."'").",";
		$sql.= " ".(! isset($this->Pagesjaunes)?'NULL':"'".$this->Pagesjaunes."'").",";
		$sql.= " ".(! isset($this->Voyagesetdéplacements)?'NULL':"'".$this->Voyagesetdéplacements."'").",";
		$sql.= " ".(! isset($this->IndemnitésKilométriques)?'NULL':"'".$this->IndemnitésKilométriques."'").",";
		$sql.= " ".(! isset($this->Impôtsettaxes)?'NULL':"'".$this->Impôtsettaxes."'").",";
		$sql.= " ".(! isset($this->RémunérationGérant1)?'NULL':"'".$this->RémunérationGérant1."'").",";
		$sql.= " ".(! isset($this->RémunérationGérant2)?'NULL':"'".$this->RémunérationGérant2."'").",";
		$sql.= " ".(! isset($this->CotisationssocialesGérant1)?'NULL':"'".$this->CotisationssocialesGérant1."'").",";
		$sql.= " ".(! isset($this->CotisationssocialesGérant2)?'NULL':"'".$this->CotisationssocialesGérant2."'").",";
		$sql.= " ".(! isset($this->Fraisbancaires)?'NULL':"'".$this->Fraisbancaires."'").",";
		$sql.= " ".(! isset($this->Rémunérationssalariés)?'NULL':"'".$this->Rémunérationssalariés."'").",";
		$sql.= " ".(! isset($this->Cotisationssocialessalariés)?'NULL':"'".$this->Cotisationssocialessalariés."'").",";
		$sql.= " ".(! isset($this->Agiosetintérêtspayés)?'NULL':"'".$this->Agiosetintérêtspayés."'").",";
		$sql.= " ".(! isset($this->RéseauxdentrepriseBNILinkedInViadeo)?'NULL':"'".$this->RéseauxdentrepriseBNILinkedInViadeo."'").",";
		$sql.= " ".(! isset($this->TVA)?'NULL':"'".$this->TVA."'").",";
		$sql.= " ".(! isset($this->Formation)?'NULL':"'".$this->Formation."'").",";
		$sql.= " ".(! isset($this->test)?'NULL':"'".$this->test."'").",";
		$sql.= " ".(! isset($this->CA)?'NULL':"'".$this->CA."'").",";
		$sql.= " ".(! isset($this->achat)?'NULL':"'".$this->achat."'").",";
		$sql.= " ".(! isset($this->date) || dol_strlen($this->date)==0?'NULL':$this->db->idate($this->date)).",";
		$sql.= " ".(! isset($this->type)?'NULL':"'".$this->db->escape($this->type)."'")."";

        
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."tresorerie");

			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
			}
        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
            return $this->id;
		}
    }


    /**
     *  Load object in memory from the database
     *
     *  @param	int		$id    Id object
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch($id)
    {
    	global $langs;
        $sql = "SELECT";
		$sql.= " t.rowid,";
		
		$sql.= " t.VentesPrestations,";
		$sql.= " t.VentesMatériels,";
		$sql.= " t.VentesLogiciels,";
		$sql.= " t.VentesPortsdemarchandises,";
		$sql.= " t.AchatPrestation,";
		$sql.= " t.AchatMatériels,";
		$sql.= " t.AchatLogiciels,";
		$sql.= " t.AchatPortdemarchandises,";
		$sql.= " t.Emprunts,";
		$sql.= " t.ElectricitéEauGaz,";
		$sql.= " t.Fournituresadministratives,";
		$sql.= " t.EquipementsMobilierInformatique,";
		$sql.= " t.Locations,";
		$sql.= " t.AssurancesRCP,";
		$sql.= " t.AssurancesMutuellePrevoyanceRetraite,";
		$sql.= " t.Maintenancematérielle,";
		$sql.= " t.Maintenancelogicielle,";
		$sql.= " t.Entretienlocauxmatérielvéhicule,";
		$sql.= " t.FraisdecréationEntreprise,";
		$sql.= " t.HonorairescomptablesCLG,";
		$sql.= " t.AutresHonorairesCLS,";
		$sql.= " t.Fraisdacteetdecontentieux,";
		$sql.= " t.Affranchissements,";
		$sql.= " t.Téléphonefixe,";
		$sql.= " t.Téléphonemobile,";
		$sql.= " t.ADSLFax,";
		$sql.= " t.CopieurRicoh,";
		$sql.= " t.Nomdedomaine,";
		$sql.= " t.HebergementLinux,";
		$sql.= " t.HebergementWindows,";
		$sql.= " t.Publicité,";
		$sql.= " t.Fraisdetransport,";
		$sql.= " t.Pagesjaunes,";
		$sql.= " t.Voyagesetdéplacements,";
		$sql.= " t.IndemnitésKilométriques,";
		$sql.= " t.Impôtsettaxes,";
		$sql.= " t.RémunérationGérant1,";
		$sql.= " t.RémunérationGérant2,";
		$sql.= " t.CotisationssocialesGérant1,";
		$sql.= " t.CotisationssocialesGérant2,";
		$sql.= " t.Fraisbancaires,";
		$sql.= " t.Rémunérationssalariés,";
		$sql.= " t.Cotisationssocialessalariés,";
		$sql.= " t.Agiosetintérêtspayés,";
		$sql.= " t.RéseauxdentrepriseBNILinkedInViadeo,";
		$sql.= " t.TVA,";
		$sql.= " t.Formation,";
		$sql.= " t.test,";
		$sql.= " t.CA,";
		$sql.= " t.achat,";
		$sql.= " t.date,";
		$sql.= " t.type";

		
        $sql.= " FROM ".MAIN_DB_PREFIX."tresorerie as t";
        $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                
				$this->VentesPrestations = $obj->VentesPrestations;
				$this->VentesMatériels = $obj->VentesMatériels;
				$this->VentesLogiciels = $obj->VentesLogiciels;
				$this->VentesPortsdemarchandises = $obj->VentesPortsdemarchandises;
				$this->AchatPrestation = $obj->AchatPrestation;
				$this->AchatMatériels = $obj->AchatMatériels;
				$this->AchatLogiciels = $obj->AchatLogiciels;
				$this->AchatPortdemarchandises = $obj->AchatPortdemarchandises;
				$this->Emprunts = $obj->Emprunts;
				$this->ElectricitéEauGaz = $obj->ElectricitéEauGaz;
				$this->Fournituresadministratives = $obj->Fournituresadministratives;
				$this->EquipementsMobilierInformatique = $obj->EquipementsMobilierInformatique;
				$this->Locations = $obj->Locations;
				$this->AssurancesRCP = $obj->AssurancesRCP;
				$this->AssurancesMutuellePrevoyanceRetraite = $obj->AssurancesMutuellePrevoyanceRetraite;
				$this->Maintenancematérielle = $obj->Maintenancematérielle;
				$this->Maintenancelogicielle = $obj->Maintenancelogicielle;
				$this->Entretienlocauxmatérielvéhicule = $obj->Entretienlocauxmatérielvéhicule;
				$this->FraisdecréationEntreprise = $obj->FraisdecréationEntreprise;
				$this->HonorairescomptablesCLG = $obj->HonorairescomptablesCLG;
				$this->AutresHonorairesCLS = $obj->AutresHonorairesCLS;
				$this->Fraisdacteetdecontentieux = $obj->Fraisdacteetdecontentieux;
				$this->Affranchissements = $obj->Affranchissements;
				$this->Téléphonefixe = $obj->Téléphonefixe;
				$this->Téléphonemobile = $obj->Téléphonemobile;
				$this->ADSLFax = $obj->ADSLFax;
				$this->CopieurRicoh = $obj->CopieurRicoh;
				$this->Nomdedomaine = $obj->Nomdedomaine;
				$this->HebergementLinux = $obj->HebergementLinux;
				$this->HebergementWindows = $obj->HebergementWindows;
				$this->Publicité = $obj->Publicité;
				$this->Fraisdetransport = $obj->Fraisdetransport;
				$this->Pagesjaunes = $obj->Pagesjaunes;
				$this->Voyagesetdéplacements = $obj->Voyagesetdéplacements;
				$this->IndemnitésKilométriques = $obj->IndemnitésKilométriques;
				$this->Impôtsettaxes = $obj->Impôtsettaxes;
				$this->RémunérationGérant1 = $obj->RémunérationGérant1;
				$this->RémunérationGérant2 = $obj->RémunérationGérant2;
				$this->CotisationssocialesGérant1 = $obj->CotisationssocialesGérant1;
				$this->CotisationssocialesGérant2 = $obj->CotisationssocialesGérant2;
				$this->Fraisbancaires = $obj->Fraisbancaires;
				$this->Rémunérationssalariés = $obj->Rémunérationssalariés;
				$this->Cotisationssocialessalariés = $obj->Cotisationssocialessalariés;
				$this->Agiosetintérêtspayés = $obj->Agiosetintérêtspayés;
				$this->RéseauxdentrepriseBNILinkedInViadeo = $obj->RéseauxdentrepriseBNILinkedInViadeo;
				$this->TVA = $obj->TVA;
				$this->Formation = $obj->Formation;
				$this->test = $obj->test;
				$this->CA = $obj->CA;
				$this->achat = $obj->achat;
				$this->date = $this->db->jdate($obj->date);
				$this->type = $obj->type;

                
            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
            return -1;
        }
    }


    /**
     *  Update object into database
     *
     *  @param	User	$user        User that modifies
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    function update($user=0, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->VentesPrestations)) $this->VentesPrestations=trim($this->VentesPrestations);
		if (isset($this->VentesMatériels)) $this->VentesMatériels=trim($this->VentesMatériels);
		if (isset($this->VentesLogiciels)) $this->VentesLogiciels=trim($this->VentesLogiciels);
		if (isset($this->VentesPortsdemarchandises)) $this->VentesPortsdemarchandises=trim($this->VentesPortsdemarchandises);
		if (isset($this->AchatPrestation)) $this->AchatPrestation=trim($this->AchatPrestation);
		if (isset($this->AchatMatériels)) $this->AchatMatériels=trim($this->AchatMatériels);
		if (isset($this->AchatLogiciels)) $this->AchatLogiciels=trim($this->AchatLogiciels);
		if (isset($this->AchatPortdemarchandises)) $this->AchatPortdemarchandises=trim($this->AchatPortdemarchandises);
		if (isset($this->Emprunts)) $this->Emprunts=trim($this->Emprunts);
		if (isset($this->ElectricitéEauGaz)) $this->ElectricitéEauGaz=trim($this->ElectricitéEauGaz);
		if (isset($this->Fournituresadministratives)) $this->Fournituresadministratives=trim($this->Fournituresadministratives);
		if (isset($this->EquipementsMobilierInformatique)) $this->EquipementsMobilierInformatique=trim($this->EquipementsMobilierInformatique);
		if (isset($this->Locations)) $this->Locations=trim($this->Locations);
		if (isset($this->AssurancesRCP)) $this->AssurancesRCP=trim($this->AssurancesRCP);
		if (isset($this->AssurancesMutuellePrevoyanceRetraite)) $this->AssurancesMutuellePrevoyanceRetraite=trim($this->AssurancesMutuellePrevoyanceRetraite);
		if (isset($this->Maintenancematérielle)) $this->Maintenancematérielle=trim($this->Maintenancematérielle);
		if (isset($this->Maintenancelogicielle)) $this->Maintenancelogicielle=trim($this->Maintenancelogicielle);
		if (isset($this->Entretienlocauxmatérielvéhicule)) $this->Entretienlocauxmatérielvéhicule=trim($this->Entretienlocauxmatérielvéhicule);
		if (isset($this->FraisdecréationEntreprise)) $this->FraisdecréationEntreprise=trim($this->FraisdecréationEntreprise);
		if (isset($this->HonorairescomptablesCLG)) $this->HonorairescomptablesCLG=trim($this->HonorairescomptablesCLG);
		if (isset($this->AutresHonorairesCLS)) $this->AutresHonorairesCLS=trim($this->AutresHonorairesCLS);
		if (isset($this->Fraisdacteetdecontentieux)) $this->Fraisdacteetdecontentieux=trim($this->Fraisdacteetdecontentieux);
		if (isset($this->Affranchissements)) $this->Affranchissements=trim($this->Affranchissements);
		if (isset($this->Téléphonefixe)) $this->Téléphonefixe=trim($this->Téléphonefixe);
		if (isset($this->Téléphonemobile)) $this->Téléphonemobile=trim($this->Téléphonemobile);
		if (isset($this->ADSLFax)) $this->ADSLFax=trim($this->ADSLFax);
		if (isset($this->CopieurRicoh)) $this->CopieurRicoh=trim($this->CopieurRicoh);
		if (isset($this->Nomdedomaine)) $this->Nomdedomaine=trim($this->Nomdedomaine);
		if (isset($this->HebergementLinux)) $this->HebergementLinux=trim($this->HebergementLinux);
		if (isset($this->HebergementWindows)) $this->HebergementWindows=trim($this->HebergementWindows);
		if (isset($this->Publicité)) $this->Publicité=trim($this->Publicité);
		if (isset($this->Fraisdetransport)) $this->Fraisdetransport=trim($this->Fraisdetransport);
		if (isset($this->Pagesjaunes)) $this->Pagesjaunes=trim($this->Pagesjaunes);
		if (isset($this->Voyagesetdéplacements)) $this->Voyagesetdéplacements=trim($this->Voyagesetdéplacements);
		if (isset($this->IndemnitésKilométriques)) $this->IndemnitésKilométriques=trim($this->IndemnitésKilométriques);
		if (isset($this->Impôtsettaxes)) $this->Impôtsettaxes=trim($this->Impôtsettaxes);
		if (isset($this->RémunérationGérant1)) $this->RémunérationGérant1=trim($this->RémunérationGérant1);
		if (isset($this->RémunérationGérant2)) $this->RémunérationGérant2=trim($this->RémunérationGérant2);
		if (isset($this->CotisationssocialesGérant1)) $this->CotisationssocialesGérant1=trim($this->CotisationssocialesGérant1);
		if (isset($this->CotisationssocialesGérant2)) $this->CotisationssocialesGérant2=trim($this->CotisationssocialesGérant2);
		if (isset($this->Fraisbancaires)) $this->Fraisbancaires=trim($this->Fraisbancaires);
		if (isset($this->Rémunérationssalariés)) $this->Rémunérationssalariés=trim($this->Rémunérationssalariés);
		if (isset($this->Cotisationssocialessalariés)) $this->Cotisationssocialessalariés=trim($this->Cotisationssocialessalariés);
		if (isset($this->Agiosetintérêtspayés)) $this->Agiosetintérêtspayés=trim($this->Agiosetintérêtspayés);
		if (isset($this->RéseauxdentrepriseBNILinkedInViadeo)) $this->RéseauxdentrepriseBNILinkedInViadeo=trim($this->RéseauxdentrepriseBNILinkedInViadeo);
		if (isset($this->TVA)) $this->TVA=trim($this->TVA);
		if (isset($this->Formation)) $this->Formation=trim($this->Formation);
		if (isset($this->test)) $this->test=trim($this->test);
		if (isset($this->CA)) $this->CA=trim($this->CA);
		if (isset($this->achat)) $this->achat=trim($this->achat);
		if (isset($this->type)) $this->type=trim($this->type);

        

		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."tresorerie SET";
        
		$sql.= " VentesPrestations=".(isset($this->VentesPrestations)?$this->VentesPrestations:"null").",";
		$sql.= " VentesMatériels=".(isset($this->VentesMatériels)?$this->VentesMatériels:"null").",";
		$sql.= " VentesLogiciels=".(isset($this->VentesLogiciels)?$this->VentesLogiciels:"null").",";
		$sql.= " VentesPortsdemarchandises=".(isset($this->VentesPortsdemarchandises)?$this->VentesPortsdemarchandises:"null").",";
		$sql.= " AchatPrestation=".(isset($this->AchatPrestation)?$this->AchatPrestation:"null").",";
		$sql.= " AchatMatériels=".(isset($this->AchatMatériels)?$this->AchatMatériels:"null").",";
		$sql.= " AchatLogiciels=".(isset($this->AchatLogiciels)?$this->AchatLogiciels:"null").",";
		$sql.= " AchatPortdemarchandises=".(isset($this->AchatPortdemarchandises)?$this->AchatPortdemarchandises:"null").",";
		$sql.= " Emprunts=".(isset($this->Emprunts)?$this->Emprunts:"null").",";
		$sql.= " ElectricitéEauGaz=".(isset($this->ElectricitéEauGaz)?$this->ElectricitéEauGaz:"null").",";
		$sql.= " Fournituresadministratives=".(isset($this->Fournituresadministratives)?$this->Fournituresadministratives:"null").",";
		$sql.= " EquipementsMobilierInformatique=".(isset($this->EquipementsMobilierInformatique)?$this->EquipementsMobilierInformatique:"null").",";
		$sql.= " Locations=".(isset($this->Locations)?$this->Locations:"null").",";
		$sql.= " AssurancesRCP=".(isset($this->AssurancesRCP)?$this->AssurancesRCP:"null").",";
		$sql.= " AssurancesMutuellePrevoyanceRetraite=".(isset($this->AssurancesMutuellePrevoyanceRetraite)?$this->AssurancesMutuellePrevoyanceRetraite:"null").",";
		$sql.= " Maintenancematérielle=".(isset($this->Maintenancematérielle)?$this->Maintenancematérielle:"null").",";
		$sql.= " Maintenancelogicielle=".(isset($this->Maintenancelogicielle)?$this->Maintenancelogicielle:"null").",";
		$sql.= " Entretienlocauxmatérielvéhicule=".(isset($this->Entretienlocauxmatérielvéhicule)?$this->Entretienlocauxmatérielvéhicule:"null").",";
		$sql.= " FraisdecréationEntreprise=".(isset($this->FraisdecréationEntreprise)?$this->FraisdecréationEntreprise:"null").",";
		$sql.= " HonorairescomptablesCLG=".(isset($this->HonorairescomptablesCLG)?$this->HonorairescomptablesCLG:"null").",";
		$sql.= " AutresHonorairesCLS=".(isset($this->AutresHonorairesCLS)?$this->AutresHonorairesCLS:"null").",";
		$sql.= " Fraisdacteetdecontentieux=".(isset($this->Fraisdacteetdecontentieux)?$this->Fraisdacteetdecontentieux:"null").",";
		$sql.= " Affranchissements=".(isset($this->Affranchissements)?$this->Affranchissements:"null").",";
		$sql.= " Téléphonefixe=".(isset($this->Téléphonefixe)?$this->Téléphonefixe:"null").",";
		$sql.= " Téléphonemobile=".(isset($this->Téléphonemobile)?$this->Téléphonemobile:"null").",";
		$sql.= " ADSLFax=".(isset($this->ADSLFax)?$this->ADSLFax:"null").",";
		$sql.= " CopieurRicoh=".(isset($this->CopieurRicoh)?$this->CopieurRicoh:"null").",";
		$sql.= " Nomdedomaine=".(isset($this->Nomdedomaine)?$this->Nomdedomaine:"null").",";
		$sql.= " HebergementLinux=".(isset($this->HebergementLinux)?$this->HebergementLinux:"null").",";
		$sql.= " HebergementWindows=".(isset($this->HebergementWindows)?$this->HebergementWindows:"null").",";
		$sql.= " Publicité=".(isset($this->Publicité)?$this->Publicité:"null").",";
		$sql.= " Fraisdetransport=".(isset($this->Fraisdetransport)?$this->Fraisdetransport:"null").",";
		$sql.= " Pagesjaunes=".(isset($this->Pagesjaunes)?$this->Pagesjaunes:"null").",";
		$sql.= " Voyagesetdéplacements=".(isset($this->Voyagesetdéplacements)?$this->Voyagesetdéplacements:"null").",";
		$sql.= " IndemnitésKilométriques=".(isset($this->IndemnitésKilométriques)?$this->IndemnitésKilométriques:"null").",";
		$sql.= " Impôtsettaxes=".(isset($this->Impôtsettaxes)?$this->Impôtsettaxes:"null").",";
		$sql.= " RémunérationGérant1=".(isset($this->RémunérationGérant1)?$this->RémunérationGérant1:"null").",";
		$sql.= " RémunérationGérant2=".(isset($this->RémunérationGérant2)?$this->RémunérationGérant2:"null").",";
		$sql.= " CotisationssocialesGérant1=".(isset($this->CotisationssocialesGérant1)?$this->CotisationssocialesGérant1:"null").",";
		$sql.= " CotisationssocialesGérant2=".(isset($this->CotisationssocialesGérant2)?$this->CotisationssocialesGérant2:"null").",";
		$sql.= " Fraisbancaires=".(isset($this->Fraisbancaires)?$this->Fraisbancaires:"null").",";
		$sql.= " Rémunérationssalariés=".(isset($this->Rémunérationssalariés)?$this->Rémunérationssalariés:"null").",";
		$sql.= " Cotisationssocialessalariés=".(isset($this->Cotisationssocialessalariés)?$this->Cotisationssocialessalariés:"null").",";
		$sql.= " Agiosetintérêtspayés=".(isset($this->Agiosetintérêtspayés)?$this->Agiosetintérêtspayés:"null").",";
		$sql.= " RéseauxdentrepriseBNILinkedInViadeo=".(isset($this->RéseauxdentrepriseBNILinkedInViadeo)?$this->RéseauxdentrepriseBNILinkedInViadeo:"null").",";
		$sql.= " TVA=".(isset($this->TVA)?$this->TVA:"null").",";
		$sql.= " Formation=".(isset($this->Formation)?$this->Formation:"null").",";
		$sql.= " test=".(isset($this->test)?$this->test:"null").",";
		$sql.= " CA=".(isset($this->CA)?$this->CA:"null").",";
		$sql.= " achat=".(isset($this->achat)?$this->achat:"null").",";
		$sql.= " date=".(dol_strlen($this->date)!=0 ? "'".$this->db->idate($this->date)."'" : 'null').",";
		$sql.= " type=".(isset($this->type)?"'".$this->db->escape($this->type)."'":"null")."";

        
        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{
			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
	    	}
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
    }


 	/**
	 *  Delete object in database
	 *
     *	@param  User	$user        User that deletes
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		$this->db->begin();

		if (! $error)
		{
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
		        // want this action calls a trigger.

		        //// Call triggers
		        //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
		        //$interface=new Interfaces($this->db);
		        //$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
		        //if ($result < 0) { $error++; $this->errors=$interface->errors; }
		        //// End call triggers
			}
		}

		if (! $error)
		{
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."tresorerie";
    		$sql.= " WHERE rowid=".$this->id;

    		dol_syslog(get_class($this)."::delete sql=".$sql);
    		$resql = $this->db->query($sql);
        	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}



	/**
	 *	Load an object from its id and create a new one in database
	 *
	 *	@param	int		$fromid     Id of object to clone
	 * 	@return	int					New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Tresorerie($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		$object->statut=0;

		// Clear fields
		// ...

		// Create clone
		$result=$object->create($user);

		// Other options
		if ($result < 0)
		{
			$this->error=$object->error;
			$error++;
		}

		if (! $error)
		{


		}

		// End
		if (! $error)
		{
			$this->db->commit();
			return $object->id;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Initialise object with example values
	 *	Id must be 0 if object instance is a specimen
	 *
	 *	@return	void
	 */
	function initAsSpecimen()
	{
		$this->id=0;
		
		$this->VentesPrestations='';
		$this->VentesMatériels='';
		$this->VentesLogiciels='';
		$this->VentesPortsdemarchandises='';
		$this->AchatPrestation='';
		$this->AchatMatériels='';
		$this->AchatLogiciels='';
		$this->AchatPortdemarchandises='';
		$this->Emprunts='';
		$this->ElectricitéEauGaz='';
		$this->Fournituresadministratives='';
		$this->EquipementsMobilierInformatique='';
		$this->Locations='';
		$this->AssurancesRCP='';
		$this->AssurancesMutuellePrevoyanceRetraite='';
		$this->Maintenancematérielle='';
		$this->Maintenancelogicielle='';
		$this->Entretienlocauxmatérielvéhicule='';
		$this->FraisdecréationEntreprise='';
		$this->HonorairescomptablesCLG='';
		$this->AutresHonorairesCLS='';
		$this->Fraisdacteetdecontentieux='';
		$this->Affranchissements='';
		$this->Téléphonefixe='';
		$this->Téléphonemobile='';
		$this->ADSLFax='';
		$this->CopieurRicoh='';
		$this->Nomdedomaine='';
		$this->HebergementLinux='';
		$this->HebergementWindows='';
		$this->Publicité='';
		$this->Fraisdetransport='';
		$this->Pagesjaunes='';
		$this->Voyagesetdéplacements='';
		$this->IndemnitésKilométriques='';
		$this->Impôtsettaxes='';
		$this->RémunérationGérant1='';
		$this->RémunérationGérant2='';
		$this->CotisationssocialesGérant1='';
		$this->CotisationssocialesGérant2='';
		$this->Fraisbancaires='';
		$this->Rémunérationssalariés='';
		$this->Cotisationssocialessalariés='';
		$this->Agiosetintérêtspayés='';
		$this->RéseauxdentrepriseBNILinkedInViadeo='';
		$this->TVA='';
		$this->Formation='';
		$this->test='';
		$this->CA='';
		$this->achat='';
		$this->date='';
		$this->type='';

		
	}

}
?>

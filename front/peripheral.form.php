<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2006 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------


$NEEDED_ITEMS=array("peripheral","infocom","contract","user","link","networking","document","tracking","reservation","computer","enterprise");

define('GLPI_ROOT', '..');
include (GLPI_ROOT . "/inc/includes.php");

if(isset($_GET)) $tab = $_GET;
if(empty($tab) && isset($_POST)) $tab = $_POST;
if(empty($tab["ID"])) $tab["ID"] = "";
if(!isset($tab["withtemplate"])) $tab["withtemplate"] = "";

$peripheral=new Peripheral();

if (isset($_POST["add"]))
{
	checkRight("peripheral","w");

	$newID=$peripheral->add($_POST);
	logEvent($newID, "peripherals", 4, "inventory", $_SESSION["glpiname"]." ".$LANG["log"][20]." ".$_POST["name"].".");
	glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($tab["delete"]))
{
	checkRight("peripheral","w");

	if (!empty($tab["withtemplate"]))
		$peripheral->delete($tab,1);
	else $peripheral->delete($tab);

	logEvent($tab["ID"], "peripherals", 4, "inventory", $_SESSION["glpiname"]." ".$LANG["log"][22]);
	if(!empty($tab["withtemplate"])) 
		glpi_header($CFG_GLPI["root_doc"]."/front/setup.templates.php");
	else 
		glpi_header($CFG_GLPI["root_doc"]."/front/peripheral.php");
}
else if (isset($_POST["restore"]))
{
	checkRight("peripheral","w");

	$peripheral->restore($_POST);
	logEvent($tab["ID"], "peripherals", 4, "inventory", $_SESSION["glpiname"]." ".$LANG["log"][23]);
	glpi_header($CFG_GLPI["root_doc"]."/front/peripheral.php");
}
else if (isset($tab["purge"]))
{
	checkRight("peripheral","w");

	$peripheral->delete($tab,1);
	logEvent($tab["ID"], "peripherals", 4, "inventory", $_SESSION["glpiname"]." ".$LANG["log"][24]);
	glpi_header($CFG_GLPI["root_doc"]."/front/peripheral.php");
}
else if (isset($_POST["update"]))
{
	checkRight("peripheral","w");

	$peripheral->update($_POST);
	logEvent($_POST["ID"], "peripherals", 4, "inventory", $_SESSION["glpiname"]." ".$LANG["log"][21]);
	glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($tab["unglobalize"]))
{
	checkRight("peripheral","w");

	unglobalizeDevice(PERIPHERAL_TYPE,$tab["ID"]);
	logEvent($tab["ID"], "peripherals", 4, "inventory", $_SESSION["glpiname"]." ".$LANG["log"][60]);
	glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($tab["disconnect"]))
{
	checkRight("peripheral","w");

	Disconnect($tab["ID"]);
	logEvent(0, "peripherals", 5, "inventory", $_SESSION["glpiname"]." ".$LANG["log"][27]);
	glpi_header($_SERVER['HTTP_REFERER']);
}
else if(isset($tab["connect"])&&isset($tab["item"])&&$tab["item"]>0)
{

	checkRight("peripheral","w");

	Connect($tab["sID"],$tab["item"],PERIPHERAL_TYPE);
	logEvent($tab["sID"], "peripherals", 4, "inventory", $_SESSION["glpiname"]." ".$LANG["log"][26]);
	glpi_header($CFG_GLPI["root_doc"]."/front/peripheral.form.php?ID=".$tab["sID"]);
}
else
{
	checkRight("peripheral","r");

	if (!isset($_SESSION['glpi_onglet'])) $_SESSION['glpi_onglet']=1;
	if (isset($_GET['onglet'])) {
		$_SESSION['glpi_onglet']=$_GET['onglet'];
		//		glpi_header($_SERVER['HTTP_REFERER']);
	}


	commonHeader($LANG["title"][7],$_SERVER['PHP_SELF']);

	if (!empty($tab["withtemplate"])) {

		if ($peripheral->showForm($_SERVER['PHP_SELF'],$tab["ID"], $tab["withtemplate"])){
			if ($tab["ID"]>0){

				switch($_SESSION['glpi_onglet']){
					case 4 :
						showInfocomForm($CFG_GLPI["root_doc"]."/front/infocom.form.php",PERIPHERAL_TYPE,$tab["ID"],1,$tab["withtemplate"]);
						showContractAssociated(PERIPHERAL_TYPE,$tab["ID"],$tab["withtemplate"]);
						break;
					case 5 :
						showDocumentAssociated(PERIPHERAL_TYPE,$tab["ID"],$tab["withtemplate"]);
						break;

					default :
						if (!display_plugin_action(PERIPHERAL_TYPE,$tab["ID"],$_SESSION['glpi_onglet'],$tab["withtemplate"])){
							if ($tab["withtemplate"]!=2)	showPortsAdd($tab["ID"],PERIPHERAL_TYPE);
							showPorts($tab["ID"], PERIPHERAL_TYPE,$tab["withtemplate"]);
						}

						break;
				}
			}
		}

	} else {

		if (haveRight("delete_ticket","1")&&isset($_POST["delete_inter"])&&!empty($_POST["todel"])){
			$job=new Job();
			foreach ($_POST["todel"] as $key => $val){
				if ($val==1) {
					$job->delete(array("ID"=>$key));
				}
			}
		}

		if ($peripheral->showForm($_SERVER['PHP_SELF'],$tab["ID"])){
			switch($_SESSION['glpi_onglet']){
				case -1:
					showConnect($_SERVER['PHP_SELF'],$tab["ID"],PERIPHERAL_TYPE);
					showPortsAdd($tab["ID"],PERIPHERAL_TYPE);
					showPorts($tab["ID"], PERIPHERAL_TYPE,$tab["withtemplate"]);
					showInfocomForm($CFG_GLPI["root_doc"]."/front/infocom.form.php",PERIPHERAL_TYPE,$tab["ID"]);
					showContractAssociated(PERIPHERAL_TYPE,$tab["ID"]);
					showDocumentAssociated(PERIPHERAL_TYPE,$tab["ID"]);
					showJobListForItem($_SESSION["glpiname"],PERIPHERAL_TYPE,$tab["ID"]);
					showOldJobListForItem($_SESSION["glpiname"],PERIPHERAL_TYPE,$tab["ID"]);
					showLinkOnDevice(PERIPHERAL_TYPE,$tab["ID"]);
					display_plugin_action(PERIPHERAL_TYPE,$tab["ID"],$_SESSION['glpi_onglet'],$tab["withtemplate"]);
					break;
				case 4 :
					showInfocomForm($CFG_GLPI["root_doc"]."/front/infocom.form.php",PERIPHERAL_TYPE,$tab["ID"]);
					showContractAssociated(PERIPHERAL_TYPE,$tab["ID"]);
					break;
				case 5 :
					showDocumentAssociated(PERIPHERAL_TYPE,$tab["ID"]);
					break;
				case 6 :
					showJobListForItem($_SESSION["glpiname"],PERIPHERAL_TYPE,$tab["ID"]);
					showOldJobListForItem($_SESSION["glpiname"],PERIPHERAL_TYPE,$tab["ID"]);
					break;
				case 7 :
					showLinkOnDevice(PERIPHERAL_TYPE,$tab["ID"]);
					break;	
				case 10 :
					showNotesForm($_SERVER['PHP_SELF'],PERIPHERAL_TYPE,$tab["ID"]);
					break;	
				case 11 :
					showDeviceReservations($_SERVER['PHP_SELF'],PERIPHERAL_TYPE,$tab["ID"]);
					break;
				case 12 :
					showHistory(PERIPHERAL_TYPE,$tab["ID"]);
					break;		
				default :
					if (!display_plugin_action(PERIPHERAL_TYPE,$tab["ID"],$_SESSION['glpi_onglet'],$tab["withtemplate"])){
						showConnect($_SERVER['PHP_SELF'],$tab["ID"],PERIPHERAL_TYPE);
						showPortsAdd($tab["ID"],PERIPHERAL_TYPE);
						showPorts($tab["ID"], PERIPHERAL_TYPE,$tab["withtemplate"]);
					}
					break;
			}





		}
	}
	commonFooter();
}


?>

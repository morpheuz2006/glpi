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
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}

// FUNCTIONS State


function titleState(){
	global  $LANG,$CFG_GLPI;

	$buttons["state.php?synthese=no"]=$LANG["state"][1];
	$buttons["state.php?synthese=yes"]=$LANG["state"][11];

	displayTitle($CFG_GLPI["root_doc"]."/pics/status.png",$LANG["state"][1],"",$buttons);

}


function showStateSummary($target){
	global $DB,$LANG,$CFG_GLPI,$LINK_ID_TABLE;


	$state_type=$CFG_GLPI["state_type"];

	$states=array();
	foreach ($state_type as $key=>$type){
		if (!haveTypeRight($type,"r")) {
			unset($state_type[$key]);
		} else {
			$query= "SELECT state, COUNT(ID) AS CPT FROM ".$LINK_ID_TABLE[$type]." ".getEntitiesRestrictRequest("WHERE",$LINK_ID_TABLE[$type])."GROUP BY state";
			if ($result = $DB->query($query)) {
				if ($DB->numrows($result)>0){
					while ($data=$DB->fetch_array($result)){
						$states[$data["state"]][$type]=$data["CPT"];
					}
				}
			}
		}
	}

	if (count($states)){
		// Produce headline
		echo "<div align='center'><table  class='tab_cadrehov'><tr>";
	
		// Type			
		echo "<th>";
		echo $LANG["state"][0]."</th>";
	
		$ci=new CommonItem;
		foreach ($state_type as $type){
			$ci->setType($type);
			echo "<th>".$ci->getType()."</th>";
			$total[$type]=0;
		}
		echo "<th>".$LANG["common"][33]."</th>";
		echo "</tr>";
		$query="SELECT * FROM glpi_dropdown_state ORDER BY name";
		$result = $DB->query($query);
		
		while ($data=$DB->fetch_array($result)){
			$tot=0;
			echo "<tr class='tab_bg_2'><td align='center'><strong>".$data["name"]."</strong></td>";
	
			foreach ($state_type as $type){
				echo "<td align='center'>";
	
				if (isset($states[$data["ID"]][$type])) {
					echo $states[$data["ID"]][$type];
					$total[$type]+=$states[$data["ID"]][$type];
					$tot+=$states[$data["ID"]][$type];
				}
				else echo "&nbsp;";
				echo "</td>";
			}
			echo "<td align='center'><strong>$tot</strong></td>";
			echo "</tr>";
		}
		echo "<tr class='tab_bg_2'><td align='center'><strong>".$LANG["common"][33]."</strong></td>";
		$tot=0;
		foreach ($state_type as $type){
			echo "<td align='center'><strong>".$total[$type]."</strong></td>";
			$tot+=$total[$type];
		}
		echo "<td align='center'><strong>".$tot."</strong></td>";
		echo "</tr>";
		echo "</table></div>";

	}else {
		echo "<div align='center'><b>".$LANG["state"][7]."</b></div>";
	}


}

?>

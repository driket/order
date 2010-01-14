<?php
/*
 * @version $Id: HEADER 1 2009-09-21 14:58 Tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

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
 
// ----------------------------------------------------------------------
// Original Author of file: NOUH Walid & Benjamin Fontan
// Purpose of file: plugin order v1.1.0 - GLPI 0.72
// ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderBudget extends CommonDBTM {

   static function getTypeName() {
      global $LANG;

      return $LANG['financial'][87];
   }
   
   function canCreate() {
      return plugin_order_haveRight('budget', 'w');
   }

   function canView() {
      return plugin_order_haveRight('budget', 'r');
   }
   
   function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['financial'][87];

		$tab[1]['table'] = $this->getTable();
		$tab[1]['field'] = 'name';
		$tab[1]['linkfield'] = 'name';
		$tab[1]['name'] = $LANG['common'][16];
		$tab[1]['datatype'] = 'itemlink';

		$tab[2]['table'] = $this->getTable();
		$tab[2]['field'] = 'comment';
		$tab[2]['linkfield'] = 'comment';
		$tab[2]['name'] = $LANG['common'][25];
      $tab[2]['datatype']='text';
      
		$tab[3]['table'] = $this->getTable();
		$tab[3]['field'] = 'start_date';
		$tab[3]['linkfield'] = 'start_date';
		$tab[3]['name'] = $LANG['search'][8];
      $tab[3]['datatype']='date';

		$tab[4]['table'] = $this->getTable();
		$tab[4]['field'] = 'end_date';
		$tab[4]['linkfield'] = 'end_date';
		$tab[4]['name'] = $LANG['search'][9];
      $tab[4]['datatype']='date';

		$tab[5]['table'] = $this->getTable();
		$tab[5]['field'] = 'value';
		$tab[5]['linkfield'] = 'value';
		$tab[5]['name'] = $LANG['financial'][21];
		$tab[5]['datatype'] = 'number';

		$tab[30]['table']=$this->getTable();
      $tab[30]['field']='id';
      $tab[30]['linkfield']='';
      $tab[30]['name']=$LANG['common'][2];
		
		return $tab;
   }
	/*define header form */
	function defineTabs($ID, $withtemplate) {
		global $LANG;
		
		/* principal */
		$ong[1] = $LANG['title'][26];

		return $ong;
	}

	function showForm($target, $ID, $withtemplate = '') {
		global $CFG_GLPI, $LANG, $DB;

		if (!plugin_order_haveRight("budget","r")) return false;
		
		if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
         $this->getEmpty();
      }

      $canedit=$this->can($ID,'w');
		
      $this->showTabs($ID, $withtemplate);
      $this->showFormHeader($target,$ID,$withtemplate,1);

      echo "<tr class='tab_bg_2'><td>" . $LANG['common'][16] . ": </td>";
      echo "<td>";
      if ($canedit)
         autocompletionTextField($this,"name");
      else
         echo $this->fields["name"];	
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['financial'][87]." GLPI" . ": </td>";
      echo "<td>";
      if ($canedit)
         Dropdown::show('Budget', array('name' => "budgets_id",'value' => $this->fields["budgets_id"], 'entity' => $this->fields["entities_id"]));
      else
         echo Dropdown::getDropdownName("glpi_budgets",$this->fields["budgets_id"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['search'][8] . ": </td>";
      echo "<td>";
      if ($canedit)
         showDateFormItem("start_date",$this->fields["start_date"]);
      else
         echo convDate($this->fields["start_date"]);	
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['search'][9] . ": </td>";
      echo "<td>";
      if ($canedit)
         showDateFormItem("end_date",$this->fields["end_date"]);
      else
         echo convDate($this->fields["end_date"]);	
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['financial'][21] . ": </td>";
      echo "<td>";
      if ($canedit)
         echo "<input type='text' name='value' value=\"".formatNumber($this->fields["value"],true)."\" size='20'>";
      else
         echo $this->fields["value"];	
      echo "</td></tr>";

      if ($ID > 0) {
         $query = "SELECT SUM(`price_discounted`) AS total_price FROM `glpi_plugin_order_orders`, `glpi_plugin_order_orders_items` " .
               "WHERE `budgets_id` = '".$this->fields["budgets_id"]."' AND `glpi_plugin_order_orders_items`.`plugin_order_orders_id` = `glpi_plugin_order_orders`.`id` " .
               "GROUP BY `glpi_plugin_order_orders`.`budgets_id`";
         $result = $DB->query($query);

         echo "<tr class='tab_bg_2'><td>" . $LANG['plugin_order']['budget'][2] . ": </td>";
         echo "<td>";
         if ($DB->numrows($result))
            echo formatNumber($DB->result($result,0,0),false,2);
         else
            echo "0";
         echo "</td></tr>";
      }

      echo "<tr class='tab_bg_2'><td>" . $LANG['common'][25] . ": </td>";
      
      echo "<td colspan='3'>";
      if ($canedit)
         echo "<textarea cols='50' rows='4' name='comment' >" . $this->fields["comment"] . "</textarea>";
      else
         echo $this->fields["comment"];
      echo "</td></tr>";

      $this->showFormButtons($ID,$withtemplate,1);
      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";

		return true;
      
	}
	
	function getAllOrdersByBudget($budgets_id){
      global $DB,$LANG,$CFG_GLPI;
      
      //$this->getFromDB($budgets_id);
      $query = "SELECT * 
               FROM `glpi_plugin_order_orders` 
               WHERE `budgets_id` = '".$budgets_id."' 
               ORDER BY `entities_id`, `name` ";
      $result = $DB->query($query);

      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='5'>".$LANG['plugin_order']['budget'][1]."</th></tr>";
      echo "<tr>"; 
      echo "<th>".$LANG['common'][16]."</th>";
      echo "<th>".$LANG['entity'][0]."</th>";
      echo "</tr>";

      while ($data = $DB->fetch_array($result))
      {
         echo "<tr class='tab_bg_1' align='center'>"; 
         echo "<td>";

         $link=getItemTypeFormURL('PluginOrderOrder');
         if (plugin_order_haveRight("order","r"))
            echo "<a href=\"".$link."?id=".$data["id"]."\">".$data["name"]."</a>";
         else
            echo $data["name"];	
         echo "</td>";

         echo "<td>";
         //echo Dropdown::getDropdownName("glpi_entities",$this->fields["entities_id"]);
         echo "</td>";

         echo "</tr>"; 
      }
      
      echo "</table></div>";
   }
}

?>
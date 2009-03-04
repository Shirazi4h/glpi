<?php
/*
 * @version $Id$
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
 */

/**
 * Complete Dropdown system using ajax to get datas
 *
 * @param $use_ajax Use ajax search system (if not display a standard dropdown)
 * @param $relativeurl Relative URL to the root directory of GLPI
 * @param $params Parameters to send to ajax URL
 * @param $default Default datas t print in case of $use_ajax
 * @param $rand Random parameter used
 **/
function ajaxDropdown($use_ajax,$relativeurl,$params=array(),$default="&nbsp;",$rand=0){
	global $CFG_GLPI,$DB,$LANG,$LINK_ID_TABLE;

	$initparams=$params;	
	if ($rand==0){
		$rand=mt_rand();
	}

	if ($use_ajax){
		ajaxDisplaySearchTextForDropdown($rand);
		ajaxUpdateItemOnInputTextEvent("search_$rand","results_$rand",$CFG_GLPI["root_doc"].$relativeurl,$params);
	}
	echo "<span id='results_$rand'>\n";
		if (!$use_ajax){
			// Save post datas if exists
			$oldpost=array();
			if (isset($_POST)&&count($_POST)){
				$oldpost=$_POST;
			}
			$_POST=$params;
			$_POST["searchText"]=$CFG_GLPI["ajax_wildcard"];
			include (GLPI_ROOT.$relativeurl);
			// Restore $_POST datas
			if (count($oldpost)){
				$_POST=$oldpost;
			}
		} else {
			echo $default;
		}
	echo "</span>\n";
	//print_r($params);
	echo "<script type='text/javascript' >\n";
	echo "function update_results_$rand(){";
	if ($use_ajax){
		ajaxUpdateItemJsCode("results_$rand",$CFG_GLPI['root_doc'].$relativeurl,$initparams,true,"search_$rand");
	} else {
//		if (isset($params['value'])){
//			unset($params['value']);
//		}
		$initparams["searchText"]=$CFG_GLPI["ajax_wildcard"];
		ajaxUpdateItemJsCode("results_$rand",$CFG_GLPI['root_doc'].$relativeurl,$initparams,true,"");
	}
	echo "};";

	echo "</script>\n";
}

/**
 * Input text used as search system in ajax system
 *
 * @param $id ID of the ajax item
 * @param $size size of the input text field
 **/
function ajaxDisplaySearchTextForDropdown($id,$size=4){
	global $CFG_GLPI;
	echo "<input type='text' ondblclick=\"this.value='".$CFG_GLPI["ajax_wildcard"]."';\" id='search_$id' name='____data_$id' size='$size'>\n";

}

/**
 * Javascript code for update an item when a Input text item changed
 *
 * @param $toobserve id of the Input text to observe
 * @param $toupdate id of the item to update
 * @param $url Url to get datas to update the item
 * @param $parameters Parameters to send to ajax URL
 * @param $spinner NOT USED : always spinner - is a spinner displayed when loading ?
 **/
function ajaxUpdateItemOnInputTextEvent($toobserve,$toupdate,$url,$parameters=array(),$spinner=true){
	ajaxUpdateItemOnEvent($toobserve,$toupdate,$url,$parameters,array("dblclick","keyup"),$spinner);
}

/**
 * Javascript code for update an item when a select item changed
 *
 * @param $toobserve id of the select to observe
 * @param $toupdate id of the item to update
 * @param $url Url to get datas to update the item
 * @param $parameters Parameters to send to ajax URL
 * @param $spinner NOT USED : always spinner - is a spinner displayed when loading ?
 **/
function ajaxUpdateItemOnSelectEvent($toobserve,$toupdate,$url,$parameters=array(),$spinner=true){
	ajaxUpdateItemOnEvent($toobserve,$toupdate,$url,$parameters,array("change"),$spinner);
}


/**
 * Javascript code for update an item when another item changed
 *
 * @param $toobserve id of the select to observe
 * @param $toupdate id of the item to update
 * @param $url Url to get datas to update the item
 * @param $parameters Parameters to send to ajax URL
 * @param $events array of the observed events 
 * @param $spinner NOT USED : always spinner - is a spinner displayed when loading ?
 **/
function ajaxUpdateItemOnEvent($toobserve,$toupdate,$url,$parameters=array(),$events=array("change"),$spinner=true){
	global $CFG_GLPI;
	echo "<script type='text/javascript' >\n";
	ajaxUpdateItemOnEventJsCode($toobserve,$toupdate,$url,$parameters,$events,$spinner);
	echo "</script>\n";
}

/**
 * Javascript code for update an item when another item changed (Javascript code only)
 *
 * @param $toobserve id of the select to observe
 * @param $toupdate id of the item to update
 * @param $url Url to get datas to update the item
 * @param $parameters Parameters to send to ajax URL
 * @param $events array of the observed events 
 * @param $spinner NOT USED : always spinner - is a spinner displayed when loading ?
 **/
function ajaxUpdateItemOnEventJsCode($toobserve,$toupdate,$url,$parameters=array(),$events=array("change"),$spinner=true){
	global $CFG_GLPI;

	// No need on ready because already ready (check in header)
	//echo "Ext.onReady(function() {";
	foreach ($events as $event){
		echo "
			Ext.get('$toobserve').on(
				'$event',
				function() {";
					ajaxUpdateItemJsCode($toupdate,$url,$parameters,$spinner,$toobserve);
		echo "		});\n";
	}
	//echo "});";
}


/**
 * Javascript code for update an item
 *
 * @param $toupdate id of the item to update
 * @param $url Url to get datas to update the item
 * @param $parameters Parameters to send to ajax URL
 * @param $spinner NOT USED : always spinner - is a spinner displayed when loading ?
 * @param $toobserve id of another item used to get value in case of __VALUE__ used
 **/
function ajaxUpdateItem($toupdate,$url,$parameters=array(),$spinner=true,$toobserve=""){
	global $CFG_GLPI;
	echo "<script type='text/javascript' >\n";
	ajaxUpdateItemJsCode($toupdate,$url,$parameters,$spinner,$toobserve);
	echo "</script>\n";
}



/**
 * Javascript code for update an item (Javascript code only)
 *
 * @param $toupdate id of the item to update
 * @param $url Url to get datas to update the item
 * @param $parameters Parameters to send to ajax URL
 * @param $spinner NOT USED : always spinner - is a spinner displayed when loading ?
 * @param $toobserve id of another item used to get value in case of __VALUE__ used
 **/
function ajaxUpdateItemJsCode($toupdate,$url,$parameters=array(),$spinner=true,$toobserve=""){
	global $CFG_GLPI;

	// Get it from a Ext.Element object
	//echo "Ext.onReady(function() {";
		echo "Ext.get('$toupdate').load({
			url: '$url',
			scripts: true";
	
		if (count($parameters)){
			echo ",
				params:'";
			$first=true;
			foreach ($parameters as $key => $val){
				if ($first){
					$first=false;
				} else {
					echo "&";
				}
				
				echo $key."=";
				if ($val==="__VALUE__"){
					echo "'+Ext.get('$toobserve').getValue()+'";
				} else {
					if (is_array($val)){
						echo serialize($val);
					} else {
						echo $val;
					}
				}
	
			}
			echo "'\n";
		}
		echo "});";
	//echo "});";
}

?>

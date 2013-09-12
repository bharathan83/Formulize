<?php
###############################################################################
##     Formulize - ad hoc form creation and reporting module for XOOPS       ##
##                    Copyright (c) 2006 Freeform Solutions                  ##
##                Portions copyright (c) 2003 NS Tai (aka tuff)              ##
##                       <http://www.brandycoke.com/>                        ##
###############################################################################
##                    XOOPS - PHP Content Management System                  ##
##                       Copyright (c) 2000 XOOPS.org                        ##
##                          <http://www.xoops.org/>                          ##
###############################################################################
##  This program is free software; you can redistribute it and/or modify     ##
##  it under the terms of the GNU General Public License as published by     ##
##  the Free Software Foundation; either version 2 of the License, or        ##
##  (at your option) any later version.                                      ##
##                                                                           ##
##  You may not change or alter any portion of this comment or credits       ##
##  of supporting developers from this source code or any supporting         ##
##  source code which is considered copyrighted (c) material of the          ##
##  original comment or credit authors.                                      ##
##                                                                           ##
##  This program is distributed in the hope that it will be useful,          ##
##  but WITHOUT ANY WARRANTY; without even the implied warranty of           ##
##  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            ##
##  GNU General Public License for more details.                             ##
##                                                                           ##
##  You should have received a copy of the GNU General Public License        ##
##  along with this program; if not, write to the Free Software              ##
##  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA ##
###############################################################################
##  Author of this file: Freeform Solutions                                  ##
##  Project: Formulize                                                       ##
###############################################################################

require_once XOOPS_ROOT_PATH.'/kernel/object.php';
include_once XOOPS_ROOT_PATH.'/modules/formulize/include/functions.php';

class formulizeForm extends XoopsObject {
	function formulizeForm($id_form="", $includeAllElements=false){

		// validate $id_form
		global $xoopsDB;

		if(!is_numeric($id_form)) {
			// set empty defaults
			$id_form = "";
			$lockedform = "";
			$formq[0]['desc_form'] = "";
			$single = "";
			$elements = array();
			$elementCaptions = array();
			$elementColheads = array();
			$elementHandles = array();
			$elementTypes = array();
			$encryptedElements = array();
		} else {
			$formq = q("SELECT * FROM " . $xoopsDB->prefix("formulize_id") . " WHERE id_form=$id_form");
			if(!isset($formq[0])) {
				unset($formq);
				$id_form = "";
				$lockedform = "";
				$formq[0]['desc_form'] = "";
				$formq[0]['tableform'] = "";
				$single = "";
				$elements = array();
				$elementCaptions = array();
				$elementColheads = array();
				$elementHandles = array();
				$elementTypes = array();
				$encryptedElements = array();
			} else {
				// gather element ids for this form
				$displayFilter = $includeAllElements ? "" : "AND ele_display != \"0\"";
				$elementsq = q("SELECT ele_id, ele_caption, ele_colhead, ele_handle, ele_type, ele_encrypt FROM " . $xoopsDB->prefix("formulize") . " WHERE id_form=$id_form $displayFilter ORDER BY ele_order ASC");
				$encryptedElements = array();
				foreach($elementsq as $row=>$value) {
					$elements[$value['ele_id']] = $value['ele_id'];
					$elementCaptions[$value['ele_id']] = $value['ele_caption'];
					$elementColheads[$value['ele_id']] = $value['ele_colhead'];
					$elementHandles[$value['ele_id']] = $value['ele_handle'];
					$elementTypes[$value['ele_id']] = $value['ele_type'];
					if($value['ele_encrypt']) {
						$encryptedElements[$value['ele_id']] = $value['ele_handle'];
					}
				}
				
				// propertly format the single value
				switch($formq[0]['singleentry']) {
					case "group":
						$single = "group";
						break;
					case "on":
						$single = "user";
						break;
					case "":
						$single = "off";
						break;
					default:
						$single = "";
						break;
				}
			}
			
			// gather the view information
			$viewq = q("SELECT * FROM " . $xoopsDB->prefix("formulize_saved_views") . " WHERE sv_mainform = '$id_form' OR (sv_mainform = '' AND sv_formframe = '$id_form')");
			if(!isset($viewq[0])) {
				$views = array();
				$viewNames = array();
				$viewFrids = array();
				$viewPublished = array();
			} else {
				for($i=0;$i<count($viewq);$i++) {
					$views[$i] = $viewq[$i]['sv_id'];
					$viewNames[$i] = stripslashes($viewq[$i]['sv_name']);
					$viewFrids[$i] = $viewq[$i]['sv_mainform'] ? $viewq[$i]['sv_formframe'] : "";
					$viewPublished[$i] = $viewq[$i]['sv_pubgroups'] ? true : false;
				}
			}
			
			// setup the filter settings
			$filterSettingsq = q("SELECT groupid, filter FROM " . $xoopsDB->prefix("formulize_group_filters") . " WHERE fid='$id_form'");
			if(!isset($filterSettingsq[0])) {
				$filterSettings = array();
			} else {
				foreach($filterSettingsq as $filterSettingData) {
					$filterSettings[$filterSettingData['groupid']] = unserialize($filterSettingData['filter']);
				}
			}
			
		}

		$this->XoopsObject();
		//initVar params: key, data_type, value, req, max, opt
		$this->initVar("id_form", XOBJ_DTYPE_INT, $id_form, true);
		$this->initVar("lockedform", XOBJ_DTYPE_INT, $formq[0]['lockedform'], true);
		$this->initVar("title", XOBJ_DTYPE_TXTBOX, $formq[0]['desc_form'], true, 255);
		$this->initVar("tableform", XOBJ_DTYPE_TXTBOX, $formq[0]['tableform'], true, 255);
		$this->initVar("single", XOBJ_DTYPE_TXTBOX, $single, true, 5);
		$this->initVar("elements", XOBJ_DTYPE_ARRAY, serialize($elements));
		$this->initVar("elementCaptions", XOBJ_DTYPE_ARRAY, serialize($elementCaptions));
		$this->initVar("elementColheads", XOBJ_DTYPE_ARRAY, serialize($elementColheads));
		$this->initVar("elementHandles", XOBJ_DTYPE_ARRAY, serialize($elementHandles));
		$this->initVar("elementTypes", XOBJ_DTYPE_ARRAY, serialize($elementTypes));
		$this->initVar("encryptedElements", XOBJ_DTYPE_ARRAY, serialize($encryptedElements));
		$this->initVar("views", XOBJ_DTYPE_ARRAY, serialize($views));
		$this->initVar("viewNames", XOBJ_DTYPE_ARRAY, serialize($viewNames));
		$this->initVar("viewFrids", XOBJ_DTYPE_ARRAY, serialize($viewFrids));
		$this->initVar("viewPublished", XOBJ_DTYPE_ARRAY, serialize($viewPublished));
		$this->initVar("filterSettings", XOBJ_DTYPE_ARRAY, serialize($filterSettings));
		
	}
}

class formulizeFormsHandler {
	var $db;
	function formulizeFormsHandler(&$db) {
		$this->db =& $db;
	}
	function &getInstance(&$db) {
		static $instance;
		if (!isset($instance)) {
			$instance = new formulizeFormsHandler($db);
		}
		return $instance;
	}
	function &create() {
		return new formulizeForm();
	}
	
	function &get($fid,$includeAllElements=false) {
		$fid = intval($fid);
		static $cachedForms = array();
		if(isset($cachedForms[$fid])) { return $cachedForms[$fid]; }
		if($fid > 0) {
			$cachedForms[$fid] = new formulizeForm($fid,$includeAllElements);
			return $cachedForms[$fid];
		}
		return false;
	}

	function getAllForms($includeAllElements=false) {
		global $xoopsDB;
		$allFidsQuery = "SELECT id_form FROM " . $xoopsDB->prefix("formulize_id");
		$allFidsRes = $xoopsDB->query($allFidsQuery);
		$foundFids = array();
		while($allFidsArray = $xoopsDB->fetchArray($allFidsRes)) {
			$foundFids[] = $this->get($allFidsArray['id_form'],$includeAllElements);
		}
		return $foundFids;
	}
		
	// accepts a framework object or frid
	function getFormsByFramework($framework_Object_or_Frid) {
		if(is_object($framework_Object_or_Frid)) {
			if(get_class($framework_Object_or_Frid) == "formulizeFramework") {
				$frameworkObject = $framework_Object_or_Frid;
			} else {
				return false;
			}
		} elseif(is_numeric($framework_Object_or_Frid)) {
			include_once XOOPS_ROOT_PATH . "/modules/formulize/class/frameworks.php";
			$frameworkObject = new formulizeFramework($frid);
		} else {
			return false;
		}
		$fids = array();
		foreach($frameworkObject->getVar('fids') as $thisFid) {
			$fids[] = $this->get($thisFid);
		}
		return $fids;
	}
		
	// lock the form...set the lockedform flag to indicate that no further editing of this form is allowed
	function lockForm($fid) {
		global $xoopsDB;
		$sql = "UPDATE ".$xoopsDB->prefix("formulize_id") . " SET lockedform = 1 WHERE id_form = ". intval($fid);
		if(!$res = $xoopsDB->queryF($sql)) {
			return false;
		} else {
			return true;
		}
	}
	
	// check to see if a handle is unique within a form
	function isHandleUnique($handle, $element_id="") {
		$ucHandle = strtoupper($handle);
		if($ucHandle == "CREATION_UID" OR $ucHandle == "CREATION_DATETIME" OR $ucHandle == "MOD_UID" OR $ucHandle == "MOD_DATETIME" OR $ucHandle == "CREATOR_EMAIL" OR $ucHandle == "UID" OR $ucHandle == "PROXYID" OR $ucHandle == "CREATION_DATE" OR $ucHandle == "MOD_DATE" OR $ucHandle == "MAIN_EMAIL" OR $ucHandle == "MAIN_USER_VIEWEMAIL") {
			return false; // don't allow reserved words that will be used in the main data extraction queries
		}
		global $xoopsDB;
		$element_id_condition = $element_id ? " AND ele_id != " . intval($element_id) : "";
		$sql = "SELECT count(ele_handle) FROM " . $xoopsDB->prefix("formulize") . " WHERE ele_handle = '" . mysql_real_escape_string($handle) . "' $element_id_condition";
		if(!$res = $xoopsDB->query($sql)) {
			print "Error: could not verify uniqueness of handle '$handle' in form $fid";
		} else {
			$row = $xoopsDB->fetchRow($res);
			if($row[0] == 0) { // zero rows found with that handle in this form
				return true;
			} else {
				return false;
			}
		}
	}
	
	// create a data table for a form object (or form)
	// $fid can be an id or an object
	// Note that this method will add in fields for the elements in the form, if invoked as part of the 3.0 patch process, or when cloning forms.
	// if a map is provided, then we're cloning a form and the data types of the original elements will be preserved in the new form
	function createDataTable($fid, $clonedForm=0, $map=false) {
		if(is_numeric($fid)) {
			$fid = $this->get($fid, true); // true forces all elements to be included, even ones that are not displayed right now
		} elseif(!get_class($fid) == "formulizeForm") {
			return false;
		}
		$elementTypes = $fid->getVar('elementTypes');
		global $xoopsDB;
		// build SQL for new table
		$newTableSQL = "CREATE TABLE " . $xoopsDB->prefix("formulize_" . $fid->getVar('id_form')) . " (";
		$newTableSQL .= "entry_id int(7) unsigned NOT NULL auto_increment,";
		$newTableSQL .= "creation_datetime Datetime NULL default NULL, ";
		$newTableSQL .= "mod_datetime Datetime NULL default NULL, ";
		$newTableSQL .= "creation_uid int(7) default '0',";
		$newTableSQL .= "mod_uid int(7) default '0',";
		foreach($fid->getVar('elementHandles') as $elementId=>$thisHandle) {
						if($elementTypes[$elementId] == "areamodif" OR $elementTypes[$elementId] == "ib" OR $elementTypes[$elementId] == "sep" OR $elementTypes[$elementId] == "grid" OR $elementTypes[$elementId] == "subform") { continue; } // do not attempt to create certain types of fields since they don't live in the db!
						if($map !== false) {
							// we're cloning with data, so base the new field's datatype on the original form's datatype for the corresponding field
							if(!isset($dataTypeMap)) {
								$dataTypeMap = array();
								$dataTypeSQL = "SHOW COLUMNS FROM " . $xoopsDB->prefix("formulize_".$clonedForm);
								if($dataTypeRes = $xoopsDB->queryF($dataTypeSQL)) {
									while($dataTypeArray = $xoopsDB->fetchArray($dataTypeRes)) {
										$dataTypeMap[$dataTypeArray['Field']] = $dataTypeArray['Type'];
									}
								} else {
									print "Error: could not get column datatype information for the source form.<br>$dataTypeSQL<br>";
									return false;
								}
							}
							$newTableSQL .= "`$thisHandle` ".$dataTypeMap[array_search($thisHandle, $map)]." NULL default NULL,";
						} else {
							if($elementTypes[$elementId] == "date") {
								$newTableSQL .= "`$thisHandle` date NULL default NULL,";
							} else {
								$newTableSQL .= "`$thisHandle` text NULL default NULL,";
							}
						}
		}
		$newTableSQL .= "PRIMARY KEY (`entry_id`),";
		$newTableSQL .= "INDEX i_creation_uid (creation_uid)";
		$newTableSQL .= ") TYPE=MyISAM;";
		// make the table
		if(!$tableCreationRes = $xoopsDB->queryF($newTableSQL)) {
			return false;
		}
		return true;
	}

	// drop the data table
	// fid can be an id or object
	function dropDataTable($fid) {
		if(is_object($fid)) {
			if(!get_class("formulizeForm")) {
				return false;
			}
			$fid = $fid->getVar('id_form');
		} elseif(!is_numeric($fid)) {
			return false;
		}
		global $xoopsDB;
		$dropSQL = "DROP TABLE " . $xoopsDB->prefix("formulize_" . $fid);
		if(!$dropRes = $xoopsDB->queryF($dropSQL)) {
			return false;
		}
		// remove the entry owner groups info for that form
		$ownershipSQL = "DELETE FROM " . $xoopsDB->prefix("formulize_entry_owner_groups") . " WHERE fid=$fid";
		if(!$ownershipSQLRes = $xoopsDB->queryF($ownershipSQL)) {
			print "error: could not remove entry ownership data for form $fid";
		}
		return true;
	}
	
	// this function deletes an element field from the data table
	// $id can be numeric or an object
	function deleteElementField($element) {
		if(!$element = _getElementObject($element)) {
			return false;
		}
		global $xoopsDB;
		$deleteFieldSQL = "ALTER TABLE " . $xoopsDB->prefix("formulize_" . $element->getVar('id_form')) . " DROP `" . $element->getVar('ele_handle') . "`";
		if(!$deleteFieldRes = $xoopsDB->queryF($deleteFieldSQL)) {
			return false;
		}
		return true;
	}
	
	// this function adds an element field to the data table
	// $id can be numeric or an object
	function insertElementField($element, $dataType) {
		if(!$element = _getElementObject($element)) {
			return false;
		}
		global $xoopsDB;
		$dataType = $dataType ? $dataType : "text";
		$insertFieldSQL = "ALTER TABLE " . $xoopsDB->prefix("formulize_" . $element->getVar('id_form')) . " ADD `" . $element->getVar('ele_handle') . "` $dataType NULL default NULL";
		if(!$insertFieldRes = $xoopsDB->queryF($insertFieldSQL)) {
			return false;
		}
		return true;
	}
	
	// update the field name in the datatable.  $element can be an id or an object.
	// $newName can be used to override the current ele_handle value.  Introduced for handling the toggling of encryption on/off where we need to rename fields to something other than the ele_handle value.
	function updateField($element, $oldName, $dataType=false, $newName="") {
		if(!$element = _getElementObject($element)) {
			return false;
		}
		global $xoopsDB;
		if(!$dataType) {
			// first get its current state:
			$fieldStateSQL = "SHOW COLUMNS FROM " . $xoopsDB->prefix("formulize_" . $element->getVar('id_form')) ." LIKE '$oldName'"; // note very odd use of LIKE as a clause of its own in SHOW statements, very strange, but that's what MySQL does
			if(!$fieldStateRes = $xoopsDB->queryF($fieldStateSQL)) {
				return false;
			}
			$fieldStateData = $xoopsDB->fetchArray($fieldStateRes);
			$dataType = $fieldStateData['Type'];
		}
		$newName = $newName ? $newName : $element->getVar('ele_handle');
		$updateFieldSQL = "ALTER TABLE " . $xoopsDB->prefix("formulize_" . $element->getVar('id_form')) . " CHANGE `$oldName` `$newName` ". $dataType; 
		if(!$updateFieldRes = $xoopsDB->queryF($updateFieldSQL)) {
		  return false;
		}
		return true;
	}
	
	// this function updates the per group filter settings for a form
	// $filterSettings should be an array that has keys for groups, and then an array of all the filter settings (which will be an array of three other arrays, one for elements, one for ops and one for terms, all in synch)
	function setPerGroupFilters($filterSettings, $fid) {
		if(!is_numeric($fid) OR !is_array($filterSettings)) {
			return false;
		}
		global $xoopsDB;
		// loop through the settings and make a query to check for what exists and needs updating, vs. inserting
		$foundGroups = array();
		$checkSQL = "SELECT groupid FROM ".$xoopsDB->prefix("formulize_group_filters"). " WHERE fid=".$fid;
		$checkRes = $xoopsDB->query($checkSQL);
		while($checkArray = $xoopsDB->fetchArray($checkRes)) {
			$foundGroups[$checkArray['groupid']] = true;
		}
		
		$insertStart = true;
		$insertSQL = "INSERT INTO ".$xoopsDB->prefix("formulize_group_filters")." (`fid`, `groupid`, `filter`) VALUES ";
		$updateSQL = "UPDATE ".$xoopsDB->prefix("formulize_group_filters")." SET filter = CASE groupid ";
		$runUpdate = false;
		$runInsert = false;
		foreach($filterSettings as $groupid=>$theseSettings) {
			if(isset($foundGroups[$groupid])) {
				// add to update query
				$updateSQL .= "WHEN $groupid THEN '".mysql_real_escape_string(serialize($theseSettings))."' ";
				$runUpdate = true;
			} else {
				// add to the insert query
			  if(!$insertStart) { $insertSQL .= ", "; }
				$insertSQL .= "(".$fid.", ".$groupid.", '".mysql_real_escape_string(serialize($theseSettings))."')";
				$insertStart = false;
				$runInsert = true;
			}
		}
		$updateSQL .= " ELSE filter END WHERE fid=".$fid;
		
		if($runInsert) {
			if(!$xoopsDB->query($insertSQL)) {
				return false;
			}
		}
		if($runUpdate) {
			if(!$xoopsDB->query($updateSQL)) {
				return false;
			}
		}
		return true;
	
	}
	
	// this function clears the per group filters for a form
	function clearPerGroupFilters($groupids, $fid) {
		if(!is_array($groupids)) {
			$groupids = array(0=>$groupids);
		}
		if(!is_numeric($fid)) {
			return false;
		}
		global $xoopsDB;
		$deleteSQL = mysql_real_escape_string("DELETE FROM ".$xoopsDB->prefix("formulize_group_filters")." WHERE fid=$fid AND groupid IN (".implode(", ",$groupids).")");
		if(!$xoopsDB->query($deleteSQL)) {
			return false;
		} else {
			return true;
		}
	}
	
	// this function returns a per-group filter for the current user on the specified form, formatted as a where clause, for the specified form alias, if any
	// if groupids is specified, then it will base the filter on those groups and not the current groups
	function getPerGroupFilterWhereClause($fid, $formAlias="", $groupids=false) {

		if(!is_numeric($fid) OR $fid < 1) {
			return "";
		}

		global $xoopsUser;
		$uid = $xoopsUser ? $xoopsUser->getVar('uid') : 0;
		if(!is_array($groupids)) {
			$groupids = $xoopsUser ? $xoopsUser->getGroups() : array(0=>XOOPS_GROUP_ANONYMOUS);
		}
		
		
		if($formAlias) {
			$formAlias .= "."; // add a period at the end of the alias so it will work with the field names in the query
		}
		
		// get all the filters in effect for the specified groups, the process them all into a variable we can tack onto the end of any query
		// all filters are always on the mainform only
		global $xoopsDB;
		$getFiltersSQL = "SELECT filter FROM ".$xoopsDB->prefix("formulize_group_filters"). " WHERE groupid IN (".implode(",",$groupids).") AND fid=$fid";
		if(!$getFiltersRes = $xoopsDB->query($getFiltersSQL)) {
			return false;
		}
		$perGroupFilter = "";
		while($filters = $xoopsDB->fetchArray($getFiltersRes)) {
			$filterSettings = unserialize($filters['filter']);
			// filterSettings[0] will be the elements
			// filterSettings[1] will be the ops
			// filterSettings[2] will be the terms
			for($i=0;$i<count($filterSettings[0]);$i++) {
				if(!$perGroupFilter) { // start up the filter if this is the first time through
					$perGroupFilter = " AND (";
				} else { // put in an AND so we can include the next filter setting
					$perGroupFilter .= " OR "; // need to make this a user configurable option
				}
				$likeBits = strstr(strtoupper($filterSettings[1][$i]), "LIKE") ? "%" : "";
				$termToUse = $filterSettings[2][$i] === "{USER}" ? $uid : $filterSettings[2][$i];
				$termToUse = is_numeric($termToUse) ? $termToUse : "\"$likeBits".mysql_real_escape_string($termToUse)."$likeBits\"";
				$perGroupFilter .= "$formAlias`".$filterSettings[0][$i]."` ".htmlspecialchars_decode($filterSettings[1][$i]) . " " . $termToUse; // htmlspecialchars_decode is used because &lt;= might be the operator coming out of the DB instead of <=
			}
		}
		if($perGroupFilter) {
				 $perGroupFilter .= ") ";
		}
		return $perGroupFilter;
	}

}
<?php
/**
 * Editor framework for XOOPS
 *
 * @copyright	The XOOPS project http://www.xoops.org/
 * @license		http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		Taiwen Jiang (phppp or D.J.) <php_pp@hotmail.com>
 * @since		4.00
 * @version		$Id: xoopseditor.inc.php 9046 2009-07-22 14:14:40Z pesianstranger $
 * @package		xoopseditor
 */

if(!function_exists("xoopseditor_get_rootpath")){
	function xoopseditor_get_rootpath($type = '')
	{
/*		static $rootpath;
		if(isset($rootpath)) return $rootpath;
		$current_path = __FILE__;
		if ( DIRECTORY_SEPARATOR != "/" ) $current_path = str_replace( strpos( $current_path, "\\\\", 2 ) ? "\\\\" : DIRECTORY_SEPARATOR, "/", $current_path);
		$rootpath = dirname($current_path);
		icms_debug('editor path' . $rootpath);
		return $rootpath;*/
		if ($type == '') {
			return XOOPS_EDITOR_PATH;
		} else {
			return ICMS_PLUGINS_PATH . '/' . strtolower($type) . 'editors/';
		}
	}
}

if(defined("ICMS_ROOT_PATH")) {
	return true;
}

$mainfile = dirname(dirname(__FILE__))."/mainfile.php";
if ( DIRECTORY_SEPARATOR != "/" ) $mainfile = str_replace( DIRECTORY_SEPARATOR, "/", $mainfile);

include $mainfile;
return defined("ICMS_ROOT_PATH");
?>
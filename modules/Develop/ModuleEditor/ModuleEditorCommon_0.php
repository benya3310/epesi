<?php
/**
 * Epesi developer editor
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 0.1
 * @package epesi-develop
 * @subpackage moduleeditor
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Develop_ModuleEditorCommon extends ModuleCommon {
	public function _menu() {
		if ($this->acl_check('Edit module')) 
			return array('Development'=>array('__submenu__'=>1,'File manager'=>array()));
		return array();		
	}
	
	public function menu() {
		return self::Instance()->_menu();
	}

}

?>
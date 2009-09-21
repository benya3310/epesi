<?php
/**
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 0.8
 * @license MIT
 * @package epesi-develop
 * @subpackage ModuleCreator
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Develop_ModuleCreatorCommon extends ModuleCommon {
	public function _menu() {
		if ($this->acl_check('Create module')) 
			return array('Development'=>array('__submenu__'=>1,'Create Module'=>array('action'=>'new')));
		return array();		
	}
	
	public function body_access() {
		return $this->acl_check('Create module');
	}
	
	public static function menu(){
		return self::Instance()->_menu();
	}
}
?>

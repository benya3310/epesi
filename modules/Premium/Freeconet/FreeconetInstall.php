<?php
/**
 * freeconet.pl VoIP
 * @author pbukowski@telaxus.com
 * @copyright Telaxus LLC
 * @license MIT
 * @version 0.1
 * @package epesi-Premium
 * @subpackage Freeconet
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_FreeconetInstall extends ModuleInstall {

	public function install() {
		Base_ThemeCommon::install_default_theme($this->get_type());
		return true;
	}
	
	public function uninstall() {
		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		return true;
	}
	
	public function version() {
		return array("0.1");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base/Lang', 'version'=>0),
			array('name'=>'Base/Dashboard', 'version'=>0),
			array('name'=>'Base/User/Settings', 'version'=>0),
			array('name'=>'Utils/Tooltip', 'version'=>0),
			array('name'=>'CRM/Common', 'version'=>0),
		);
	}
	
	public static function info() {
		return array(
			'Description'=>'freeconet.pl VoIP',
			'Author'=>'pbukowski@telaxus.com',
			'License'=>'MIT');
	}
	
	public static function simple_setup() {
		return true;
	}
	
}

?>
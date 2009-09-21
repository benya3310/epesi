<?php
/**
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 0.8
<<<<<<< .mine
 * @license MIT
=======
>>>>>>> .r4403
 * @package epesi-develop
<<<<<<< .mine
 * @subpackage ModuleCreator
=======
 * @subpackage modulecreator
>>>>>>> .r4403
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Develop_ModuleCreatorInstall extends ModuleInstall {
	public function install() {
		$this->add_aco('Create module','Super administrator');
		return true;
	}
	
	public function uninstall() {
		return true;
	}
	
	public function version() {
		return array('0.9.0');
	}

	public function requires($v) {
		return array(array('name'=>'Utils/Wizard','version'=>0),
				array('name'=>'Utils/Tree','version'=>0),
				array('name'=>'Utils/GenericBrowser','version'=>0));
	}
}

?>

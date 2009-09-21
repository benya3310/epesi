<?php
/**
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2009, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-premium
 * @subpackage freeconet
 */
 
if(!isset($_POST['cid']) || !isset($_POST['phone']))
	die('alert(\'Invalid request\')');

define('JS_OUTPUT',1);
define('CID',$_POST['cid']);
define('READ_ONLY_SESSION',1);
require_once('../../../include.php');
ModuleManager::load_modules();

function alert($e) {
	print('alert(\''.Epesi::escapeJS($e,false).'\');');
}

$_POST['phone'] = str_replace(array(' ','	','-'),'',$_POST['phone']);
if(!is_numeric($_POST['phone'])) {
	alert(Base_LangCommon::ts('Premium/Freeconet','Invalid number'));
	die();
}

if(!Acl::is_user()) {
	alert('not logged');
	die();
}

function errors($n,$func) {
	if(isset($n->errors)) { //error getting token
		foreach($n->errors->children() as $k=>$v) {
			if($k!='error') continue;
			alert(Base_LangCommon::ts('Premium/Freeconet',(string)$v->msg));
		}
		die();
	}
	if(isset($n->$func->errors)) { //error getting token
		foreach($n->$func->errors->children() as $k=>$v) {
			if($k!='error') continue;
			alert(Base_LangCommon::ts('Premium/Freeconet',(string)$v->msg));
		}
		die();
	}
	return true;
}

$login = Base_User_SettingsCommon::get('Premium_Freeconet','login');
$ret = Premium_FreeconetCommon::call_freeconet_func('makeCall',array('to'=>$_POST['phone'],'from'=>$login),'errors');
if(isset($ret->callId))
	alert(Base_LangCommon::ts('Premium/Freeconet','Dialing'));
print(Epesi::get_output());
?>

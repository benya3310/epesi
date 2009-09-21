<?php
/**
 * Simple RSS Feed applet
 * @author jtylek@telaxus.com
 * @copyright 2008 Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-applets
 * @subpackage freeconet
 */

if(!isset($_POST['cid']))
	die('Invalid request');

define('CID', $_POST['cid']);
define('READ_ONLY_SESSION',1);
require_once('../../../include.php');
ModuleManager::load_modules();

$ret = Premium_FreeconetCommon::call_freeconet_func('getGroupFinAccountInfo');
if(!$ret) die();
print(((string)$ret->credit!=''?Base_LangCommon::ts('Premium/Freeconet', 'Account balance: %s',array((string)$ret->credit)).'<br>':'').
	((float)$ret->debit!=0?Base_LangCommon::ts('Premium/Freeconet', 'Account debit: %s',array((string)$ret->debit)).'<br>':'').
	((float)$ret->creditLimit!=0?Base_LangCommon::ts('Premium/Freeconet', 'Credit limit: %s',array((string)$ret->creditLimit)).'<br>':'').
	((string)$ret->minusCreditDate!=''?Base_LangCommon::ts('Premium/Freeconet', 'Day when account balance went below zero: %s',array(Base_RegionalSettingsCommon::time2reg((int)$ret->minusCreditDate))):''));


$login = Base_User_SettingsCommon::get('Premium_Freeconet','login');
$ret = Premium_FreeconetCommon::call_freeconet_func('getRegistrationStatus',array('accountList'=>$login));
if(!$ret) die();
print('<hr>');
if((string)$ret->registerStatusList->registerStatus->status=="UNREGISTERED")
	print('<font color="red">'.Base_LangCommon::ts('Premium/Freeconet','Your number is not available').'</font>');
else
	print('<font color="green">'.Base_LangCommon::ts('Premium/Freeconet','Your number is available').'</font>');

?>

<?php
/**
 * Projects Tracker
 *
 * @author Janusz Tylek <jtylek@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-premium
 * @subpackage projects
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_ProjectsInstall extends ModuleInstall {

	public function install() {
		Base_LangCommon::install_translations($this->get_type());
		
		Base_ThemeCommon::install_default_theme($this->get_type());
		$fields = array(
			array('name'=>'Project Name', 	'type'=>'text', 'required'=>true, 'param'=>'64', 'extra'=>false, 'visible'=>true,'display_callback'=>array('Premium_ProjectsCommon', 'display_proj_name')),
			array('name'=>'Company Name', 	'type'=>'crm_company', 'param'=>array('field_type'=>'select','crits'=>array('Premium_ProjectsCommon','projects_company_crits')), 'filter'=>true, 'required'=>true, 'extra'=>false, 'visible'=>true),
			array('name'=>'Project Manager','type'=>'crm_contact', 'param'=>array('field_type'=>'select', 'crits'=>array('Premium_ProjectsCommon','projects_employees_crits'), 'format'=>array('CRM_ContactsCommon','contact_format_no_company')), 'required'=>true, 'visible'=>true, 'extra'=>false),
			array('name'=>'Status', 		'type'=>'commondata', 'required'=>true, 'visible'=>true, 'filter'=>true, 'param'=>array('order_by_key'=>true,'Premium_Projects_Status'), 'extra'=>false),
			array('name'=>'Start Date', 	'type'=>'date', 'required'=>false, 'param'=>64, 'extra'=>false),
			array('name'=>'Due Date', 		'type'=>'date', 'required'=>false, 'param'=>64, 'extra'=>false),
			array('name'=>'Description', 	'type'=>'long text', 'required'=>false, 'param'=>'250', 'extra'=>false)
		);

		Utils_RecordBrowserCommon::install_new_recordset('premium_projects', $fields);
		Utils_RecordBrowserCommon::new_filter('premium_projects', 'Company Name');
		Utils_RecordBrowserCommon::new_filter('premium_projects', 'Status');
		
		Utils_RecordBrowserCommon::set_quickjump('premium_projects', 'Project Name');
		Utils_RecordBrowserCommon::set_favorites('premium_projects', true);
		Utils_RecordBrowserCommon::set_recent('premium_projects', 15);
		Utils_RecordBrowserCommon::set_caption('premium_projects', 'Projects');
		Utils_RecordBrowserCommon::set_icon('premium_projects', Base_ThemeCommon::get_template_filename('Premium/Projects', 'icon.png'));
		Utils_RecordBrowserCommon::set_access_callback('premium_projects', array('Premium_ProjectsCommon', 'access_projects'));
		Utils_RecordBrowserCommon::enable_watchdog('premium_projects', array('Premium_ProjectsCommon','watchdog_label'));
		
// ************ addons ************** //
		Utils_RecordBrowserCommon::new_addon('premium_projects', 'Premium/Projects', 'premium_projects_attachment_addon', 'Notes');
		Utils_RecordBrowserCommon::new_addon('company', 'Premium/Projects', 'company_premium_projects_addon', 'Projects');

// ************ other ************** //	
		Utils_CommonDataCommon::new_array('Premium_Projects_Status',array(0=>'Planned',1=>'Approved',2=>'Canceled',3=>'In Progress',4=>'Completed',5=>'On Hold'),true,true);
		
		$this->add_aco('browse projects',array('Employee'));
		$this->add_aco('view projects',array('Employee'));
		$this->add_aco('edit projects',array('Employee'));
		$this->add_aco('delete projects',array('Employee Manager'));

		$this->add_aco('view protected notes','Employee');
		$this->add_aco('view public notes','Employee');
		$this->add_aco('edit protected notes','Employee Administrator');
		$this->add_aco('edit public notes','Employee');
		
		return true;
	}
	
	public function uninstall() {
		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_RecordBrowserCommon::delete_addon('premium_projects', 'Premium/Projects', 'premium_projects_attachment_addon');
		Utils_RecordBrowserCommon::delete_addon('company', 'Premium/Projects', 'company_premium_projects_addon');
		Utils_RecordBrowserCommon::uninstall_recordset('premium_projects');
		Utils_CommonDataCommon::remove('Premium_Projects_Status');
		return true;
	}
	
	public function version() {
		return array("1.0");
	}
	
	public function requires($v) {
		return array(
			array('name'=>'Base','version'=>0),
			array('name'=>'Utils/ChainedSelect', 'version'=>0), 
			array('name'=>'CRM/Contacts','version'=>0));
	}
	
	public static function info() {
		return array(
			'Description'=>'Projects Tracker - Premium Module',
			'Author'=>'jtylek@telaxus.com',
			'License'=>'MIT');
	}
	
	public static function simple_setup() {
		return true;
	}
	
	public static function backup() {
		return Utils_RecordBrowserCommon::get_tables('premium_projects');		
	}
}

?>

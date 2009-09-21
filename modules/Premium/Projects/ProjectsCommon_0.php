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

class Premium_ProjectsCommon extends ModuleCommon {
    public static function get_project($id) {
		return Utils_RecordBrowserCommon::get_record('premium_projects', $id);
    }

	public static function get_projects($crits=array(),$cols=array()) {
    		return Utils_RecordBrowserCommon::get_records('premium_projects', $crits, $cols);
	}

    public static function display_proj_name($v, $nolink=false) {
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_projects', 'Project Name', $v, $nolink);
	}

	public static function display_projmanager($v, $nolink) {
		return;
		//return Utils_RecordBrowserCommon::create_linked_label('contacts', 'Last Name', $v['id'], $nolink);
	}


	public static function access_projects($action, $param=null){
		$i = self::Instance();
		switch ($action) {
			case 'browse_crits':	return $i->acl_check('browse projects');
			case 'browse':	return true;
			case 'view':	return $i->acl_check('view projects');
			case 'add':
			case 'edit':	return $i->acl_check('edit projects');
			case 'delete':	return $i->acl_check('delete projects');
		}
		return false;
    }

    public static function menu() {
		return array('Bug tracker'=>array('__submenu__'=>1,'Projects'=>array()));
	}

// Filter criteria for Company Name
	public static function projects_company_crits(){
//  	   return array(':Fav'=>1);
// gc= GC (General Contractor), res=Residential
		return array('group'=>array('customer'));
   }

// Filter criteria for Epmloyees
// Used in Project Manager
	public static function projects_employees_crits(){
		return array('company_name'=>array(CRM_ContactsCommon::get_main_company()),'group'=>array('office'));
   }


	public static function applet_caption() {
		return 'Projects';
	}
	public static function applet_info() {
		return 'Projects List';
	}

	public static function applet_info_format($r){
		return
			'Project Name: '.$r['project_name'].'<HR>'.
			'Due Date: '.$r['due_date'].'<br>'.
			'Description: '.$r['description'];
	}


	public static function applet_settings() {
		$sts = Utils_CommonDataCommon::get_translated_array('Premium_Projects_Status');
		return Utils_RecordBrowserCommon::applet_settings(array(
			// array('name'=>'title','label'=>'Title','type'=>'text','default'=>'Projects','rule'=>array(array('message'=>'Field required', 'type'=>'required'))),
			array('name'=>'status','label'=>'Display projects with status','default'=>3,'type'=>'select','values'=>array('__NULL__'=>'[All active]','__ALL__'=>'[All]')+$sts),
			array('name'=>'my','label'=>'Display only my projects','default'=>1,'type'=>'select','values'=>array(0=>'No',1=>'Yes')),
			));
	}
	
	public static function watchdog_label($rid = null, $events = array(), $details = true) {
		return Utils_RecordBrowserCommon::watchdog_label(
				'premium_projects',
				Base_LangCommon::ts('Premium_Projects','Projects'),
				$rid,
				$events,
				'project_name',
				$details
			);
	}
	
	public static function search_format($id) {
		if(!self::Instance()->acl_check('browse projects')) return false;
		$row = self::get_projects(array('id'=>$id));
		if(!$row) return false;
		$row = array_pop($row);
		return Utils_RecordBrowserCommon::record_link_open_tag('premium_projects', $row['id']).Base_LangCommon::ts('Premium_Projects', 'Project (attachment) #%d, %s', array($row['id'], $row['project_name'])).Utils_RecordBrowserCommon::record_link_close_tag();
	}


	
	///////////////////////////////////
	// mobile devices

	public function mobile_menu() {
		if(!Acl::is_user())
			return array();
		return array('Projects'=>'mobile_projects');
	}
	
	public function mobile_projects() {
		$me = CRM_ContactsCommon::get_my_record();
		Utils_RecordBrowserCommon::mobile_rb('premium_projects',array('project_manager'=>$me['id'], '!status'=>array(2,4)),array('project_name'=>'ASC'),array('company_name'=>1,'status'=>1));
	}
}
?>

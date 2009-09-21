<?php
/**
 *
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-premium
 * @subpackage projects-tickets
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Premium_Projects_TicketsInstall extends ModuleInstall {

	public function install() {
		Base_LangCommon::install_translations($this->get_type());

		Base_ThemeCommon::install_default_theme($this->get_type());
		$fields = array(
			array('name'=>'Ticket ID', 			'type'=>'calculated', 'required'=>false, 'param'=>Utils_RecordBrowserCommon::actual_db_type('text',16), 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Projects_TicketsCommon','display_ticket_id')),
			array('name'=>'Title', 				'type'=>'text', 'required'=>true, 'param'=>'255', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Projects_TicketsCommon','display_title')),
			array('name'=>'Project Name', 		'type'=>'select','param'=>array('premium_projects'=>'Project Name', 'Premium_Projects_TicketsCommon'=>'projects_crits'), 'required'=>true, 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Projects_TicketsCommon', 'proj_name_callback')),
			array('name'=>'Type',				'type'=>'commondata', 'required'=>true, 'visible'=>true, 'filter'=>true, 'param'=>array('order_by_key'=>true,'Premium_Ticket_Type'), 'extra'=>false, 'visible'=>true),
			array('name'=>'Date',				'type'=>'date', 'extra'=>false, 'visible'=>true),
			array('name'=>'Priority', 			'type'=>'commondata', 'required'=>true, 'visible'=>true, 'param'=>array('order_by_key'=>true,'Premium_Ticket_Priorities'), 'extra'=>false, 'filter'=>true),
			array('name'=>'Status',				'type'=>'commondata', 'required'=>true, 'visible'=>true, 'filter'=>true, 'param'=>array('order_by_key'=>true,'Premium_Ticket_Status'), 'extra'=>false, 'visible'=>true, 'display_callback'=>array('Premium_Projects_TicketsCommon','display_status')),
			array('name'=>'Assigned To', 		'type'=>'crm_contact', 'param'=>array('field_type'=>'multiselect', 'crits'=>array('Premium_Projects_TicketsCommon','employees_crits'),'format'=>array('Premium_Projects_TicketsCommon','assigned_to_format')), 'display_callback'=>array('Premium_Projects_TicketsCommon','display_assigned_contacts'), 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name'=>'Permission', 		'type'=>'commondata', 'required'=>true, 'param'=>array('order_by_key'=>true,'CRM/Access'), 'extra'=>false),
			array('name'=>'Resolution',			'type'=>'commondata', 'required'=>false, 'visible'=>false, 'filter'=>true, 'param'=>array('order_by_key'=>true,'Premium_Ticket_Resolution'), 'extra'=>false, 'visible'=>true),
			array('name'=>'Due Date',			'type'=>'date', 'extra'=>false, 'visible'=>true),
			array('name'=>'Required tickets', 	'type'=>'multiselect', 'param'=>'premium_tickets::Ticket ID;Premium_Projects_TicketsCommon::required_tickets_crits;Premium_Projects_TicketsCommon::adv_required_tickets_params', 'display_callback'=>array('Premium_Projects_TicketsCommon','display_required_tickets'), 'required'=>false, 'extra'=>false, 'visible'=>true),
			array('name'=>'Description', 		'type'=>'long text', 'extra'=>false, 'param'=>'255', 'required'=>false, 'visible'=>false)	
		);


		Utils_RecordBrowserCommon::install_new_recordset('premium_tickets', $fields);

		Utils_RecordBrowserCommon::new_filter('premium_tickets', 'Project Name');
		Utils_RecordBrowserCommon::enable_watchdog('premium_tickets', array('Premium_Projects_TicketsCommon','watchdog_label'));
		Utils_RecordBrowserCommon::set_quickjump('premium_tickets', 'Title');
		Utils_RecordBrowserCommon::set_favorites('premium_tickets', false);
		Utils_RecordBrowserCommon::set_recent('premium_tickets', 15);
		Utils_RecordBrowserCommon::set_caption('premium_tickets', 'Tickets');
		Utils_RecordBrowserCommon::set_access_callback('premium_tickets', array('Premium_Projects_TicketsCommon', 'access_ticket'));
		Utils_RecordBrowserCommon::set_processing_callback('premium_tickets', array('Premium_Projects_TicketsCommon', 'submit_ticket'));

// ************ addons ************** //
//	Parameters: ('table','ModuleLocation','function','Label');
		Utils_RecordBrowserCommon::new_addon('premium_tickets', 'Premium/Projects/Tickets', 'premium_tickets_attachment_addon', 'Notes');
		Utils_RecordBrowserCommon::new_addon('premium_projects', 'Premium/Projects/Tickets', 'premium_projects_tickets_addon', 'Tickets');

// ************ other ************** //
		Utils_BBCodeCommon::new_bbcode('ticket', 'Premium_Projects_TicketsCommon', 'ticket_bbcode');

// Common Data Arrays
		Utils_CommonDataCommon::new_array('Premium_Ticket_Status',array('Open','In Progress','Resolved','Awaiting Feedback','Closed'), true,true);
		Utils_CommonDataCommon::new_array('Premium_Ticket_Priorities',array('Critical','Major','Minor','Trivial'), true,true);
		Utils_CommonDataCommon::new_array('Premium_Ticket_Resolution',array('Fixed','Invalid','Duplicate','Will Not Fix','Works For Me'), true,true);
		Utils_CommonDataCommon::new_array('Premium_Ticket_Type',array('Bug','Feature Request'), true,true);

	return true;
	}
	
	public function uninstall() {
		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		Utils_RecordBrowserCommon::uninstall_recordset('premium_tickets');
		Utils_CommonDataCommon::remove('Premium_Ticket_Status');
		Utils_CommonDataCommon::remove('Premium_Ticket_Priorities');
		Utils_CommonDataCommon::remove('Premium_Ticket_Resolution');
		Utils_CommonDataCommon::remove('Premium_Ticket_Type');
		return true;
	}

	public function requires($v) {
		return array(
			array('name'=>'Premium/Projects', 'version'=>0),
			array('name'=>'Utils/RecordBrowser', 'version'=>0),
			array('name'=>'Utils/Attachment', 'version'=>0),
			array('name'=>'CRM/Acl', 'version'=>0),
			array('name'=>'CRM/Contacts', 'version'=>0),
			array('name'=>'CRM/Followup', 'version'=>0),
			array('name'=>'CRM/Common', 'version'=>0),
			array('name'=>'Base/Lang', 'version'=>0),
			array('name'=>'Base/Acl', 'version'=>0),
			array('name'=>'Utils/ChainedSelect', 'version'=>0),
			array('name'=>'Data/Countries', 'version'=>0)
		);
	}

	public static function simple_setup() {
		return true;
	}

	public function version() {
		return array('1.0');
	}

// ************************************
	public static function info() {
		return array(
			'Description'=>'Premium Projects - Tickets tracker',
			'Author'=>'abisaga@telaxus.com',
			'License'=>'MIT');
	}

	public static function backup() {
		return Utils_RecordBrowserCommon::get_tables('premium_tickets');		
	}
}

?>

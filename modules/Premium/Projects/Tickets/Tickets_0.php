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

class Premium_Projects_Tickets extends Module {
	private $rb;

	public function body() {
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_tickets','premium_tickets');
		$me = CRM_ContactsCommon::get_my_record();
		$this->rb->set_defaults(array('assigned_to'=>$me['id'], 'date'=>date('Y-m-d'), 'status'=>0, 'permission'=>0));
		$this->rb->set_crm_filter('assigned_to');
		$sts = Utils_CommonDataCommon::get_translated_array('Premium_Ticket_Status',true);
		$trans = array('__NULL__'=>array(), '__ALLACTIVE__'=>Premium_Projects_TicketsCommon::active_tickets_crits());
		foreach ($sts as $k=>$v)
			$trans[$k] = array('status'=>$k);
		$this->rb->set_custom_filter('status',array('type'=>'select','label'=>$this->t('Ticket status'),'args'=>array('__NULL__'=>'['.$this->t('All').']','__ALLACTIVE__'=>'['.$this->t('All active').']')+$sts,'trans'=>$trans));
		$this->rb->set_filters_defaults(array('status'=>'__ALLACTIVE__'));
		$this->rb->set_default_order(array('status'=>'ASC', 'priority'=>'ASC'));
		$this->rb->set_header_properties(array('ticket_id'=>array('width'=>1),'priority'=>array('width'=>1, 'wrapmode'=>'nowrap'),'project_name'=>array('width'=>1, 'wrapmode'=>'nowrap'),'assigned_to'=>array('width'=>1, 'wrapmode'=>'nowrap'),'status'=>array('width'=>1, 'wrapmode'=>'nowrap'),'type'=>array('width'=>1, 'wrapmode'=>'nowrap'),'resolution'=>array('width'=>1, 'wrapmode'=>'nowrap'),'required_tickets'=>array('width'=>1)));
		$this->display_module($this->rb);
	}

	public function premium_tickets_attachment_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('Premium/Projects/Tickets'.$arg['id']));
		$a->set_view_func(array('Premium_Projects_TicketsCommon','search_format'),array($arg['id']));
		$a->enable_watchdog('premium_tickets',$arg['id']);
		$a->additional_header('Ticket: '.$arg['title']);
		$this->display_module($a);
	}

	public function premium_projects_tickets_addon($arg){
		$rb = $this->init_module('Utils/RecordBrowser','premium_tickets');
		$ticket = array(array('project_name'=>$arg['id']), array('project_name'=>false), array());
		$me = CRM_ContactsCommon::get_my_record();
		$rb->set_defaults(array('project_name'=>$arg['id'],'assigned_to'=>$me['id'], 'date'=>date('Y-m-d'), 'status'=>0, 'permission'=>0));
		$this->display_module($rb,$ticket,'show_data');
	}
	
	public function applet($conf,$opts) {
		$opts['go'] = true;
		$opts['title'] = Base_LangCommon::ts('Premium/Projects/Tickets','Tickets');
		$me = CRM_ContactsCommon::get_my_record();
		if ($me['id']==-1) {
			CRM_ContactsCommon::no_contact_message();
			return;
		}
		$defaults = array('assigned_to'=>$me['id'], 'date'=>date('Y-m-d'), 'status'=>0, 'permission'=>0);
		$rb = $this->init_module('Utils/RecordBrowser','premium_tickets','premium_tickets');

		/********* SEARCH BY ID *************/
		static $id=0;
		$id++;
		print('<div id="tickets_applet_'.$id.'" style="display:none;">');
		$ret = $rb->search_by_id_form('Ticket ID');
		print('</div>');
		if ($ret) eval_js('document.getElementById(\'tickets_applet_'.$id.'\').style.display=\'block\';');
		$opts['actions'][] = '<a onclick="if(document.getElementById(\'tickets_applet_'.$id.'\').style.display==\'none\')set_style=\'block\';else set_style=\'none\';document.getElementById(\'tickets_applet_'.$id.'\').style.display=set_style" href="javascript:void(0)" '.Utils_TooltipCommon::open_tag_attrs($this->t('Search by ticket ID')).'><img src="'.Base_ThemeCommon::get_template_file('Premium_Projects_Tickets','search.png').'" border="0"></a>';
		/********* SEARCH BY ID *************/

		$crits = array();
		if (!($conf['mine'] && $conf['unassigned'] && $conf['others'])) {
			if ($conf['mine'] && $conf['unassigned'] && !$conf['others']) {
				$crits = array('assigned_to'=>array($me['id'],''));
				$opts['title'] = $conf['status']=='_all'?Base_LangCommon::ts('Premium/Projects/Tickets','All tickets'):Base_LangCommon::ts('Premium/Projects/Tickets','Active tickets');
			}
			if (!$conf['mine'] && !$conf['unassigned'] && $conf['others']) {
				unset($defaults['assigned_to']);
				$crits = array('!assigned_to'=>array($me['id'],''));
				$opts['title'] = $conf['status']=='_all'?Base_LangCommon::ts('Premium/Projects/Tickets','All tickets'):Base_LangCommon::ts('Premium/Projects/Tickets','Active tickets');
			}
			if ($conf['mine'] && !$conf['unassigned'] && !$conf['others']) {
				$crits = array('assigned_to'=>array($me['id']));
				$opts['title'] = Base_LangCommon::ts('Premium/Projects/Tickets','My tickets');
			}
			if (!$conf['mine'] && $conf['unassigned'] && $conf['others']) {
				unset($defaults['assigned_to']);
				$crits = array('!assigned_to'=>array($me['id']));
				$opts['title'] = Base_LangCommon::ts('Premium/Projects/Tickets','Not my tickets');
			}
			if (!$conf['mine'] && $conf['unassigned'] && !$conf['others']) {
				unset($defaults['assigned_to']);
				$crits = array('assigned_to'=>'');
				$opts['title'] = Base_LangCommon::ts('Premium/Projects/Tickets','Unassigned tickets');
			}
			if ($conf['mine'] && !$conf['unassigned'] && $conf['others']) {
				$crits = array('!assigned_to'=>'');
				$opts['title'] = Base_LangCommon::ts('Premium/Projects/Tickets','Assigned tickets');
			}
		}
		if ($conf['type']!='_all') {
			$crits['type'] = $conf['type'];
			$defaults['type'] = $conf['type'];
			$opts['title'] .= ' - '.Utils_CommonDataCommon::get_value('Premium_Ticket_Type/'.$conf['type']);
		}
		$opts['actions'][] = Utils_RecordBrowserCommon::applet_new_record_button('premium_tickets',$defaults);

		if ($conf['status']!='_all') {
			if ($conf['status']=='_active') $crits = Utils_RecordBrowserCommon::merge_crits(Premium_Projects_TicketsCommon::active_tickets_crits(),$crits);
			elseif ($conf['status']=='_no_closed_resoloved') $crits = Utils_RecordBrowserCommon::merge_crits(array('!status'=>array(2,4)),$crits);
			else $crits['status'] = $conf['status'];
		}
		$projects = array();
		foreach ($conf as $k=>$v)
			if (strpos($k,'project_')!==false && $v)
				$projects[] = substr($k,8);
				
		$crits['project_name'] = $projects;
		$conds = array(
									array(	//array('field'=>'ticket_id', 'width'=>1),
											array('field'=>'title', 'width'=>20, 'cut'=>21, 'callback'=>array('Premium_Projects_TicketsCommon','display_title_with_mark')),
//											array('field'=>'due_date', 'width'=>1),
											array('field'=>'status', 'width'=>1, 'callback'=>array('Premium_Projects_TicketsCommon','display_status_applet'))
										),
									$crits,
									array('priority'=>'ASC','due_date'=>'ASC'),
									array('Premium_Projects_TicketsCommon','applet_info_format'),
									15,
									$conf,
									& $opts
				);
		$this->display_module($rb, $conds, 'mini_view');
	}

	public function caption() {
		return "Tickets";
	}

}

?>

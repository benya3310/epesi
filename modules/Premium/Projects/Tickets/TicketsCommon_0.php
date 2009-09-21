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

class Premium_Projects_TicketsCommon extends ModuleCommon {
	
	public static function active_tickets_crits() {
//		return array('!status'=>array(2,4));
		return array('!status'=>4);
	}

	public static function menu() {
		return array('Bug tracker'=>array('__submenu__'=>1,'Tickets'=>array()));
	}

	public static function applet_caption() {
		return "Tickets";
	}

	public static function applet_info() {
		return "Tickets for Projects";
	}
	public static function ticket_bbcode($text, $param, $opt) {
		return Utils_RecordBrowserCommon::record_bbcode('premium_tickets', array('ticket_id','title'), $text, $param, $opt);
	}
	public static function watchdog_label($rid = null, $events = array(), $details = true) {
		return Utils_RecordBrowserCommon::watchdog_label(
				'premium_tickets',
				Base_LangCommon::ts('Premium_Projects_Tickets','Tickets'),
				$rid,
				$events,
				array('Premium_Projects_TicketsCommon','watchdog_label_format'),
				$details
			);
	}
	public static function watchdog_label_format($r) {
		return $r['ticket_id'].': '.$r['title'];
	}
	public static function access_ticket($action, $param=null) {
		switch ($action) {
			case 'browse_crits':	$me = CRM_ContactsCommon::get_my_record();
									return array('(!permission'=>2, '|assigned_to'=>$me['id'], '|:Created_by'=>Acl::get_user());
			case 'browse':	return true;
			case 'view':	$me = CRM_ContactsCommon::get_my_record();
							return ($param['permission']!=2 || $param['assigned_to']==$me['id'] || $param['Created_by']==Acl::get_user());
			case 'add':		return true;
			case 'edit':	$me = CRM_ContactsCommon::get_my_record();
							if ($param['permission']==0 ||
								in_array($me['id'],$param['assigned_to']) ||
								Acl::get_user()==$param['created_by']) return true;
							return false;
			case 'delete':	$me = CRM_ContactsCommon::get_my_record();
							if ($me['login']==$param['created_by']) return true;
							return false;
		}
		return false;
	}
	
	public static function generate_id($id) {
		if (is_array($id)) $id = $id['id'];
		return '#'.str_pad($id, 4, '0', STR_PAD_LEFT);
	}

	public static function display_status($record, $nolink, $desc, $status = array()) {
		$v = $record['status'];
		if (!$v) $v = 0;
		if (empty($status)) $status = Utils_CommonDataCommon::get_translated_array('Premium_Ticket_Status');
		if ($nolink) return $status[$v];
		$leightbox_ready = & CRM_FollowupCommon::$leightbox_ready;
		$prefix = 'premium_ticket_status';
		CRM_FollowupCommon::check_location();
		if (!isset($leightbox_ready[$prefix])) {
			$leightbox_ready[$prefix] = true;

			$theme = Base_ThemeCommon::init_smarty();
			eval_js_once($prefix.'_followups_deactivate = function(){leightbox_deactivate(\''.$prefix.'_followups_leightbox\');}');
	
			$error=Base_LangCommon::ts('Premium_Projects_Tickets','Resolution is required when marking ticket as closed or resolved.');
			eval_js('check_if_'.$prefix.'_set_resolution = function() {not_resolved=$(\''.$prefix.'_resolution_select\').options[0].selected;if(not_resolved)$(\''.$prefix.'_resolution_required\').innerHTML=\''.$error.'\';return !not_resolved;}');
			$theme->assign('resolution_required_error','<div id="'.$prefix.'_resolution_required"></div>');
			$theme->assign('reopen',array('open'=>'<a id="'.$prefix.'_feedback" onclick="'.$prefix.'_followups_deactivate();'.$prefix.'_set_action(\'reopen\');'.$prefix.'_submit_form();">','text'=>Base_LangCommon::ts('Premium/Projects/Tickets', 'Reopen'),'close'=>'</a>'));
			$theme->assign('feedback',array('open'=>'<a id="'.$prefix.'_feedback" onclick="'.$prefix.'_followups_deactivate();'.$prefix.'_set_action(\'feedback\');'.$prefix.'_submit_form();">','text'=>Base_LangCommon::ts('Premium/Projects/Tickets', 'Need Feedback'),'close'=>'</a>'));
			$theme->assign('close',array('open'=>'<a id="'.$prefix.'_close" onclick="if(check_if_'.$prefix.'_set_resolution()){'.$prefix.'_followups_deactivate();'.$prefix.'_set_action(\'close\');'.$prefix.'_submit_form();}">','text'=>Base_LangCommon::ts('Premium/Projects/Tickets', 'Close'),'close'=>'</a>'));			
			$theme->assign('resolve',array('open'=>'<a id="'.$prefix.'_resolve" onclick="if(check_if_'.$prefix.'_set_resolution()){'.$prefix.'_followups_deactivate();'.$prefix.'_set_action(\'resolve\');'.$prefix.'_submit_form();}">','text'=>Base_LangCommon::ts('Premium/Projects/Tickets', 'Resolved'),'close'=>'</a>'));

			eval_js($prefix.'_submit_form = function () {'.
						'$(\''.$prefix.'_follow_up_form\').submited.value=1;Epesi.href($(\''.$prefix.'_follow_up_form\').serialize(), \'processing...\');$(\''.$prefix.'_follow_up_form\').submited.value=0;'.
					'}');
			eval_js($prefix.'_set_action = function (arg) {'.
						'document.forms["'.$prefix.'_follow_up_form"].action.value = arg;'.
					'}');
			eval_js($prefix.'_set_id = function (id) {'.
						'document.forms["'.$prefix.'_follow_up_form"].id.value = id;'.
						'document.forms["'.$prefix.'_follow_up_form"].note.value = "";'.
						'document.forms["'.$prefix.'_follow_up_form"].resolution.selectedIndex = 0;'.
					'}');
			$theme->assign('form_open','<form id="'.$prefix.'_follow_up_form" name="'.$prefix.'_follow_up_form" method="POST">'.
							'<input type="hidden" name="submited" value="0" />'.
							'<input type="hidden" name="form_name" value="'.$prefix.'_follow_up_form" />'.
							'<input type="hidden" name="id" value="" />'.
							'<input type="hidden" name="action" value="" />');
			$theme->assign('form_note',	array('label'=>Base_LangCommon::ts('Premium/Projects/Tickets','Note'), 'html'=>'<textarea name="note" value=""></textarea>'));
			$resolution_html = '<select name="resolution" value="0" id="'.$prefix.'_resolution_select">';
			$resolution_html .= '<option value="">---</option>';
			$ress = Utils_CommonDataCommon::get_translated_array('Premium_Ticket_Resolution', true);
			foreach ($ress as $k=>$v)
				$resolution_html .= '<option value="'.$k.'">'.$v.'</option>';
			$resolution_html .= '</select>';
			$theme->assign('form_resolution', array('label'=>Base_LangCommon::ts('Premium/Projects/Tickets','Resolution'), 'html'=>$resolution_html));
			$theme->assign('form_close','</form>');
			ob_start();
			Base_ThemeCommon::display_smarty($theme,'Premium_Projects_Tickets','status_leightbox');
			$profiles_out = ob_get_clean();

			Libs_LeightboxCommon::display($prefix.'_followups_leightbox',$profiles_out,Base_LangCommon::ts('Premium/Projects/Tickets', 'Change Status'));
		}
		if (!self::access_ticket('edit', $record) && !Base_AclCommon::i_am_admin()) return $v;
		$v = $record['status'];
		if ($v==4) return $status[$v];
		if (isset($_REQUEST['form_name']) && $_REQUEST['form_name']==$prefix.'_follow_up_form' && $_REQUEST['id']==$record['id']) {
			unset($_REQUEST['form_name']);
			$note = $_REQUEST['note'];
			$resolution = $_REQUEST['resolution'];
			$action  = $_REQUEST['action'];
			switch ($action) {
				case 'reopen': 	$v=0;
								$resolution = '';
								break;
				case 'set_next_stage': $v++; break;
				case 'resolve': $v=2; break;
				case 'feedback': $v=3; break;
				case 'close': $v=4; break;
			}
			if ($note) {
				if (get_magic_quotes_gpc())
					$note = stripslashes($note);
				$note = str_replace("\n",'<br />',$note);
				Utils_AttachmentCommon::add('Premium/Projects/Tickets'.$record['id'],0,Acl::get_user(),$note);
			}
			Utils_RecordBrowserCommon::update_record('premium_tickets', $record['id'], array('status'=>$v,'resolution'=>$resolution));
			location(array());
		}
		if ($v<=0) {
			return '<a href="javascript:void(0)" onclick="'.$prefix.'_set_action(\'set_next_stage\');'.$prefix.'_set_id(\''.$record['id'].'\');'.$prefix.'_submit_form();">'.$status[$v].'</a>';
		}
		return '<a href="javascript:void(0)" class="lbOn" rel="'.$prefix.'_followups_leightbox" onMouseDown="'.$prefix.'_set_id('.$record['id'].');">'.$status[$v].'</a>';
	}

	public static function display_ticket_id($r, $nolink) {
		return Utils_RecordBrowserCommon::create_linked_label_r('premium_tickets', 'ticket_id', $r, $nolink);	
	}

	public static function display_status_applet($record, $nolink=false, $desc=array()) {
		$status = Utils_CommonDataCommon::get_translated_array('Premium_Ticket_Status');
		$status[3] = Base_LangCommon::ts('Premium_Projects_Tickets','Feedback');
		return self::display_status($record, $nolink, $desc, $status);
	}

	public static function display_title($record, $nolink) {
		$ret = array();
		$blocked = array();
		sort($record['required_tickets']);
		$icon = null;
		foreach ($record['required_tickets'] as $v) {
			$status = Utils_RecordBrowserCommon::get_value('premium_tickets',$v,'status');
			if (!$icon) $icon = 'unblocked';
			if ($status!=4 && Utils_RecordBrowserCommon::is_active('premium_tickets',$v)) {
				if ((!$icon || $icon=='unblocked') && $status==2) $icon = 'blocked-free';
				if ((!$icon || $icon=='unblocked' || $icon=='blocked-free') && $status!=2) $icon = 'blocked';
				$blocked[] = ($status==2?'<b>'.Base_LangCommon::ts('Premium_Projects_Tickets','[Resolved]').'</b> ':'').self::generate_id($v).': '.Utils_RecordBrowserCommon::get_value('premium_tickets',$v,'title');
			}
		}
		asort($blocked);
		if (!empty($blocked))
			array_unshift($blocked, '<b>'.Base_LangCommon::ts('Premium_Projects_Tickets','Blocked due to following tickets:').'</b>');
		$blocking_id = '';
		if ((!$icon || $icon=='unblocked') && $record['status']!=4) {
			$blocking_id = Utils_RecordBrowserCommon::get_id('premium_tickets','required_tickets',$record['id']);
			if ($blocking_id) {
				$icon = 'blocking';
				array_unshift($blocked, '<b>'.Base_LangCommon::ts('Premium_Projects_Tickets','Blocks ticket ').'</b>'.self::generate_id($blocking_id));
			}
		}
		$ret = Utils_RecordBrowserCommon::create_linked_label_r('premium_tickets', 'title', $record, $nolink);
		if (isset($record['description']) && $record['description']!='') $ret = '<span '.Utils_TooltipCommon::open_tag_attrs($record['description'], false).'>'.$ret.'</span>';
		if ($icon) {
			$icon = '<img src="'.Base_ThemeCommon::get_template_file('Premium_Projects_Tickets',$icon.'.png').'">';
			if (!empty($blocked))
			$icon = '<span '.Utils_TooltipCommon::open_tag_attrs(implode('<br>',$blocked), false).'>'.
					$icon.
					'</span>';
			$ret = $icon.$ret;
		}
		return $ret;
	}

	public static function display_required_tickets($r, $nolink){
		$ret = array();
		sort($r['required_tickets']);
		foreach ($r['required_tickets'] as $v) {
			$rr = Utils_RecordBrowserCommon::get_record('premium_tickets',$v);
			if ($rr['active']) {
				$str = Utils_RecordBrowserCommon::create_linked_label('premium_tickets','ticket_id',$v, $nolink);
				$note = self::applet_info_format($rr);
				$str = '<span '.Utils_TooltipCommon::open_tag_attrs($note['notes'], false).'>'.$str.'</span>';
				if ($rr['status']==4) $str = '<del>'.$str.'</del>'; 
				$ret[] = $str;
			}
		}
		return implode(', ',$ret);
	}

	public static function proj_name_callback($v, $nolink) {
		return Utils_RecordBrowserCommon::create_linked_label('premium_projects', 'Project Name', $v['project_name'], $nolink);
	}

	public static function employees_crits(){
		return array('company_name'=>array(CRM_ContactsCommon::get_main_company()),'group'=>array('office'));
	}
   
	public static function assigned_to_format($record, $nolink=false){
		$ret = '<span id="contact_confirmed_'.$record['id'].'"></span>';
		if (!$nolink) $ret .= Utils_RecordBrowserCommon::record_link_open_tag('contact', $record['id']);
		$ret .= $record['last_name'].(($record['first_name']!=='')?' '.$record['first_name']:'');
		if (!$nolink) $ret .= Utils_RecordBrowserCommon::record_link_close_tag();
		return $ret;
	}

	public static function display_title_with_mark($record) {
		$me = CRM_ContactsCommon::get_my_record();
		$ret = self::display_title($record, false);
		if (!in_array($me['id'], $record['assigned_to'])) return $ret;
		return $ret;
	}
	
	public static function display_assigned_contacts($record,$nolink,$desc) {
		$icon_on = Base_ThemeCommon::get_template_file('images/active_on.png');
		$icon_off = Base_ThemeCommon::get_template_file('images/active_off.png');
		$icon_none = Base_ThemeCommon::get_template_file('images/active_off2.png');
		$v = $record[$desc['id']];
		$def = '';
		$first = true;
		$param = explode(';',$desc['param']);
		if ($param[1] == '::') $callback = array('CRM_ContactsCommon', 'contact_format_default');
		else $callback = explode('::', $param[1]);
		if (!is_array($v)) $v = array($v);
		foreach($v as $k=>$w){
			if ($w=='') break;
			if ($first) $first = false;
			else $def .= '<br>';
			$contact = CRM_ContactsCommon::get_contact($w);
			if (!$nolink) {
				if ($contact['login']=='') $icon = $icon_none;
				else {
					$icon = Utils_WatchdogCommon::user_check_if_notified($contact['login'],'premium_tickets',$record['id']);
					if ($icon===null) $icon = $icon_none;
					elseif ($icon===true) $icon = $icon_on;
					else $icon = $icon_off;
				}
				$def .= '<img src="'.$icon.'" />';
			}
			$def .= Utils_RecordBrowserCommon::no_wrap(call_user_func($callback, $contact, $nolink));
		}
		if (!$def) 	$def = '---';
		return $def;
	}
	
	public static function applet_info_format($r){

		$project=Utils_RecordBrowserCommon::get_value('premium_projects',$r['project_name'],'project_name');

		$args=array(
					'Ticket ID:' => '<b>'.$r['ticket_id'].'</b>',
					'Project:' => '<b>'.$project.'</b>',
					'Title:' => '<b>'.$r['title'].'</b>',
					'Description:' => $r['description'],
					'No. of Notes' => '<b>'.Utils_AttachmentCommon::count('Premium/Projects/Tickets'.$r['id']).'</b>',
					'Assigned to:' => CRM_ContactsCommon::display_contact(array('id'=>$r['assigned_to']),true,array('id'=>'id', 'param'=>'::;CRM_ContactsCommon::contact_format_no_company')),
					'Ticket Type:' => Utils_CommonDataCommon::get_value('Premium_Ticket_Type/'.$r['type'],true),
					'Priority:' => Utils_CommonDataCommon::get_value('Premium_Ticket_Priorities/'.$r['priority'],true),
					'Status:' => Utils_CommonDataCommon::get_value('Premium_Ticket_Status/'.$r['status'],true),
					'Resolution:' => Utils_CommonDataCommon::get_value('Premium_Ticket_Resolution/'.$r['resolution'],true),
					'Due Date:' => $r['due_date']!=''?
				Base_RegionalSettingsCommon::time2reg($r['due_date'],false):Base_LangCommon::ts('Premium/Projects/Tickets','Not set')
					);

		$bg_color = '';
		switch ($r['priority']) {
			case 3: $bg_color = '#FFFFFF'; break; // trivial
			case 2: $bg_color = '#D5FFD5'; break; // minor
			case 1: $bg_color = '#FFFFD5'; break; // major
			case 0: $bg_color = '#FFD5D5'; break; // critical
		}
		$ret = array('notes'=>Utils_TooltipCommon::format_info_tooltip($args,'Premium_Projects_Tickets'));
		if ($bg_color) $ret['row_attrs'] = 'style="background:'.$bg_color.';"';
		return $ret;
	}
	
	public static function projects_crits(){
		return array('!status'=>array(2,4));
	}
	
	public static function claiming_ticket($values) {
		$me = CRM_ContactsCommon::get_my_record();
		if (self::access_ticket('edit',$values) &&
			$me['id']!=-1) {
			$already_assigned = array_search($me['id'],$values['assigned_to']);
			Base_ActionBarCommon::add(($already_assigned!==false)?'delete':'add',($already_assigned!==false)?'Abandon ticket':'Claim ticket',Module::create_href(array('_claim_ticket'=>$values['id'])));
			if (isset($_REQUEST['_claim_ticket']) &&
				is_numeric($_REQUEST['_claim_ticket']) &&
				$_REQUEST['_claim_ticket']==$values['id']) {
					if ($already_assigned===false) {
						$values['assigned_to'][] = $me['id'];
						Utils_WatchdogCommon::subscribe('premium_tickets',$values['id']);
					} else unset($values['assigned_to'][$already_assigned]);
					Utils_RecordBrowserCommon::update_record('premium_tickets',$values['id'],array('assigned_to'=>$values['assigned_to']));
					location(array());
				}
		}
	}
	
	public static function subscribed_assigned($v) {
		if (!is_array($v)) return;
		foreach ($v['assigned_to'] as $k) {
			$user = Utils_RecordBrowserCommon::get_value('contact',$k,'Login');
			if ($user!==false && $user!==null) Utils_WatchdogCommon::user_subscribe($user, 'premium_tickets', $v['id']);
		}
	}

	public static function submit_ticket($values, $mode) {
		switch ($mode) {
		case 'add':
			if (!$values['due_date'])
				$values['due_date'] = Utils_RecordBrowserCommon::get_value('premium_projects',$values['project_name'],'due_date');
			return $values;
		case 'view':
			self::claiming_ticket($values);
			return $values;
		case 'edit':
			$values['ticket_id'] = self::generate_id($values['id']);
			$k = array_search($values['id'],$values['required_tickets']);
			if ($k!==false) unset($values['required_tickets'][$k]);
			$old_project = Utils_RecordBrowserCommon::get_value('premium_tickets',$values['id'],'project_name');
		case 'added':
			self::subscribed_assigned($values);
			if ($mode==='edit' && $old_project==$values['project_name']) break;
			Utils_RecordBrowserCommon::update_record('premium_tickets',$values['id'],array('ticket_id'=>self::generate_id($values['id'])), false, null, true);
			$subs = Utils_WatchdogCommon::get_subscribers('premium_projects',$values['project_name']);
			foreach($subs as $s)
				Utils_WatchdogCommon::user_subscribe($s, 'premium_tickets',$values['id']);
		}
		return $values;
	}
	
	public static function required_tickets_crits($arg, $r){
		if (isset($r['id'])) $crits = array('!id'=>$r['id']); 
		else $crits = array();
		if ($arg) return $crits;
		return Utils_RecordBrowserCommon::merge_crits(array('!status'=>4), $crits);
	}
	
	public static function display_ticket_short($r) {
		return $r['ticket_id'].': '.$r['title'];	
	}
	
	public static function adv_required_tickets_params(){
		return array(	'cols'=>array('project_name'=>false,'priority'=>false),
						'format_callback'=>array('Premium_Projects_TicketsCommon','display_ticket_short'));
	}
	
	public static function applet_settings() {
		$type = Utils_CommonDataCommon::get_translated_array('Premium_Ticket_Type');
		$status = Utils_CommonDataCommon::get_translated_array('Premium_Ticket_Status');
		$projects_recs = Utils_RecordBrowserCommon::get_records('premium_projects', array('!status'=>array(2,4)), array('project_name'));
		$projects = array();
		if (!empty($projects_recs))
			$projects[] = array('label'=>'Include Projects', 'name'=>'projects_header', 'type'=>'header');
		foreach ($projects_recs as $v)
			$projects[] = array('label'=>$v['project_name'], 'name'=>'project_'.$v['id'], 'type'=>'checkbox', 'default'=>true);
		return Utils_RecordBrowserCommon::applet_settings(array_merge(array(
			array('label'=>'Display my tickets','name'=>'mine','type'=>'checkbox','default'=>true),
			array('label'=>'Display others tickets','name'=>'others','type'=>'checkbox','default'=>false),
			array('label'=>'Display unassigned tickets','name'=>'unassigned','type'=>'checkbox','default'=>true),
			array('label'=>'Tickets type','name'=>'type','type'=>'select','default'=>'_all','values'=>array('_all'=>'[All]')+$type),
			array('label'=>'Tickets status','name'=>'status','type'=>'select','default'=>'_all','values'=>array('_all'=>'[All]', '_active'=>'[All except closed]', '_no_closed_resoloved'=>'[All except Closed and Resolved]')+$status),
			),$projects));
	}
	
	
	public static function search_format($id) {
		if(Acl::check('Premium_Projects','browse projects')) return false;
		$row = Utils_RecordBrowserCommon::get_records('premium_tickets',array('id'=>$id));
		if(!$row) return false;
		$row = array_pop($row);
		return Utils_RecordBrowserCommon::record_link_open_tag('premium_tickets', $row['id']).Base_LangCommon::ts('Premium_Projects_Tickets', 'Ticket (attachment) #%d, %s', array($row['ticket_id'], $row['project_name'])).Utils_RecordBrowserCommon::record_link_close_tag();
	}

	///////////////////////////////////
	// mobile devices

	public function mobile_menu() {
		if(!Acl::is_user())
			return array();
		return array('Tickets'=>'mobile_tickets');
	}
	
	public function mobile_tickets() {
		$me = CRM_ContactsCommon::get_my_record();
		Utils_RecordBrowserCommon::mobile_rb('premium_tickets',array_merge(array('assigned_to'=>array($me['id'])),Premium_Projects_TicketsCommon::active_tickets_crits()),array('status'=>'ASC', 'priority'=>'ASC', 'title'=>'ASC'),array('status'=>1,'priority'=>1));
	}
}

?>
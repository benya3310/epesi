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

class Premium_Projects extends Module {
	private $rb;

public function body() {
		// premium_projects=recordset, premium_projects_module=internal unique name for RB
		$this->rb = $this->init_module('Utils/RecordBrowser','premium_projects','premium_projects_module');
		// set defaults
		$sts = Utils_CommonDataCommon::get_translated_array('Premium_Projects_Status');
		$trans = array('__NULL__'=>array(), '__ALLACTIVE__'=>array('!status'=>array(0,2,4,5)));
		foreach ($sts as $k=>$v)
			$trans[$k] = array('status'=>$k);
		$me = CRM_ContactsCommon::get_my_record();
		$this->rb->set_custom_filter('status',array('type'=>'select','label'=>$this->t('Projects status'),'args'=>array('__NULL__'=>'['.$this->t('All').']','__ALLACTIVE__'=>'['.$this->t('All active').']')+$sts,'trans'=>$trans));
		$this->rb->set_defaults(array('project_manager'=>$me['id'], 'start_date'=>date('Y-m-d')));
		$this->rb->set_filters_defaults(array('status'=>'All'));
		$this->rb->set_default_order(array('project_name'=>'ASC'));		
		$this->rb->set_cut_lengths(array('project_name'=>30,'company_name'=>30));
		$this->display_module($this->rb);
	}

public function applet($conf,$opts) {
		$opts['go'] = true; // enable full screen
		$sts = Utils_CommonDataCommon::get_translated_array('Premium_Projects_Status');
		$rb = $this->init_module('Utils/RecordBrowser','premium_projects','premium_projects');
		$limit = null;
		$crits = array();
		$me = CRM_ContactsCommon::get_my_record();
		if ($conf['status']=='__ALL__') {
			$opts['title'] = Base_LangCommon::ts('Premium/Projects','All Projects');
		} elseif ($conf['status']=='__NULL__') {
			$opts['title'] = Base_LangCommon::ts('Premium/Projects','Active projects');
			$crits['!status'] = array(2,4);
		} else {
			$projstatus = $sts[$conf['status']];
			$opts['title'] = Base_LangCommon::ts('Premium/Projects','Projects: %s',array($projstatus));
			$crits['status'] = $conf['status'];
		}
		if ($conf['my']==1) {
			$crits['project_manager'] = array($me['id']);
		}

		// $conds - parameters for the applet
		// 1st - table field names, width, truncate
		// 2nd - criteria (filter)
		// 3rd - sorting
		// 4th - function to return tooltip
		// 5th - limit how many records are returned, null = no limit
		// 6th - Actions icons - default are view + info (with tooltip)
		
		$sorting = array('project_name'=>'ASC');
		$cols = array(
							array('field'=>'project_name', 'width'=>10, 'cut'=>18),
							array('field'=>'company_name', 'width'=>10, 'cut'=>18)
										);

		$conds = array(
									$cols,
									$crits,
									$sorting,
									array('Premium_ProjectsCommon','applet_info_format'),
									$limit,
									$conf,
									& $opts
				);
		$opts['actions'][] = Utils_RecordBrowserCommon::applet_new_record_button('premium_projects',array('project_manager'=>$me['id'], 'start_date'=>date('Y-m-d')));
		$this->display_module($rb, $conds, 'mini_view');
	}

public function premium_projects_attachment_addon($arg){
		$a = $this->init_module('Utils/Attachment',array('Premium/Projects/'.$arg['id']));
		$a->set_view_func(array('Premium_ProjectsCommon','search_format'),array($arg['id']));
		$a->additional_header('Project: '.$arg['project_name']); // Field is 'Project Name' but it is converted to lowercase and spec replaced with '_'
		$a->enable_watchdog('premium_projects',$arg['id']);
		$a->allow_protected($this->acl_check('view protected notes'),$this->acl_check('edit protected notes'));
		$a->allow_public($this->acl_check('view public notes'),$this->acl_check('edit public notes'));
		$this->display_module($a);
	}

public function company_premium_projects_addon($arg){
		$rb = $this->init_module('Utils/RecordBrowser','premium_projects');
		$proj = array(array('company_name'=>$arg['id']), array('company_name'=>false), array('Fav'=>'DESC'));
		$this->display_module($rb,$proj,'show_data');
	}

public function caption(){
		if (isset($this->rb)) return $this->rb->caption();
	}
}

?>
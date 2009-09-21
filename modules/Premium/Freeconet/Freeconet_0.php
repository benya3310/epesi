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

class Premium_Freeconet extends Module {
	private $name;

	public function construct() {
		$this->name = md5($this->get_path());
	}
	
	public function body() {
		set_time_limit(0);

		$login = Base_User_SettingsCommon::get('Premium_Freeconet','login');
		if($login=='') {
			print($this->t('Please fill your login and password in control panel.'));
			return;
		}

		$t = time();
		$start = & $this->get_module_variable('agenda_start',date('Y-m-d', $t - (7 * 24 * 60 * 60)));
		$end = & $this->get_module_variable('agenda_end',date('Y-m-d',$t));
		$calldir = & $this->get_module_variable('calldir','OUT');
		$ismissed = & $this->get_module_variable('ismissed','no');
		$offset = & $this->get_module_variable('offset',0);
		
		$form = $this->init_module('Libs/QuickForm');
		$theme =  $this->pack_module('Base/Theme');
		
		$form->addElement('select','calldir',$this->t('Call direction'),array('IN'=>$this->ht('in'),'OUT'=>$this->ht('out')));
		$form->addElement('select','ismissed',$this->t('Missed calls'),array('yes'=>$this->ht('Yes'),'no'=>$this->ht('No')));
		
		$form->addElement('datepicker', 'start', $this->t('From'));
		$form->addElement('datepicker', 'end', $this->t('To'));
		$form->addElement('submit', 'submit_button', $this->ht('Show'));
		$form->addRule('start', 'Field required', 'required');
		$form->addRule('end', 'Field required', 'required');
		$form->setDefaults(array('calldir'=>$calldir, 'start'=>$start, 'end'=>$end, 'ismissed'=>$ismissed));

		if($form->validate()) {
			$data = $form->exportValues();
			$start = $data['start'];
			$end = $data['end'];
			$end = date('Y-m-d',strtotime($end));
			$ismissed = $data['ismissed'];
			if($data['calldir'] == 'IN' || $data['calldir'] == 'OUT')
				$calldir = $data['calldir'];
			$offset = 0;
		}

		$form->assign_theme('form', $theme);


		$account_dir = ($calldir=='IN')?array('toAccount'=>$login):array('fromAccount'=>$login);
		$billing_opts = array('callDir'=>$calldir,'isMissed'=>($ismissed=='yes')?'true':'false','fromDate'=>strtotime($start),'toDate'=>strtotime($end)+86400)+$account_dir;
		$count = Premium_FreeconetCommon::call_freeconet_func('getBillingCount',$billing_opts);
		if($count===false) return;

		$m = & $this->init_module('Utils/GenericBrowser',null,'billing');
 		$m->set_table_columns(array(
							  array('name'=>'From','width'=>20,'order'=>'fromNumber'),
							  array('name'=>'To','width'=>20,'order'=>'toNumber'),
							  array('name'=>'Start','width'=>20,'order'=>'startDate'),
							  array('name'=>'Duration','width'=>20,'order'=>'callDuration'),
							  array('name'=>'Cost','width'=>20,'order'=>'callCost')
							  ));
		$m->set_default_order(array('Start'=>'ASC'));
		$limits = $m->get_limit($count->noOfRows);
		$order = $m->get_order();
		$billing = Premium_FreeconetCommon::call_freeconet_func('getBilling',$billing_opts+array('columnList'=>'fromNumber,toNumber,startDate,callDuration,callCost','offRow'=>(string)($limits['offset']+1),'noOfRows'=>$limits['numrows'],'orderDesc'=>$order[0]['direction']=='ASC'?'false':'true', 'orderBy'=>$order[0]['order']),array('Premium_FreeconetCommon','handle_errors'),true);
		$billing_data = explode("\n",$billing->billingData);
		foreach($billing_data as $row) {
			if(!trim($row)) continue;
			$data = explode('|',$row);
			
			$from_rec = CRM_ContactsCommon::get_contacts(array('|work_phone'=>$data[0],'|mobile_phone'=>$data[0],'|home_phone'=>$data[0]));
			foreach($from_rec as &$rec)
				$rec = CRM_ContactsCommon::contact_format_default($rec);
			$from_rec_comp = CRM_ContactsCommon::get_companies(array('phone'=>$data[0]));
			foreach($from_rec_comp as $rec)
				$from_rec[] = Utils_RecordBrowserCommon::create_linked_label('company', 'Company Name', $rec);
			
			$to_rec = CRM_ContactsCommon::get_contacts(array('|work_phone'=>$data[1],'|mobile_phone'=>$data[1],'|home_phone'=>$data[1]));
			foreach($to_rec as &$rec)
				$rec = CRM_ContactsCommon::contact_format_default($rec);
			$to_rec_comp = CRM_ContactsCommon::get_companies(array('phone'=>$data[1]));
			foreach($to_rec_comp as $rec)
				$to_rec[] = Utils_RecordBrowserCommon::create_linked_label('company', 'Company Name', $rec);
	 		$m->add_row(CRM_CommonCommon::get_dial_code($data[0]).(empty($from_rec)?'':' ('.implode(', ',$from_rec).')'),
				    CRM_CommonCommon::get_dial_code($data[1]).(empty($to_rec)?'':' ('.implode(', ',$to_rec).')'),
				    Base_RegionalSettingsCommon::time2reg($data[2]),
				    Base_RegionalSettingsCommon::seconds_to_words($data[3],true,true),
				    $data[4]);
		}
 		$theme->assign('billing',$this->get_html_of_module($m));

		$theme->display();
	}

	public function applet($conf,$opts) {
		$opts['title'] = $this->t("Freeconet status");
		$opts['go'] = true;

		//div for updating
		print('<div id="freeconet_'.$this->name.'" style="width: 270px; padding: 5px 5px 5px 20px;">'.$this->t('Loading data...').'</div>');

		//get rss now!
		eval_js('Freeconet.init(\''.$this->name.'\')');

	}
}

?>
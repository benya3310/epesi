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

class Premium_FreeconetCommon extends ModuleCommon {
	public static function applet_caption() {
		return "Freeconet account info";
	}

	public static function applet_info() {
		$html="Displays available credits or for postpaid users: debit and credit limit.";
		return $html;
	}

	public static function user_settings() {
		if(Acl::is_user()) {
			return array(
				'Freeconet'=>array(
					array('name'=>'login','label'=>'Login', 'type'=>'text', 'default'=>''),
					array('name'=>'pass','label'=>'Password', 'type'=>'password', 'default'=>''),
					array('name'=>'fax_login','label'=>'Fax Login', 'type'=>'text', 'default'=>''),
					array('name'=>'fax_pass','label'=>'Fax Password', 'type'=>'password', 'default'=>'')
				)
			);
		}
		return array();
	}
	
	private static $url = 'https://apiuser.freeconet.pl/RestAPI/V2/execute?';
	
	public static function handle_errors($n,$func) {
		if(isset($n->errors)) {
			print(Base_LangCommon::ts('Premium/Freeconet','Errors').':<br><ul>');
			foreach($n->errors->children() as $k=>$v) {
				if($k!='error') continue;
				print('<li>'.Base_LangCommon::ts('Premium/Freeconet',(string)$v->msg).'</li>');
			}
			print('</ul>');
			return false;
		}
		if(isset($n->$func->errors)) {
			print(Base_LangCommon::ts('Premium/Freeconet','Errors').':<br><ul>');
			foreach($n->$func->errors->children() as $k=>$v) {
				if($k!='error') continue;
				print('<li>'.Base_LangCommon::ts('Premium/Freeconet',(string)$v->msg).'</li>');
			}
			print('</ul>');
			return false;
		}
		return true;
	}

    private static function uncdata($xml)
    {
        // States:
        //
        //     'out'
        //     '<'
        //     '<!'
        //     '<!['
        //     '<![C'
        //     '<![CD'
        //     '<![CDAT'
        //     '<![CDATA'
        //     'in'
        //     ']'
        //     ']]'
        //
        // (Yes, the states a represented by strings.) 
        //

        $state = 'out';

        $a = str_split($xml);

        $new_xml = '';

        foreach ($a AS $k => $v) {

            // Deal with "state".
            switch ( $state ) {
                case 'out':
                    if ( '<' == $v ) {
                        $state = $v;
                    } else {
                        $new_xml .= $v;
                    }
                break;

                case '<':
                    if ( '!' == $v  ) {
                        $state = $state . $v;
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                break;

                 case '<!':
                    if ( '[' == $v  ) {
                        $state = $state . $v;
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                break;

                case '<![':
                    if ( 'C' == $v  ) {
                        $state = $state . $v;
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                break;

                case '<![C':
                    if ( 'D' == $v  ) {
                        $state = $state . $v;
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                break;

                case '<![CD':
                    if ( 'A' == $v  ) {
                        $state = $state . $v;
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                break;

                case '<![CDA':
                    if ( 'T' == $v  ) {
                        $state = $state . $v;
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                break;

                case '<![CDAT':
                    if ( 'A' == $v  ) {
                        $state = $state . $v;
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                break;

                case '<![CDATA':
                    if ( '[' == $v  ) {


                        $cdata = '';
                        $state = 'in';
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                break;

                case 'in':
                    if ( ']' == $v ) {
                        $state = $v;
                    } else {
                        $cdata .= $v;
                    }
                break;

                case ']':
                    if (  ']' == $v  ) {
                        $state = $state . $v;
                    } else {
                        $cdata .= $state . $v;
                        $state = 'in';
                    }
                break;

                case ']]':
                    if (  '>' == $v  ) {
                        $new_xml .= str_replace('>','&gt;',
                                    str_replace('>','&lt;',
                                    str_replace('"','&quot;',
                                    str_replace('&','&amp;',
                                    $cdata))));
                        $state = 'out';
                    } else {
                        $cdata .= $state . $v;
                        $state = 'in';
                    }
                break;
            } // switch

        }

        //
        // Return.
        //
            return $new_xml;

    }
    
	public static function get_url($url) {
	    if(function_exists('curl_init')) {
		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_VERBOSE, 0);
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($ch, CURLOPT_HEADER, 0);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		    $response = curl_exec($ch);
		    if($response===false) Epesi::alert(curl_error($ch));
		    curl_close($ch);
		    return $response;
	    }
	    return @file_get_contents($url);
        }
    
        public static function get_freeconet_token($errors=null,$type='USER') {
		if(!isset($errors))
			$errors = array('Premium_FreeconetCommon','handle_errors');
		if($type=='USER') {
			$login = Base_User_SettingsCommon::get('Premium_Freeconet','login');
			$pass = Base_User_SettingsCommon::get('Premium_Freeconet','pass');
		} elseif($type=='FAXV') {
			$login = Base_User_SettingsCommon::get('Premium_Freeconet','fax_login');
			$pass = Base_User_SettingsCommon::get('Premium_Freeconet','fax_pass');
		}

		if(!isset($_SESSION['freeconet'][$login][$pass][$type]) || $_SESSION['freeconet'][$login][$pass][$type]['time']+1000-100<time()) { //100 is tolerance, yes its big
			$url = self::$url.http_build_query(array(
				'fun'=>'getToken',
				'userName'=>$login,
				'password'=>$pass,
				'authSystem'=>$type,
				'validPeriod'=>'10000'));
		
			$token = self::get_url($url);
			if(!$token) {
				Epesi::alert('Freeconet authorization failed: invalid login and password, or php url fopen not installed');
				return false;
			}
			$token = simplexml_load_string($token);
			if(is_callable($errors)) {
			        if(!call_user_func($errors,$token,'getToken')) return false;
			}
			$token = (string)$token->getToken->tokenStr; //ok, here we have our token
			$_SESSION['freeconet'][$login][$pass][$type] = array('token'=>$token, 'time'=>time());
		} else {
			$token = $_SESSION['freeconet'][$login][$pass][$type]['token'];
		}
		return $token;
	}
	
	public static function sig_freeconet_func(array & $op,$type='USER') {
		if($type=='USER')
			$pass = Base_User_SettingsCommon::get('Premium_Freeconet','pass');
		elseif($type=='FAXV')
			$pass = Base_User_SettingsCommon::get('Premium_Freeconet','fax_pass');

		$sig = '';
		foreach($op as $k=>$v) $sig .= $k.$v;
		$sig .= md5($pass);
		$sig = md5($sig);
		$op['sig'] = $sig;
	}
	
	public static function call_freeconet_func($func,array $args=array(),$errors=null,$cdata=false,$type='USER') {
		if(!isset($errors)) $errors = array('Premium_FreeconetCommon','handle_errors');
		$token = self::get_freeconet_token($errors,$type);
		if(!$token) return false;

		$op = array(
			'fun'=>$func,
			'tokenStr'=>$token);
		$op += $args;
		self::sig_freeconet_func($op,$type);
		$url = self::$url.http_build_query($op);

		$op_call = self::get_url($url);
		if(!$op_call) return false;
		if($cdata)
			$op_call = self::uncdata($op_call);
		$op_call = simplexml_load_string($op_call);
		if(is_callable($errors)) {
			if(!call_user_func($errors,$op_call,$func)) return false;
		}
		return $op_call->$func;
	}
	
	public static function dialer_description() {
		return Base_LangCommon::ts('Premium_Freeconet','Freeconet SIP phone');
	}
	
	public static function dialer($tel) {
		return '<a href="javascript:Freeconet.call(\''.escapeJS($tel,false,true).'\')">'.$tel.'</a>';
	}
	
	public static function menu() {
		if(!Acl::is_user()) return array();
		return array('Freeconet billing'=>array());
	}
	
	public static function fax_provider($f=null) {
		$login = Base_User_SettingsCommon::get('Premium_Freeconet','login');
		$pass = Base_User_SettingsCommon::get('Premium_Freeconet','pass');
		$fax_login = Base_User_SettingsCommon::get('Premium_Freeconet','fax_login');
		$fax_pass = Base_User_SettingsCommon::get('Premium_Freeconet','fax_pass');
		if(isset($f) && filesize($f)>3*1024*1024) {
			return false;
		}
		if($login && $pass && $fax_login && $fax_pass) {
			$ret = array('name'=>'Freeconet','get_queue_func'=>'get_queue', 
							'get_queue_count_func'=>'get_queue_count', 
							'queue_statuses'=>array('WAITING'=>'Waiting', 'STARTED'=>'Started', 'CANCELED'=>'Canceled', 'ALL'=>'All'),
							'get_received_func'=>'get_received',
							'get_received_count_func'=>'get_received_count',
							'get_sent_func'=>'get_sent', 
							'get_sent_count_func'=>'get_sent_count',
							'sent_statuses'=>array('ENDED'=>'Ended','ERROR'=>'Error','CANCELED'=>'Canceled','ALL'=>'All'));
			if(function_exists('curl_init'))
				$ret['send_func']='fax_file';
			return $ret;
		}
		return false;
	}
	
	public static function get_received_count($from,$to) {
		$opts = array('fromDate'=>strtotime($from),'toDate'=>strtotime($to));
		$count = Premium_FreeconetCommon::call_freeconet_func('getFaxReceivedCount',$opts,null,false,'FAXV');
		if(!isset($count->noOfRows)) return false;
		return (string)$count->noOfRows;
	}
	
	public static function get_received($from,$to,$order_by,$order_direction, $no_of_rows, $offset) {
		$opts = array('fromDate'=>strtotime($from),'toDate'=>strtotime($to),
				'orderBy'=>$order_by,'orderDesc'=>($order_direction=='DESC')?'true':'false',
				'noOfRows'=>$no_of_rows,'offRow'=>$offset,
				'columnList'=>'fromNumber,toNumber,receivedDate,fileUrl');
		$tmp = Premium_FreeconetCommon::call_freeconet_func('getFaxReceived',$opts,null,true,'FAXV');
		if(!$tmp) return false;
		$ret = array();
		$cols = explode(',',$tmp->columnList);
		$tmp = explode("\n",$tmp->faxData);
		foreach($tmp as $row) {
			if(!trim($row)) continue;
			$data = explode('|',$row);
			$row2 = array();
			foreach($cols as $kk=>$c)
				$row2[$c] = $data[$kk];
			$ret[] = $row2;
		}
		return $ret;
	
	}
	
	public static function get_sent_count($from,$to,$status) {
		$opts = array('fromDate'=>strtotime($from),'toDate'=>strtotime($to),'faxStatus'=>$status);
		$count = Premium_FreeconetCommon::call_freeconet_func('getFaxSentCount',$opts,null,false,'FAXV');
		if(!isset($count->noOfRows)) return false;
		return (string)$count->noOfRows;
	}
	
	public static function get_sent($from,$to,$status,$order_by,$order_direction, $no_of_rows, $offset) {
		$opts = array('fromDate'=>strtotime($from),'toDate'=>strtotime($to),'faxStatus'=>$status,
				'orderBy'=>$order_by,'orderDesc'=>($order_direction=='DESC')?'true':'false',
				'noOfRows'=>$no_of_rows,'offRow'=>$offset,
				'columnList'=>'toNumber,faxStatus,faxStatusDetails,sentDate,noPages,sentCost,fileUrl,fileName');
		$tmp = Premium_FreeconetCommon::call_freeconet_func('getFaxSent',$opts,null,true,'FAXV');
		if(!$tmp) return false;
		$ret = array();
		$cols = explode(',',$tmp->columnList);
		$tmp = explode("\n",$tmp->faxData);
		foreach($tmp as $row) {
			if(!trim($row)) continue;
			$data = explode('|',$row);
			$row2 = array();
			foreach($cols as $kk=>$c)
				$row2[$c] = $data[$kk];
			$ret[] = $row2;
		}
		return $ret;
	}
	
	public static function get_queue_count($status) {
		$opts = array('faxStatus'=>$status);
		$count = Premium_FreeconetCommon::call_freeconet_func('getFaxCurrentCount',$opts,null,false,'FAXV');
		if(!isset($count->noOfRows)) return false;
		return (string)$count->noOfRows;
	}

	public static function get_queue($status,$order_by,$order_direction, $no_of_rows, $offset) {
		$opts = array('faxStatus'=>$status,
				'orderBy'=>$order_by,'orderDesc'=>($order_direction=='DESC')?'true':'false',
				'noOfRows'=>$no_of_rows,'offRow'=>$offset,
				'columnList'=>'toNumber,faxStatus,creationDate,fileUrl,fileName');
		$tmp = Premium_FreeconetCommon::call_freeconet_func('getFaxCurrent',$opts,null,true,'FAXV');
		if(!$tmp) return false;
		$ret = array();
		$cols = explode(',',$tmp->columnList);
		$tmp = explode("\n",$tmp->faxData);
		foreach($tmp as $row) {
			if(!trim($row)) continue;
			$data = explode('|',$row);
			$row2 = array();
			foreach($cols as $kk=>$c)
				$row2[$c] = $data[$kk];
			$ret[] = $row2;
		}
		return $ret;
	
	}

	public static function fax_file($tmp,$numbers) {
		$login = Base_User_SettingsCommon::get('Premium_Freeconet','fax_login');
		$pass = Base_User_SettingsCommon::get('Premium_Freeconet','fax_pass');
		if(!preg_match('/\.(pdf|tiff|tif)$/i',$tmp)) {
			Epesi::alert(Base_LangCommon::ts('Premium/Freeconet','Freeconet fax accepts pdf and tiff files only.'));
			return false;
		}
		$token = self::get_freeconet_token();
		if(!$token) return false;

		$op = array(
			'fun'=>'uploadFile',
			'tokenStr'=>$token);
		$url = self::$url.http_build_query($op);

		$params = array(
	    	    'fileDocument'=>"@".$tmp,
		);		
			
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$response = curl_exec($ch);
		curl_close($ch);

		if(!$response) {
			Epesi::alert('Unable to connect to freeconet server');
			return false;
		}

		$response = simplexml_load_string($response);
		ob_start();
		if(!self::handle_errors($response,'uploadFile')) {
			Epesi::alert(strip_tags(ob_get_clean()));
			return false;
		}
		ob_end_clean();
		
		$doc = (string)$response->uploadFile->fileDocumentId;

		$args = array(
			'fileDocumentId'=>$doc,
			'numberList'=>implode(',',$numbers),
			'campaignName'=>'epesi');
		if(!self::call_freeconet_func('sendFax',$args,null,false,'FAXV')) {
			Epesi::alert(Base_LangCommon::ts('Premium/Freeconet','Unable to send fax.'));
			return false;
		}

		Epesi::alert(Base_LangCommon::ts('Premium/Freeconet','Fax queued.'));
		return true;
	}
}

load_js('modules/Premium/Freeconet/utils.js');

?>

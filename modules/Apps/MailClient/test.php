<?php
ini_set('include_path',dirname(__FILE__).'/PEAR'.PATH_SEPARATOR.ini_get('include_path'));

$host = 'imap.gmail.com';
$port = '993';
$ssl = true;
$user = 'shacky7';
$pass = 'newqrwea';
$method = null;
$ref = '{'.$host.':'.$port.'/imap/novalidate-cert'.($ssl?'/ssl':'').'}';
$in = @imap_open($ref, $user,$pass, OP_HALFOPEN);
//$in = @imap_open($ref, $user,$pass);
if(!$in) {
	die('(connect error) '.implode(', ',imap_errors()));
}

//$new_name = "Trash";
//$iname = $ref.$new_name;
$iname = mb_convert_encoding( '{imap.gmail.com:993/imap/novalidate-cert/ssl}[Gmail]', "UTF7-IMAP", "UTF-8" );


//print($iname);
//imap_createmailbox($in,$iname);
//imap_subscribe($in,$iname);

//print_r(imap_getsubscribed($in,$ref,'*'));

//imap_unsubscribe($in,$iname);
//imap_deletemailbox($in,$iname);
//print_r(imap_getmailboxes($in,$ref,'*'));
print(imap_utf7_encode($iname));
imap_reopen($in,$iname) || die('dupa1');
$err = imap_errors();
print_r($err);
$st = imap_status($in,$iname,SA_UIDNEXT);
$last_uid = $st->uidnext-1;
print_r($st);
$l=imap_fetch_overview($in,'0:'.$last_uid,FT_UID); //list of new messages
print_r($l);
die("\n");
//var_dump($st);
//$msgCount = $st->uidnext;
//$l=imap_fetch_overview($in,'1:'.$msgCount,FT_UID);
//imap_reopen($in,$ref) || die('dupa2');
//print_r($l);

//imap_append($in, $iname, file_get_contents('mail'));

?>

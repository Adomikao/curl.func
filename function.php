<?php

/** 去除转义字符 */
function stripslashes_deep($value) {
	if (is_array($value)) {
		$value = array_map('stripslashes_deep', $value);
	} elseif (is_object($value)) {
		$vars = get_object_vars($value);
		foreach ($vars as $key => $data) {
			$value -> {$key} = stripslashes_deep($data);
		}
	} else {
		$value = stripslashes($value);
	}
	
	return $value;
}

/** 去除转义字符 */
function addslashes_deep($value) {
	if (is_array($value)) {
		$value = array_map('addslashes_deep', $value);
	} elseif (is_object($value)) {
		$vars = get_object_vars($value);
		foreach ($vars as $key => $data) {
			$value -> {$key} = addslashes_deep($data);
		}
	} else {
		$value = addslashes($value);
	}
	
	return $value;
}

/** 添加转义字符 */
function add_magic_quotes($array) {
	foreach ((array) $array as $k => $v) {
		if (is_array($v)) {
			$array[$k] = add_magic_quotes($v);
		} else {
			$array[$k] = addslashes($v);
		}
	}
	
	return $array;
}

/** 计算时间隔 */
function datediff($format, $timestamp) {
	$newtime = time() - $timestamp;
	
	$hour = floor($newtime / 3600);
	$day = floor($newtime / (24 * 3600));
	$week = floor($newtime / (7 * 24 * 3600));
	$month = floor($newtime / (30 * 24 * 3600));

	$format = strtolower($format);
	switch ($format) {
		case 'h' :
			return $hour;
			break;
		case 'd' :
			return $day;
			break;
		case 'w' :
			return $week;
			break;
		case 'm' :
			return $month;
			break;
	}
}

/** 表单HASH */
function get_formhash() {
	$formhash = substr(md5(substr(time(), 0, -7)), 8, 8);
	
	return $formhash;
}

/** 生成指定长度的随机字符串 */
function random($length = 16, $isnum = false){
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $isnum ? 10 : 35);
	$seed = $isnum ? $seed.'zZ'.strtoupper($seed) : str_replace('0', '', $seed).'01234056789';
	
	$randstr = '';
	$max = strlen($seed) - 1;
	for ($i = 0; $i < $length; $i++) {
		$randstr .= $seed{mt_rand(0, $max)};
	}
	return $randstr;
}

/** 编码函数 */
function authcode($string, $operation = 'ENCODE', $key = '', $expiry = 0) {
	$ckey_length = 4;

	$key = md5($key ? $key : 'yeN3g9EbNfiaYfodV63dI1j8Fbk5HaL7W4yaW4y7u2j4Mf45mfg2v899g451k576');
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}

/** 将数组转换为以逗号分隔的字符串 */
function dimplode($array) {
	if (!empty($array)) {
		return "'".implode("','", is_array($array) ? $array : array($array))."'";
	} else {
		return '';
	}
}

/** apache模块检测 */
function apache_mod_enabled($module) {
	if (function_exists('apache_get_modules')) {
		$apache_mod = apache_get_modules();
		if (in_array($module, $apache_mod)) {
			return true;
		} else {
			return false;
		}
	}
}

 /** 获取客户端IP */
function get_client_ip2() {        
	if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$client_ip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$client_ip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$client_ip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$client_ip = $_SERVER['REMOTE_ADDR'];
	}
	
	$client_ip = addslashes($client_ip);
	@preg_match("/[\d\.]{7,15}/", $client_ip, $ip);
	$ip_addr = $ip[0] ? $ip[0] : 'unknown';
	unset($ip);
	
	return $ip_addr;
}

if(!function_exists('get_domain'))
{
    function get_domain($url) {
    	if (preg_match("/^(http:\/\/)?([^\/]+)/i", $url, $domain)) {
    		return $domain[2];
    	} else {
    		return false;
    	}
    }
}

/** 获取指定URL内容 */
function get_url_content($url,$referer = '',$agent = '') {
	require_once(PATH_INC.'/snoopy.php');
	
	$data = array();
	$snoopy = new Snoopy();
	$snoopy->agent = empty($agent) ? $_SERVER['HTTP_USER_AGENT'] : trim($agent);
	$snoopy->referer = empty($referer) ? $_SERVER['HTTP_REFERER'] : trim($referer);
	$snoopy->fetch($url);
	if (!$snoopy->timed_out) {
		$data = $snoopy->results;
		$encode = mb_detect_encoding($data, array('ascii', 'gb2312', 'utf-8', 'gbk'));
		if ($encode == 'EUC-CN' || $encode == 'CP936') {
			$data = @mb_convert_encoding($data, 'utf-8', 'gb2312');
		}
	}
	
	return $data;
}


function msgbox($msg,$url = 'javascript: history.go(-1);',$timeout = 5)
{ 
    global $clienttype;    
    if(empty($clienttype)) $clienttype = 'main';
    if(empty($url)) $url = 'javascript: history.go(-1);';
    if($url == 'javascript: history.go(-1);') $timeout = 2;
    if($clienttype == 'main')
    {
        $path_tpls = PATH_TPLS_MAIN . '/2015';
        $file = 'msgbox.tpl';
    }
    elseif($clienttype == 'mobile')
    {
        $path_tpls = PATH_TPLS_MOBILE . '/2012';
        $file = '320/msgbox.tpl';
    }
    app_tpl::clear_cache($file,$path_tpls);
    app_tpl::assign('msg',$msg,$path_tpls);
    app_tpl::assign('url',$url,$path_tpls);
    app_tpl::assign('timeout',$timeout,$path_tpls);
    app_tpl::display($file,$path_tpls);	
	exit();
}

function redirect($url) {
	header('location:'.$url, false, 302);
    exit;
}

function get_real_size($size) {
	$kb = 1024;         // Kilobyte
	$mb = 1024 * $kb;   // Megabyte
	$gb = 1024 * $mb;   // Gigabyte
	$tb = 1024 * $gb;   // Terabyte

	if ($size < $kb) {
		return $size.' Byte';
	} else if ($size < $mb) {
		return round($size / $kb, 2).' KB';
	} else if ($size < $gb) {
		return round($size / $mb, 2).' MB';
	} else if ($size < $tb) {
		return round($size / $gb, 2).' GB';
	} else {
		return round($size / $tb,2).' TB';
	}
}

/**
 * $msg 信息内容
 * $type 信息类型 json
 * $iserror 标志位 分辨是正确信息或者错误信息 0正确 1错误 
 */
function ShowMsgByType($msg,$type,$iserror=1,$isecho=0)
{
    global $data,$fromurl;    
    if($type == 'json')
    {        
        $data['msg'] = $msg;
        $data['status'] = $iserror;                   
        echo json_encode2(gb2utf8($data));         
    }
    else
    {
        if($isecho == 1) echo $msg;
        else msgbox($msg,$fromurl);
    }
    exit();
}

function JsonMsg($result='', $callback='', $status=0, $msg='ok',$isecho=1, $charset='gbk')
{    
    global $exdata;
    
    $data = array_merge(array('status'=>$status, 'msg'=>$msg), (array)$exdata, array('result'=>$result));
    if($charset == 'gbk') $data = gb2utf8($data);    
    $result = json_encode2($data);
    if(!empty($callback)) $result = "$callback(".$result.");";
    if($isecho == 1)
    {
        header("Content-type: text/html; charset=utf-8");
        if(trim($_GET['appkey']) == 'd7e89ead7e78315e' && $status != 0)
        {
            header("paycut: -1");
        }
        elseif(trim($_GET['appkey']) == '9e0138b3edebdfcb' && $status != 0 && !in_array($msg, array('驾驶证号和档案编号不一致', '驾驶证信息不存在', '公司不存在')))
        {
            header("HTTP/1.1 404 Not Found");
        }
        echo $result;
        exit();
    }    
    return $result;    
}

function getCharset($str)
{
    return mb_detect_encoding($str, array(
		//ascii
		'ASCII',
		
		//unicodes
		'UTF-8',
		'UTF-16',
		
		//chinese
		'EUC-CN',  //gb2312
		'CP936',   //gbk
		'EUC-TW',  //big5
		
		//japanese
		'EUC-JP',
		'SJIS',
		'eucJP-win',
		'SJIS-win',
		'JIS',
		'ISO-2022-JP'
	));
}


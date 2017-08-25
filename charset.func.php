<?php 
/**
 * 用到的字符编码转换的小助手函数
 *
 * @version        $Id: charset.helper.php 1 2010-07-05 11:43:09Z tianya $
 * @package        Toyo.Helpers
 */


$GbkUniDic = null;

function array_iconv(&$val, $key, $userdata)
{ 
    $val = iconv($userdata[0], $userdata[1], $val); 
    //$key = iconv($userdata[0], $userdata[1], $key);    
    //$val = mb_convert_encoding($val,$userdata[1] ,$userdata[0]);
} 
function recursive_iconv($in_charset, $out_charset, $arr)
{ 
    if (!is_array($arr)){        
        return iconv($in_charset, $out_charset, $arr); 
    } 
    $ret = $arr;        
    array_walk_recursive($ret, "array_iconv", array($in_charset, $out_charset)); 
    return $ret;
}

/**
 *  UTF-8 转GB编码
 *
 * @access    public
 * @param     string  $utfstr  需要转换的字符串/数组
 * @return    string
 */
/*function utf82gb($utfstr)
{        
    return iconv('utf-8','gbk//ignore',$utfstr);
}*/

function utf82gb($utfstr)
{   
    return recursive_iconv('utf-8','gbk//ignore',$utfstr);
}

/**
 *  GB转UTF-8编码
 *
 * @access    public
 * @param     string  $gbstr  gbk的字符串
 * @return    string
 */
/*function gb2utf8($gbstr)
{        
    return iconv('gbk','utf-8//ignore',$gbstr);             
}*/

function gb2utf8($gbstr)
{        
    return recursive_iconv('gbk','utf-8//ignore',$gbstr);             
}

/**
 *  Unicode转utf8
 *
 * @access    public
 * @param     string  $c  Unicode的字符串内容
 * @return    string
 */
if ( ! function_exists('u2utf8'))
{
    function u2utf8($c)
    {
        for ($i = 0;$i < count($c);$i++)
        {
            $str = "";
        }
        if ($c < 0x80)
        {
            $str .= $c;
        }
        else if ($c < 0x800)
        {
            $str .= (0xC0 | $c >> 6);
            $str .= (0x80 | $c & 0x3F);
        }
        else if ($c < 0x10000)
        {
            $str .= (0xE0 | $c >> 12);
            $str .= (0x80 | $c >> 6 & 0x3F);
            $str .= (0x80 | $c & 0x3F);
        }
        else if ($c < 0x200000)
        {
            $str .= (0xF0 | $c >> 18);
            $str .= (0x80 | $c >> 12 & 0x3F);
            $str .= (0x80 | $c >> 6 & 0x3F);
            $str .= (0x80 | $c & 0x3F);
        }
        return $str;
    }
}

/**
 *  utf8转Unicode
 *
 * @access    public
 * @param     string  $c  UTF-8的字符串信息
 * @return    string
 */
if ( ! function_exists('utf82u'))
{
    function utf82u($c)
    {
        switch(strlen($c))
        {
            case 1:
                return ord($c);
            case 2:
                $n = (ord($c[0]) & 0x3f) << 6;
                $n += ord($c[1]) & 0x3f;
                return $n;
            case 3:
                $n = (ord($c[0]) & 0x1f) << 12;
                $n += (ord($c[1]) & 0x3f) << 6;
                $n += ord($c[2]) & 0x3f;
                return $n;
            case 4:
                $n = (ord($c[0]) & 0x0f) << 18;
                $n += (ord($c[1]) & 0x3f) << 12;
                $n += (ord($c[2]) & 0x3f) << 6;
                $n += ord($c[3]) & 0x3f;
                return $n;
        }
    }
}

/**
 *  Big5码转换成GB码
 *
 * @access    public
 * @param     string   $Text  字符串内容
 * @return    string
 */
/*if ( ! function_exists('big52gb'))
{
    function big52gb($Text)
    {        
        return iconv('big5','gbk//ignore',$Text);       
    }
}*/

function big52gb($Text)
{        
    return recursive_iconv('big5','gbk//ignore',$Text);             
}

/**
 *  GB码转换成Big5码
 *
 * @access    public
 * @param     string  $Text 字符串内容
 * @return    string
 */
/*if ( ! function_exists('gb2big5'))
{
    function gb2big5($Text)
    {        
        return iconv('gbk','big5//ignore',$Text);       
    }
}*/

function gb2big5($Text)
{        
    return recursive_iconv('gbk','big5//ignore',$Text);             
}

/**
 * $str 原始字符串
 * $encoding 原始字符串的编码，默认GBK
 * $prefix 编码后的前缀，默认"&#"
 * $postfix 编码后的后缀，默认";"
 */
function unicode_encode($str, $encoding = 'GBK', $prefix = '&#', $postfix = ';') {
    $str = iconv($encoding, 'UCS-2BE', $str);
    $arrstr = str_split($str, 2);
    $unistr = '';
    for($i = 0, $len = count($arrstr); $i < $len; $i++) {
        $dec = hexdec(bin2hex($arrstr[$i]));
        $unistr .= $prefix . $dec . $postfix;
    } 
    return $unistr;
} 
 
/**
 * $str Unicode编码后的字符串
 * $encoding 原始字符串的编码，默认GBK
 * $prefix 编码字符串的前缀，默认"&#"
 * $postfix 编码字符串的后缀，默认";"
 */
function unicode_decode($unistr, $encoding = 'GBK', $prefix = '&#', $postfix = ';') {    
    $arruni = explode($prefix, $unistr);
    $unistr = '';
    for($i = 1, $len = count($arruni); $i < $len; $i++)
    {
        if(strlen($postfix) > 0)
        {
            $arruni2 = array();
            $arruni2 = explode($postfix, $arruni[$i]);
            if (count($arruni2) > 1)
            {
                if (is_numeric($arruni2[0]))
                {
                    $temp = intval($arruni2[0]);
                    $unistr .= iconv('UCS-2BE', $encoding, ($temp < 256) ? chr(0) . chr($temp) : chr($temp / 256) . chr($temp % 256));
                    if (!empty($arruni2[1]))
                    {
                        $unistr .= $arruni2[1];
                    }
                } 
                else
                {
                    $unistr .= $arruni[$i];
                }
            } 
            else
            {
                $unistr .= $arruni[$i];
            }
        } 
        else
        {
            $temp = intval($arruni[$i]);
            $unistr .= iconv('UCS-2BE', $encoding, ($temp < 256) ? chr(0) . chr($temp) : chr($temp / 256) . chr($temp % 256));
        }
    }

    return $unistr;
}

/**
 *  unicode url编码转gbk编码函数
 *
 * @access    public
 * @param     string  $str  转换的内容
 * @return    string
 */
if ( ! function_exists('UnicodeUrl2Gbk'))
{
    function UnicodeUrl2Gbk($str)
    {
        //载入对照词典
        if(!isset($GLOBALS['GbkUniDic']))
        {
            $fp = fopen(PATH_INC.'/data/gbk-unicode.dat','rb');
            while(!feof($fp))
            {
                $GLOBALS['GbkUniDic'][bin2hex(fread($fp,2))] = fread($fp,2);
            }
            fclose($fp);
        }

        //处理字符串
        $str = str_replace('$#$','+',$str);
        $glen = strlen($str);
        $okstr = "";
        for($i=0; $i < $glen; $i++)
        {
            if($glen-$i > 4)
            {
                if($str[$i]=='%' && $str[$i+1]=='u')
                {
                    $uni = strtolower(substr($str,$i+2,4));
                    $i = $i+5;
                    if(isset($GLOBALS['GbkUniDic'][$uni]))
                    {
                        $okstr .= $GLOBALS['GbkUniDic'][$uni];
                    }
                    else
                    {
                        $okstr .= "&#".hexdec('0x'.$uni).";";
                    }
                }
                else
                {
                    $okstr .= $str[$i];
                }
            }
            else
            {
                $okstr .= $str[$i];
            }
        }
        return $okstr;
    }
}

/**
 *  自动转换字符集 支持数组转换
 *
 * @access    public
 * @param     string  $str  转换的内容
 * @return    string
 */
if(!function_exists('AutoCharset'))
{
    function AutoCharset($content, $from='gbk', $to='utf-8')
    {
        $from = strtoupper($from) == 'UTF8'? 'utf-8' : $from;
        $to = strtoupper($to) == 'UTF8'? 'utf-8' : $to;
        if(strtoupper($from) === strtoupper($to) || empty($content) || (is_scalar($content) && !is_string($content))){
            //如果编码相同或者非字符串标量则不转换
            return $content;
        }
        if(is_string($content)) 
        {
            if(function_exists('mb_convert_encoding'))
            {
                return mb_convert_encoding($content, $to, $from);
            } 
            elseif(function_exists('iconv'))
            {
                return iconv($from, $to, $content);
            }
            else
            {
                return $content;
            }
        }
        elseif(is_array($content))
        {
            foreach($content as $key=>$val)
            {
                $_key = AutoCharset($key,$from,$to);
                $content[$_key] = AutoCharset($val,$from,$to);
                if($key != $_key) unset($content[$key]);
            }
            return $content;
        }
        else{
            return $content;
        }
    }
}

//GBK页面可改为gb2312，其他随意填写为UTF8
function Pinyin($_String, $_Code = 'gb2312')
{ 
    $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha" .
        "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|" .
        "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er" .
        "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui" .
        "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang" .
        "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang" .
        "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue" .
        "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne" .
        "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen" .
        "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang" .
        "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|" .
        "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|" .
        "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu" .
        "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you" .
        "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|" .
        "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
    $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990" .
        "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725" .
        "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263" .
        "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003" .
        "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697" .
        "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211" .
        "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922" .
        "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468" .
        "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664" .
        "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407" .
        "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959" .
        "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652" .
        "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369" .
        "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128" .
        "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914" .
        "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645" .
        "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149" .
        "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087" .
        "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658" .
        "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340" .
        "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888" .
        "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585" .
        "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847" .
        "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055" .
        "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780" .
        "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274" .
        "|-10270|-10262|-10260|-10256|-10254";
    $_TDataKey = explode('|', $_DataKey);
    $_TDataValue = explode('|', $_DataValue);
    $_Data = array_combine($_TDataKey, $_TDataValue);
    arsort($_Data);
    reset($_Data);
    if ($_Code != 'gb2312')
        $_String = _U2_Utf8_Gb($_String);
    $_Res = '';
    for ($i = 0; $i < strlen($_String); $i++)
    {
        $_P = ord(substr($_String, $i, 1));
        if ($_P > 160)
        {
            $_Q = ord(substr($_String, ++$i, 1));
            $_P = $_P * 256 + $_Q - 65536;
        }
        $_Res .= _Pinyin($_P, $_Data);
    }
    return preg_replace("/[^a-z0-9]*/", '', $_Res);
}
function _Pinyin($_Num, $_Data)
{
    if ($_Num > 0 && $_Num < 160)
    {
        return chr($_Num);
    } elseif ($_Num < -20319 || $_Num > -10247)
    {
        return '';
    } else
    {
        foreach ($_Data as $k => $v)
        {
            if ($v <= $_Num)
                break;
        }
        return $k;
    }
}
function _U2_Utf8_Gb($_C)
{
    $_String = '';
    if ($_C < 0x80)
    {
        $_String .= $_C;
    } elseif ($_C < 0x800)
    {
        $_String .= chr(0xC0 | $_C >> 6);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif ($_C < 0x10000)
    {
        $_String .= chr(0xE0 | $_C >> 12);
        $_String .= chr(0x80 | $_C >> 6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif ($_C < 0x200000)
    {
        $_String .= chr(0xF0 | $_C >> 18);
        $_String .= chr(0x80 | $_C >> 12 & 0x3F);
        $_String .= chr(0x80 | $_C >> 6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    }
    return iconv('UTF-8', 'GB2312', $_String);
}

/**
 *  获取拼音信息
 *
 * @access    public
 * @param     string  $str  字符串
 * @param     int  $isfirst  是否为首字母
 * @param     int  $isclose  解析后是否释放资源
 * @return    string
 */
function GetPinyin($str, $isfirst=0, $isclose=1)
{
    global $pinyins;
    $restr = '';
    $str = trim($str);
    $slen = strlen($str);
    if($slen < 2)
    {
        return $str;
    }
    if(count($pinyins) == 0)
    {
        $fp = fopen(PATH_INC.'/data/pinyin.dat', 'r');
        while(!feof($fp))
        {
            $line = trim(fgets($fp));
            $pinyins[$line[0].$line[1]] = substr($line, 3, strlen($line)-3);
        }
        fclose($fp);
    }
    for($i=0; $i<$slen; $i++)
    {
        if(ord($str[$i])>0x80)
        {
            $c = $str[$i].$str[$i+1];
            $i++;
            if(isset($pinyins[$c]))
            {
                if($isfirst==0)
                {
                    $restr .= $pinyins[$c];
                }
                else
                {
                    $restr .= $pinyins[$c][0];
                }
            }else
            {
                $restr .= "_";
            }
        }else if( preg_match("/[a-z0-9]/i", $str[$i]) )
        {
            $restr .= $str[$i];
        }
        else
        {
            $restr .= "_";
        }
    }
    if($isclose==0)
    {
        unset($pinyins);
    }
    return $restr;
}


//去除UTF-8 BOM
function prepareJSON($input) {

    //This will convert ASCII/ISO-8859-1 to UTF-8.
    //Be careful with the third parameter (encoding detect list), because
    //if set wrong, some input encodings will get garbled (including UTF-8!)
    //$imput = mb_convert_encoding($input, 'UTF-8', 'ASCII,UTF-8,ISO-8859-1');

    //Remove UTF-8 BOM if present, json_decode() does not like it.
    if(substr($input, 0, 3) == pack("CCC", 0xEF, 0xBB, 0xBF)) $input = substr($input, 3);

    return $input;
}

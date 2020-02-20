<?php


global $months;
global $months_f;
global $months_eng;

$months = array(
    "января",
    "февраля",
    "марта",
    "апреля",
    "мая",
    "июня",
    "июля",
    "августа",
    "сентября",
    "октября",
    "ноября",
    "декабря");
$months_f = array(
    "январь",
    "февраль",
    "март",
    "апрель",
    "май",
    "июнь",
    "июль",
    "август",
    "сентябрь",
    "октябрь",
    "ноябрь",
    "декабрь");
$months_eng = array(
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December");
define ("PI", 3.141592654);


function GetLanguageStrings($data,$flds,$lang = 'ru',$clang = 0) {
    $f = explode(',',$flds);
    $a = array();
    foreach ($data as $i=>$v) {
	foreach($f as $t) {
	    if (is_numeric(@$data[$i][$t])) {
		array_push($a,$data[$i][$t]);
	    }
	}
    }
    if (count($a)) {
	Cache_MarkQueryBlock("lang_string");
	$res = mysql_query2("SELECT bid,txt FROM lang_string WHERE bid in (".join(',',$a).") AND lang = '$lang'");
	$res = fetch_all_data($res,'bid');
	if (!count($res)) {
	    Cache_DeleteMarkedQuery("lang_string");
	}
	if ($clang) {
	    $rs = mysql_query2("SELECT s.bid as bid,sum(l.indx) as lav FROM lang_string as s,language_list as l WHERE s.lang = l.lang AND s.bid in (".join(',',$a).") AND s.lang != '$lang' AND s.txt != '' GROUP by bid");
	    $rs = fetch_all_data($rs,'bid');
	}
	foreach ($data as $i=>$v) {
	    foreach($f as $t) {
		if (is_numeric(@$data[$i][$t])) {
		    if ($clang) {
			$data[$i][$t."_availang"] = @$rs[$data[$i][$t]]['lav'] + 0;
		    }
		    if ($data[$i][$t]) {
			$data[$i][$t] = @$res[$data[$i][$t]]['txt']."";
		    } else {
			$data[$i][$t] = '';
		    }
		}
	    }
	}
    }
    return $data;
}


function Cache_MarkQueryBlock($mark) {
    global $cache_db_requests,$cache_analyse;
    if ($cache_analyse) {
	array_push($cache_db_requests,"/*--$mark--*/");
    }
}

function Cache_DeleteMarkedQuery($mark) {
    global $cache_db_requests,$cache_analyse;
    if ($cache_analyse) {
	while  (count($cache_db_requests)&&($cache_db_requests[count($cache_db_requests)-1]!="/*--$mark--*/")) {
	    array_pop($cache_db_requests);
	}
    }
}

function Class2Array($data)   {
    $ret = array();
    foreach ((array)$data as $key=>$val) {
	if (is_array($val)||is_object($val)) {
	    $ret[$key] = Class2Array($val);
	} else {
	    $ret[$key] = $val;
	}
    }
    return $ret;
}

function convert_array($arr,$from,$to) {
    $ret = Array();
    foreach ($arr as $k=>$v) {
	if (is_array($v)) {
	    $v = convert_array($v, $from,$to);
	} else {
	    $v = iconv($from,$to,$v);
	}
	$ret[$k] = $v;
    }
    return $ret;
}

function CreateDirectory ($dir, $chmod=0755) {
    $arr = array();
    $dir = preg_replace("|\/+|", '/', $dir);
    while (!is_dir($dir)) {
	if (preg_match("/[\/\\\]([\,\-\.\s\w]{0,})$/",trim($dir),$res)) {
	    if ($res[1]) {
		array_push($arr,$res[1]);
	    }
	    $dir = preg_replace("/[\/\\\]([\,\-\.\s\w]{0,})$/",'',$dir);
	} else {
	    break;
	}
    }
    while ($d = array_pop($arr)) {
	$dir = $dir  . '/' . $d;
	if (!mkdir($dir)) {echo "<p>Cannot make directory [$dir]";}
	else {chmod($dir, $chmod);}
    }
    return $dir;
}

?>

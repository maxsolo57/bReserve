<?php


$close_limit = 4;
$timeline_pics = 12;
$timeline_item_width = 86;
$pic_flds = "p.oid as id, p.path as p, p.filename as f, LPAD(YEAR(p.shottime)-2000,2,'0') as y, LPAD(MONTH(p.shottime),2,'0') as n, LPAD(DAYOFMONTH(p.shottime),2,'0') as d, LPAD(HOUR(p.shottime),2,'0') as h, LPAD(MINUTE(p.shottime),2,'0') as m";

function today($cyear, $cmonth, $cday, $type) {
    $res = "SELECT oid, HOUR(shottime) as h, LPAD(MINUTE(shottime),2,'0') as m FROM webcamera WHERE type = '$type' AND shottime > '$cyear-$cmonth-$cday' AND  shottime <= '$cyear-$cmonth-$cday 23:59:59' ORDER by shottime";
    $res = mysql_query2($res);
    return array('today'=>fetch_all_data($res));
}

function calendar($cyear, $cmonth, $type) {
    $dt = array();
    $res = mysql_query2("SELECT DAYOFMONTH(LAST_DAY('$cyear-$cmonth-01')),DAYOFWEEK(LAST_DAY('$cyear-$cmonth-01')) ");
    $dt['cmonth_ld']  = mysql_result($res, 0, 0);
    $dt['cmonth_ldw'] = mysql_result($res, 0, 1)+5;
    $res = "SELECT DAYOFMONTH(shottime) as day, count(*) as n FROM webcamera WHERE type = '$type' AND shottime > '$cyear-$cmonth-01' AND  shottime <= '$cyear-$cmonth-{$dt['cmonth_ld']} 23:59:59'  GROUP by day ORDER by day";
    $res = mysql_query2($res);
    $res = fetch_all_data($res, 'day');
    $dt['calend'] = array();
    for ($i=1; $i<=$dt['cmonth_ld']; $i++) {
	array_push($dt['calend'], array('day'=>$i, 'count'=>@$res[$i]['n']+0));
    }
    return $dt;
}

function today2($cyear, $cmonth, $cday, $type) {
    $res = "SELECT oid, HOUR(shottime) as h, LPAD(MINUTE(shottime),2,'0') as m FROM webcamera WHERE type = '$type' AND shottime >= '$cyear-$cmonth-$cday' AND  shottime <= '$cyear-$cmonth-$cday 23:59:59' ORDER by shottime";
    $res = mysql_query2($res);
    $t = array();
    while ($row=mysql_fetch_assoc($res)) {
	if (!isset($t[$row['h']])) $t[$row['h']]=array();
	$t[$row['h']][$row['m']] = $row['oid'];
    }
    ksort($t);
    return array('today'=>$t);
}

?>
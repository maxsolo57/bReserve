<?php

set_include_path('/var/www/html/webcamera/php');

require_once('host.php');
require_once('lib_reg.php');
require_once('lib_nsite.php');
require_once('imager.php');

function webcamera() {
  global $SRC_BASE,$CACHE_BASE;

  //   $SRC_BASE = '../webcam_files';
  $SRC_BASE = 'http://b-reserve.ru/webcam_files';

  $CACHE_BASE  = "php/pics";


  require_once ('lib_webcam.php');
  $outext = '';
  $dt = array();

  // echo 'query: '.$_SERVER["QUERY_STRING"].'<br>';


  // if URL contains pXXX or vXXX
  if (preg_match("/([lpv])(i?)(-?\d+)(\w?)/", $_SERVER["QUERY_STRING"], $rs)) {

    $type = $rs[1];
    $t2 = $rs[2];
    $num = $rs[3];
    $mode = @$rs[4];
    $type = ($type=='v')?'video':(($type=='p')?'pic':'live');



    if (@$t2=='i') {
      $picid  = $num;
    } else {
      $picid  = 0;
      $cyear  = mb_substr($num, 0, 2) + 2000;
      $cmonth = mb_substr($num, 2, 2) + 0;
      $cday   = mb_substr($num, 4, 2) + 0;
      $chour  = mb_substr($num, 6, 2) + 0;
      $cmin   = mb_substr($num, 8, 2) + 0;
    }
  }


  $type = 'video';

  $where = array();
  if (!($picid && $cyear && $cmonth && $cday)) {
    array_push($where, "type='$type'");
    if ($picid) {
      $order = "ABS(oid - $picid)";
    } else {
      $res = "SELECT YEAR(now()) as y, month(now()) as m, dayofmonth(now()) as d";
      $res = mysql_query2($res);
      $res = mysql_fetch_assoc($res);
      $cyear  = @$cyear?$cyear:$res['y'];
      $cmonth = @$cmonth?$cmonth:$res['m'];
      $cday   = @$cday?$cday:$res['d'];
      $chour  = @$chour?$chour:12;
      $cmin   = @$cmin?$cmin:00;
      $order = "ABS(UNIX_TIMESTAMP(shottime) - UNIX_TIMESTAMP('$cyear-$cmonth-$cday $chour:$cmin:00'))";
    }
  } else {
    $order = "shottime";
    array_push($where, "shottime > '$cyear-$cmonth-$cday'");
  }

  $res = "SELECT shottime, YEAR(shottime) as y, month(shottime) as m, dayofmonth(shottime) as d, dayofweek(shottime) as w, HOUR(shottime) as h,LPAD(MINUTE(shottime), 2, '0') as n, oid FROM webcamera WHERE " . join(" AND ", $where) . " ORDER by $order LIMIT 1";
  $res = mysql_query2($res);
  $res = mysql_fetch_assoc($res);
  $picid  = $res['oid'];
  $cyear  = $res['y'] ;
  $cmonth = $res['m'];
  $cwday  = $res['w'];
  $cday   = $res['d'];
  $chour  = $res['h'];
  $cmin   = $res['n'];
  $cptime = $res['shottime'];
  $total_on_page = $timeline_pics;


  switch ($mode) {
    case 'dl': $mode_where = " AND ABS((HOUR(shottime)*60 + MINUTE(shottime)) - ($chour*60+$cmin)) < $close_limit"; break;
    case 'wk': $mode_where = " AND ABS(((DAYOFWEEK(shottime)*24+HOUR(shottime))*60 + MINUTE(shottime)) - (($cwday*24+$chour)*60+$cmin)) < $close_limit";break;
    case 'mn': $mode_where = " AND ABS(((DAYOFMONTH(shottime)*24+HOUR(shottime))*60 + MINUTE(shottime)) - (($cday*24+$chour)*60+$cmin)) < $close_limit";break;
    default: $mode_where = "";
  }
  $res = "SELECT $pic_flds FROM webcamera as p WHERE type = '$type' AND shottime <= '$cptime' ORDER by shottime desc LIMIT 1";
  $res = mysql_query2($res);
  if (mysql_num_rows($res)) {
    $dt['cur_pic'] = fetch_all_data($res);
    $picid = $dt['cur_pic'][0]['id'];
  }
  $res = mysql_query2("SELECT $pic_flds FROM webcamera as p WHERE type = '$type' AND shottime > '$cptime' $mode_where ORDER by shottime LIMIT 4");
  if (mysql_num_rows($res)) {
    $dt['next_timeline'] = fetch_all_data($res);
    $dt['next_oid'] = @$dt['next_timeline'][0]['oid'];
  }
  $limit = $total_on_page - 1 - count(@$dt['next_timeline']);


  $res = "SELECT $pic_flds FROM webcamera as p WHERE type = '$type' AND shottime < '$cptime' $mode_where  ORDER by shottime desc LIMIT $limit";
  $res = mysql_query2($res);
  if (mysql_num_rows($res)) {
    $dt['prev_timeline'] = array_reverse(fetch_all_data($res));
    $dt['prev_oid'] = @$dt['prev_timeline'][count($dt['prev_timeline'])-1]['oid'];
  }
  if (mysql_num_rows($res) < $limit) {
    $limit = $total_on_page - 1;
    $res = mysql_query2("SELECT $pic_flds FROM webcamera as p WHERE type = '$type' AND oid > ".($picid+0)." $mode_where  ORDER by oid LIMIT $limit");
    if (mysql_num_rows($res)) {
      $dt['next_timeline'] = fetch_all_data($res);
      $dt['next_oid'] = $dt['next_timeline'][0]['oid'];
    }
  }
  $dt['type']   = mb_substr($type, 0, 1);



  // check thumbnails $dt['next_timeline'] ; $dt['cur_pic'] ; $dt['prev_timeline']

  $resfolder = 'video';
  $resextension = '.flv';
  $thumbextension = '.thumb.jpg';

  foreach ($dt['cur_pic'] as $resarr){
    $currentthumb = SERVER_ROOT().$CACHE_BASE.$resarr[p].'/'.$resarr[f].$thumbextension;
    if (!file_exists($currentthumb)){
      make_thumb($currentthumb);
    }
  }

  foreach ($dt['prev_timeline'] as $resarr){
    $currentthumb = SERVER_ROOT().$CACHE_BASE.$resarr[p].'/'.$resarr[f].$thumbextension;
    if (!file_exists($currentthumb)){
      make_thumb($currentthumb);
    }
  }

  foreach ($dt['next_timeline'] as $resarr){
    $currentthumb = SERVER_ROOT().$CACHE_BASE.$resarr[p].'/'.$resarr[f].$thumbextension;
    if (!file_exists($currentthumb)){
      make_thumb($currentthumb);
    }
  }


  $dt['picid']  = $picid  ;
  $dt['cyear']  = str_pad($cyear - 2000, 2, '0', STR_PAD_LEFT);
  $dt['cmonth'] = str_pad($cmonth, 2, '0', STR_PAD_LEFT);
  $dt['cday']   = str_pad($cday, 2, '0', STR_PAD_LEFT);
  $dt['chour']  = str_pad($chour, 2, '0', STR_PAD_LEFT);
  $dt['cmin']   = str_pad($cmin, 2, '0', STR_PAD_LEFT);
  $dt['_page_lang'] = 'rus';
  $dt['mode']   = $mode   ;
  $dt['src_base']   =   $SRC_BASE. '/' . ((mb_substr($type,0,1)=='v')?'video':'pics');
  $dt['cache_base']   =   $CACHE_BASE;
  $dt['timeline_pics']    = $timeline_pics;
  $dt['timeline_item_width']   = $timeline_item_width;
  $dt = array_merge($dt,calendar($cyear, $cmonth, $type));

  // echo 'picid:'.$dt['picid'].'; ';
  // echo 'y:'.$dt['cyear'].'; ';
  // echo 'm:'.$dt['cmonth'].'; ';
  // echo 'd:'.$dt['cday'].'<br>';
  // echo 'h:'.$dt['chour'].'; ';
  // echo 'm:'.$dt['cmin'].'; ';
  // echo 'dt[timeline_pics] = '.$dt['timeline_pics'];


  global $d__;
  $d__ = array_merge($dt);

  require(SERVER_ROOT()."php/page_video.php");
}

database_connect();
echo webcamera();

// echo '<br>the end of archive module<br>';
?>

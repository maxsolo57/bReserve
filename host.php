<?php

global $REAL_FILES_PATH;
// $REAL_FILES_PATH = "C:/etc/Apache24/htdocs/drupal/webcam_files";
$REAL_FILES_PATH = "http://bLake.ru/camerasrc";

// $SRC_BASE = "/webcam_files";
$SRC_BASE = "http://bLake.ru/camerasrc";

$CACHE_BASE  = "php/pics";

$COOKIE_HOST = 'www.bReserve.ru';
$_GET_TMPL_BASEDIR = "php/";
$dblink = 0;

ini_set('display_errors','On');
ini_set('error_reporting','E_ALL & ~E_NOTICE');

mb_internal_encoding("UTF-8");


function database_connect($mode='') {
  $SQLHOST = "localhost:3306";
  $SQLUN = "root";
  $SQLPW = "xxxxxxx";
  $SQLDB = "bZap_db";
  global $dblink;
  $dblink =  mysql_pconnect($SQLHOST, $SQLUN, $SQLPW);
  if (!$dblink) {
    $errtext = "<br>DB CONNECT ERROR: " . mysql_error() . "\r\n";
    switch ($mode) {
      case 'silent_ret': return 1;
      break;
      default: die($errtext);
    }

  }
  $res = @mysql_select_db($SQLDB);

  if (!$res) {
    $errtext = "<br>DB_SELECT_BASE ERROR: " . mysql_error();
    switch ($mode) {
      case 'silent_ret': return 2;
      break;
      default: die($errtext);
    }
  }
  // mysql_query("SET NAMES KOI8R");
  // mysql_query("SET CHARACTER SET KOI8R");
  return 0;
}


function SERVER_ROOT(){
  $ROOTPATH = "/var/www/html/webcamera/";
  return $ROOTPATH;
};


function database_disconnect() {
  mysql_close();
}



function mysql_query2($query, $dlink=0) {
  global $cache_analyse,$cache_db_requests, $dblink, $OFFICE_REMOTE_ADDR;
  if ($cache_analyse) {
    array_push($cache_db_requests,"<br>$query");
  }
  if (!$dlink) {
    $dlink = $dblink;
  }
  $res = mysql_query($query, $dlink);
  if (mysql_error()) {
    echo "\n<p>DB Error description: <b style='color:red'>" . mysql_error(). "\n<p>Bad query: [$query]";
  }
  return $res;
}



?>

<?php

set_include_path('/home/bZap/domains/bReserve.ru/public_html/webcamera/php');

require_once('host.php');
require_once('lib_reg.php');
require_once('lib_nsite.php');
require_once('imager.php');

set_include_path('/home/bZap/domains/bReserve.ru/public_html/webcamera/php/_libs/phpseclib');
include('Net/SSH2.php');
include('Net/SFTP.php');



function GetFileList($path) {
  global $sftp;

  $ret = array();

  $sftp->chdir('/'.$path);

  $dirs = $sftp->nlist();

  foreach ($dirs as $dir){
    if ( ($dir <> '.') && ($dir <> '..')) {
      array_push($ret, $dir);
    }
  }

  sort($ret);
  return $ret;
}

function webcam_PushNewFile($pic, $year, $mn, $dy, $type, $total, $min, $mnames, $src) {
  global $SRC_BASE,$CACHE_BASE;

  switch ($type) {
    case 'pic':
    $p = preg_match("/(pic\-(\d+){$mnames[$mn-1]}{$year}\-(\d+)\-(\d+)\-(\d+))\.jpg/", $pic, $rs);
    break;
    case 'video':
    $p = preg_match("/(video\-(\d+){$mnames[$mn-1]}{$year}\-(\d+)\-(\d+))\.flv/", $pic, $rs);
    break;
    default: $p = 0;
  }
  if ($p) {
    if ((($year >  $min['year'])) ||
    (($year == $min['year'])&&($mn >  $min['mon']))  ||
    (($year == $min['year'])&&($mn == $min['mon'])&&($rs[2] >  $min['day']))  ||
    (($year == $min['year'])&&($mn == $min['mon'])&&($rs[2] == $min['day'])&&($rs[3] >  $min['hour']))  ||
    (($year == $min['year'])&&($mn == $min['mon'])&&($rs[2] == $min['day'])&&($rs[3] == $min['hour'])&&($rs[4] >  $min['min'])) ||
    (($year == $min['year'])&&($mn == $min['mon'])&&($rs[2] == $min['day'])&&($rs[3] == $min['hour'])&&($rs[4] == $min['min'])&&($rs[5] > $min['sec']))
  ) {
    if ($type=='pic') {
      $res = "INSERT IGNORE INTO webcamera (pushtime, shottime, path, filename, type)
      VALUES (now(), '$year-$mn-$rs[2] $rs[3]:$rs[4]:$rs[5]', '$src/$year/{$mnames[$mn-1]}/$dy','$rs[1]', '$type' )";
    } else {
      $res = "INSERT IGNORE INTO webcamera (pushtime, shottime, path, filename, type)
      VALUES (now(), '$year-$mn-$rs[2] $rs[3]:$rs[4]:$rs[5]', '$src/$year/{$mnames[$mn-1]}','$rs[1]', '$type' )";
    }



    $res = mysql_query2($res);
    if (mysql_affected_rows()) {

      echo 'Query: '.$mnames[$mn-1].'/'.$dy.'/'.$rs[1].'; total = '.$total.'<br>';

      switch ($type) {
        case 'pic':

        $currentmain = SERVER_ROOT().$CACHE_BASE.'/'.$year.'/'.$mnames[$mn-1].'/'.$dy.'/'.$rs[1].'.thumb.jpg';
        if (!file_exists($currentmain)){
          make_thumb($currentmain);
        }

        $currentmain = SERVER_ROOT().$CACHE_BASE.'/'.$year.'/'.$mnames[$mn-1].'/'.$dy.'/'.$rs[1].'.main.jpg';
        if (!file_exists($currentmain)){
          make_thumb($currentmain);
        }
        break;
        case 'video':
        $currentmain = SERVER_ROOT().$CACHE_BASE.'/'.$year.'/'.$mnames[$mn-1].'/'.$rs[1].'.thumb.jpg';
        if (!file_exists($currentmain)){
          make_thumb($currentmain);
        }
        break;
      }

      $total++;
    }

  }
} else {
  echo "<p><b>Strange filename [$pic] found - cannot parse it - should be smth like pic-##{$mnames[$mn-1]}{$year}-##-##-##.jpg</b>";
}
return $total;
}

function webcam_ScanForNew($realsrc, $src, $type, $silent=1) {
  $mnames= array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

  $res = mysql_query2("SELECT shottime-INTERVAL 10 day FROM webcamera WHERE type = '$type' AND shottime > '2010-01-01' ORDER by shottime desc limit 1");
  if (mysql_num_rows($res)) {
    $min = mysql_result($res,0,0);
  } else {
    $min = '2010-01-01 12:00:00';
  }

  echo 'min:'.$min.'<br>';


  preg_match("/(\d+)\-(\d+)\-(\d+)\s+(\d+):(\d+):(\d+)/",$min,$rs);
  $min = array();
  $min['year'] = $rs[1];
  $min['mon']  = $rs[2];
  $min['day']  = $rs[3];
  $min['hour'] = $rs[4];
  $min['min']  = $rs[5];
  $min['sec']  = $rs[6];

  $years = GetFileList($realsrc);

  $total = 0;



  if (!$silent) {
    echo " Years: " . array_check($years);
  }


  foreach ($years as $year) {
    if ($year >= $min['year']) {
      $months = GetFileList($realsrc . "/" . $year);

      if (!$silent) {
        echo "<h2>$year</h2>Months:".array_check($months);
      }


      for ($mn=1;$mn<=12;$mn++) {
        if (( ($year > $min['year']) || (($year == $min['year'])&&($mn >= $min['mon'])) )&&(in_array($mnames[$mn-1], $months))) {

          $files = GetFileList($realsrc.'/'.$year.'/'.$mnames[$mn-1]);

          if (!$silent) {
            echo '<h3>'.$mnames[$mn-1].' '.$year.'</h3>Days:'.array_check($files);
          }

          if ($type=='pic') {
            for ($dy=1;$dy<=31;$dy++) {

              if ($total<500){


                $files = GetFileList($realsrc.'/'.$year.'/'.$mnames[$mn-1].'/'.str_pad($dy, 2, "0", STR_PAD_LEFT));

                if (!$silent) {
                  //                    echo $mnames[$mn-1].' - '.$dy.' : Total:'.$total.array_check($files);
                }

                foreach ($files as $pic) {
                  $total = webcam_PushNewFile($pic, $year, $mn, str_pad($dy, 2, "0", STR_PAD_LEFT), $type, $total, $min, $mnames, $src);
                }
              }
            }

          }

          else {
            foreach ($files as $pic) {
              $total = webcam_PushNewFile($pic, $year, $mn, 0, $type, $total, $min, $mnames, $src);
            }
          }
        }
      }
    }
  }
  if ($total && !$silent) {
    echo "<p>$total records pushed successfully";
  }
}

database_connect();


global $sftp;
$sftp = new Net_SFTP('bLake.ru');

if (!$sftp->login('bRes', 'xxxxxxx')) {
  exit('Login Failed');
}


webcam_ScanForNew("pics", "", 'pic', 0);
webcam_ScanForNew("video", "", 'video', 0);

?>

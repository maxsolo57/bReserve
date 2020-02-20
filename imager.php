<?php


require_once('host.php');
require_once('lib_reg.php');

ini_set('gd.jpeg_ignore_warning', 1);
$err = '';


function url_exists($path){
  $file_headers = @get_headers($path);
  if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
    return false;
  }
  else {
    return true;
  }
}



function make_thumb($request_path='') {
  global $REAL_FILES_PATH;

  if (!isset($request_path)) {
    //   echo "I have no reason to show you anything...";
    //   exit();
  }
  if (!preg_match("|(http:\/\/)?(.*?)pics(\/.*?)([^\/]+)$|",$request_path,$res)) {
    preg_match("|(http:\/\/)?(.{0,}pics)(.*?)([^\/]+)$|",$request_path,$res);
  }

  $filepath = $res[3];
  $filename = $res[4];
  $filepath = preg_replace("|\/+|", "/", $filepath);



  if (preg_match("/^(.*?)\.(.*?)(\.\w+)$/", $filename, $rs)) {
    $genmodename = $rs[2];
    $filename = $rs[1];
    $filetype = $rs[3];
    $mode_presets = array('thumb'=>array('code'=>'cr84x60'),'main'=>array('code'=>'590x0'));
    if (!$mode_presets[$genmodename]) {
      //	echo "Bad mode";
      return;
    } else {
      $genmode = $mode_presets[$genmodename];
    }


    if (preg_match('/video/', $filename)) {
      $im1 = preg_replace("|\/+|", "/", $REAL_FILES_PATH . "/video/".$filepath."/".$filename.'.flv');

      if (!url_exists($im1)) {
        //		die("no file found [$im1]");
        return;
      }
      $im1 = new ffmpeg_movie($im1);
      $imm = $im1->getFrame(10);
      $im1 = $imm->toGDImage();

      unset($imm);
    } else {

      $im1 = $REAL_FILES_PATH . "/pics".$filepath."/".$filename.'.jpg';
      if (!url_exists($im1)) {
        //		die("no file found [$im1]");
        return;
      }

      $im1 = ImageCreateFromJpeg($im1);

    }


    $_width  = ImageSx($im1);
    $_height = ImageSy($im1);

    if (preg_match("/^([a-z_]{0,})(\d+)(x(\d+))?(\w+)?$/i", $genmode['code'], $rs)) {



      ini_set('memory_limit','32M');
      $op = $rs[1];
      $nw = $rs[2];
      $nh = $rs[4];
      $opt= $rs[5];


      switch ($op) {

        case 'sq':

        $coof = $_height/$_width;
        $nw = floor(sqrt($nw/$coof));
        $nw = $nw>$_width?$_width:$nw;
        $nh = floor($nw*$coof);
        $im2 = ImageCreateTrueColor($nw,$nh);
        ImageCopyResampled($im2,$im1,0,0,0,0,$nw,$nh,$_width,$_height);
        break;

        case 'cr':

        $hotspot = $imdt['hotspot'];



        $hotspot = $hotspot?explode('x',$hotspot):'';
        if ($_height && $_width) {


          if ($nw/$nh > $_width/$_height) {
            $srcx = 0;
            $srcw = $_width;
            $srch = round($srcw*$nh/$nw);
            if ($hotspot) {
              $srcy = $hotspot[1] - $srch/2;
              if ($srcy < 0) $srcy = 0;
              if ($srcy + $srch > $_height) $srcy = $_height - $srch;
            } else {
              $srcy = round(($_height - $srch)/2);
            }
          } else {
            $srcy = 0;
            $srch = $_height;
            $srcw = round($srch*$nw/$nh);
            if ($hotspot) {
              $srcx = $hotspot[0] - $srcw/2;
              if ($srcx < 0) $srcx = 0;
              if ($srcx + $srcw > $_width) $srcx = $_width - $srcw;
            } else {
              $srcx = round(($_width - $srcw)/2);
            }
          }
          $im2 = ImageCreateTrueColor($nw,$nh);
          ImageCopyResampled($im2,$im1,0,0,$srcx,$srcy,$nw,$nh,$srcw,$srch);
        }
        break;

        case 'f':

        default:
        if ($_height && $_width) {
          if (!$nw) {
            $fitwidth = 1;
          } else
          if (!$nh) {
            $fitwidth = 0;
          } else {
            $fitwidth = ($nw/$nh > $_width/$_height);
          }
          if ($fitwidth) {
            $nw = floor($nh*$_width/$_height);
          } else {
            $nh = floor($nw*$_height/$_width);
          }
          $im2 = ImageCreateTrueColor($nw,$nh);
          ImageCopyResampled($im2,$im1,0,0,0,0,$nw,$nh,$_width,$_height);
        }
        break;
      }


      if ($im2) {
        switch ($opt) { // Post-processing according to 'options'

          case 'auth':

          $imdt = GetLanguageStrings(array($imdt),'author');
          $imdt = $imdt[0];
          if ($imdt['author']) {
            $font = SERVER_ROOT() . "php/ttf/arial.ttf";
            $textarr = explode('/','&#169;  '.iconv('koi8-r','utf-8',$imdt['author']));
            $top = $nh;
            do {
              $text = "";
              do {
                $text   = trim(array_pop($textarr)) . ($text?" / $text":"");
                $ntext  = count($textarr)?$textarr[count($textarr)-1]:"";
                $box = Imagettfbbox (8, 0, $font, $text . '/' . $ntext);
                $boxw = abs($box[2]-$box[0]);
              } while ($boxw < $nw && $ntext);
              $text = ($ntext?"/ ":"").$text;
              $box = Imagettfbbox (8, 0, $font, $text);
              Imagettftext  ($im2, 8, 0, $nw - abs($box[2]-$box[0]) - 2, $top - 2, 0x00, $font, $text);
              Imagettftext  ($im2, 8, 0, $nw - abs($box[2]-$box[0]) - 3, $top - 3, 0xFFFFFF, $font, $text);
              $top -= 12;
            } while (count($textarr));
          }
          break;
        }
        $CACHE_BASE = 'php/pics';
        $im1 = SERVER_ROOT() . $CACHE_BASE . $filepath . $filename . '.' . $genmodename . $filetype;
        if (preg_match('/video/', $filename)) {
          imagealphablending($im2,  true);
          $im3 = ImageCreateFromPNG(SERVER_ROOT() . "php/img/play2.png");
          ImageCopy($im2,$im3, 0, 0, 0, 0, ImageSx($im3), ImageSy($im3));
        }
        CreateDirectory(SERVER_ROOT() . $CACHE_BASE . $filepath);
        ImageJpeg($im2, $im1, isset($genmode['quality'])?$genmode['quality']:80);

        //	    readfile($im1);
      } else {
        echo array_check($imdt);
      }
      //	exit();

    }
  }
}


?>

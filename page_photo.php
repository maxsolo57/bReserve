<link rel="stylesheet" href="php/_libs/css/theme/1.8.2/jquery-ui-1.8.2.custom.css" type="text/css" media="screen" charset="UTF-8">
<link rel="stylesheet" href="php/css/elements.css" type="text/css" media="screen" charset="UTF-8">
<link rel="stylesheet" href="php/css/style.css" type="text/css" media="screen" charset="UTF-8">

<script language="javascript" type="text/javascript" src="php/_libs/jquery/jquery-1.4.2.min.js" ></script>
<script language="javascript" type="text/javascript" src="php/_libs/jquery/jquery-ui-1.8.7.custom.min.js" ></script>
<script language="javascript" type="text/javascript" src="php/_libs/jquery/jquery.json-1.3.min.js" ></script>
<script src="php/_libs/jquery/jquery.lightbox-0.5.js" type="text/javascript" charset="UTF-8"></script>

<script src="php/page_photo.js" type="text/javascript" charset="UTF-8"></script>

<?php

global $months_f, $months_eng;
if ($d__['_page_lang']=='eng') { $months_names = $months_eng;  }
else                           { $months_names = $months_f;    }
?>

<div class="arcwrapper">


  <div class=thecolumns>
    <div class=centercol>
      <div class=picbox>
        <div class=curpic>
          <img
          <?php
          if (@count(@$d__["cur_pic"])) {
            foreach((@$d__["cur_pic"]) as $k_1=>$v_1){
              ?>
              src=
              <?php
              echo @$d__["cache_base"];
              echo @$v_1["p"]; ?>/<?php  echo @$v_1["f"];
              ?>.main.jpg
              <?php
            }}
            ?>
            class=bigthumb onclick="wc.NextPic()">

            <img class=loadproxy>
          </div>

          <div class=zoomglass><a href=<?php
          echo @$d__["src_base"];
          if (@count(@$d__["cur_pic"])) { foreach((@$d__["cur_pic"]) as $k_2=>$v_2){ echo @$v_2["p"]; ?>/<?php  echo @$v_2["f"]; ?>.jpg
          <?php
        }}
        ?>
        class=picimga><img src=php/img/zoomglass.png></a>
      </div>

      <div class=likebutt><img class=like src=php/img/like.png></div>
    </div>

    <div class=timeline_box>
      <div class=timeline>
        <table><tbody><tr></tr></tbody></table>
      </div>
    </div>
    <img src=php/img/spacer.gif class=prevbutton><img src=php/img/spacer.gif class=nextbutton>

  </div>

  <div class=rightcol>

    <?php
    if ($d__['cmonth'] < 12) {
      $d__['nextmonth'] = str_pad($d__['cmonth']+1,2,'0',STR_PAD_LEFT);
      $d__['nextyear'] = $d__['cyear'];
      if ($d__['cmonth'] > 1) {
        $d__['prevmonth'] = str_pad($d__['cmonth']-1,2,'0',STR_PAD_LEFT);
        $d__['prevyear'] = $d__['cyear'];
      } else {
        $d__['prevmonth'] = 12;
        $d__['prevyear'] = str_pad($d__['cyear']-1,2,'0',STR_PAD_LEFT);
      }
    } else {
      $d__['nextmonth'] = '01';
      $d__['nextyear'] = str_pad($d__['cyear']+1,2,'0',STR_PAD_LEFT);
      $d__['prevmonth'] = str_pad($d__['cmonth']-1,2,'0',STR_PAD_LEFT);
      $d__['prevyear'] = $d__['cyear'];
    }
    ?>


    <div class=timepicker>
      <div class=calendar>

        <table class=carendaa width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td><a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo "12";  echo @$d__["cmonth"];  echo "15"; ?>">2012</a></td>
            <td><a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo "13";  echo @$d__["cmonth"];  echo "15"; ?>">2013</a></td>
            <td><a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo "14";  echo @$d__["cmonth"];  echo "15"; ?>">2014</a></td>
          </tr>
        </table>

        <table class=carendaa width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td><a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo @$d__["cyear"];  echo "01";  echo "15"; ?>">Январь</a></td>
            <td><a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo @$d__["cyear"];  echo "02";  echo "15"; ?>">Февраль</a></td>
            <td><a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo @$d__["cyear"];  echo "03";  echo "15"; ?>">Март</a></td>
            <td><a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo @$d__["cyear"];  echo "04";  echo "15"; ?>">Апрель</a></td>
          </tr>
          <tr>
            <td><a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo @$d__["cyear"];  echo "05";  echo "15"; ?>">Май</a></td>
            <td><a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo @$d__["cyear"];  echo "06";  echo "15"; ?>">Июнь</a></td>
            <td><a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo @$d__["cyear"];  echo "07";  echo "15"; ?>">Июль</a></td>
            <td><a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo @$d__["cyear"];  echo "08";  echo "15"; ?>">Август</a></td>
          </tr>
          <tr>
            <td><a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo @$d__["cyear"];  echo "09";  echo "15"; ?>">Сентябрь</a></td>
            <td><a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo @$d__["cyear"];  echo "10";  echo "15"; ?>">Октябрь</a></td>
            <td><a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo @$d__["cyear"];  echo "11";  echo "15"; ?>">Ноябрь</a></td>
            <td><a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo @$d__["cyear"];  echo "12";  echo "15"; ?>">Декабрь</a></td>
          </tr>
        </table>


        <table class=carendaa border=0 cellpadding=0 cellspacing=2>
          <tr>
            <th>
              <a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo @$d__["prevyear"];  echo @$d__["prevmonth"];  echo @$d__["cday"]; ?>">&lt;</a>
            </th>
            <th colspan=5>
              <?php
              $now = localtime();
              echo $months_names[$d__['cmonth']-1] . ((($d__['cyear']+2000) != 1900+$now[5])?(" ".($d__['cyear']+2000)):"");
              ?>
            </th>
            <th>
              <a href="?a=<?php  echo @$d__["_page_path"];  echo @$d__["type"];  echo @$d__["nextyear"];  echo @$d__["nextmonth"];  echo @$d__["cday"]; ?>">&gt;</a>
            </th>
          </tr>
          <tr>
            <td class='calendarheader'>Пн</td>
            <td class='calendarheader'>Вт</td>
            <td class='calendarheader'>Ср</td>
            <td class='calendarheader'>Чт</td>
            <td class='calendarheader'>Пт</td>
            <td class='calendarheader'>Сб</td>
            <td class='calendarheader'>Вс</td>
          </tr>
          <tr>
            <?php $lastdw = (($d__['cmonth_ldw'] - $d__['cmonth_ld'] % 7 + 1 + 7) % 7);
            for ($w=0; $w<$lastdw; $w++) {
              ?>
              <td></td>
            <?php }
            $i=0;
            while ($i < $d__['cmonth_ld']) {
              if (!(($w+$i)%7)) echo "</tr>\n<tr>\n";
              echo "<td class='d".$d__['calend'][$i]['day'].(($d__['calend'][$i]['day']==$d__['cday'])?" cur":"")."'>"
              .($d__['calend'][$i]['count']?("<a title='".$d__['calend'][$i]['count']." {$medianames[1]}' href=\"?a={$d__['_page_path']}{$d__['type']}{$d__['cyear']}{$d__['cmonth']}{$d__['calend'][$i]['day']}\">"):"")
              . $d__['calend'][$i]['day']
              .($d__['calend'][$i]['count']?"</a>":"")
              ."</td>\n";
              $i++;
            }
            ?>
          </tr>
        </table>


      </div>
      <div class=today></div>
    </div>
  </div>

  <div class="bestshotsbox">Лучшие фото
    <div class="bestshots"></div>
    <div class="bestshotsmore">еще</div>
  </div>

  <script>
  var TIMELINE_PICS = <?php  echo @$d__["timeline_pics"]+0;?>;
  var BACKENDSCRIPT = "php/webcam_server.php";
  var TIMELINE_ITEM_WIDTH = <?php  echo @$d__["timeline_item_width"]+0;?>;
  var wc = new WebCam();
  wc.init(
    '<?php  echo @$d__["type"]; ?>',
    '<?php  if(@$d__["_page_lang"]=='eng'){?>eng<?php } else { ?>rus<?php } ?>',
    {y:'<?php  echo @$d__["cyear"]; ?>',
    n:'<?php  echo @$d__["cmonth"]; ?>',
    d:'<?php  echo @$d__["cday"]; ?>',
    h:'<?php  echo @$d__["chour"]; ?>',
    m:'<?php  echo @$d__["cmin"]; ?>'},
    [<?php for ($i=0;$i<12;$i++) {echo ($i?",":"") . "'" . mb_substr($months_names[$i],0,3) . "'";} ?>],
    [<?php if (@count(@$d__["prev_timeline"]))
    { foreach((@$d__["prev_timeline"]) as $k_3=>$v_3){?> <?php if($k_3){?>,<?php } ?>{id:'<?php  echo @$v_3["id"]; ?>',
      p:'<?php  echo @$v_3["p"]; ?>', f:'<?php  echo @$v_3["f"]; ?>',
      y:'<?php  echo @$v_3["y"]; ?>', n:'<?php  echo @$v_3["n"]; ?>',
      d:'<?php  echo @$v_3["d"]; ?>', h:'<?php  echo @$v_3["h"]; ?>',
      m:'<?php  echo @$v_3["m"]; ?>'}
    <?php }} ?>
    <?php if(count(@$d__["prev_timeline"])){?>,
      <?php } ?>
    <?php if (@count(@$d__["cur_pic"])) { foreach((@$d__["cur_pic"]) as $k_4=>$v_4){?>
      <?php if($k_4){?>,
        <?php } ?>{id:'<?php  echo @$v_4["id"]; ?>',
        p:'<?php  echo @$v_4["p"]; ?>',
        f:'<?php  echo @$v_4["f"]; ?>',
        y:'<?php  echo @$v_4["y"]; ?>', n:'<?php  echo @$v_4["n"]; ?>', d:'<?php  echo @$v_4["d"]; ?>', h:'<?php  echo @$v_4["h"]; ?>', m:'<?php  echo @$v_4["m"]; ?>' }<?php
      }} ?> <?php if(count(@$d__["next_timeline"])){?>,<?php
      } ?> <?php if (@count(@$d__["next_timeline"])) { foreach((@$d__["next_timeline"]) as $k_5=>$v_5){?>
        <?php if($k_5){?>,<?php } ?>{id:'<?php  echo @$v_5["id"]; ?>',
        p:'<?php  echo @$v_5["p"]; ?>', f:'<?php  echo @$v_5["f"]; ?>', y:'<?php  echo @$v_5["y"]; ?>', n:'<?php  echo @$v_5["n"]; ?>', d:'<?php  echo @$v_5["d"]; ?>', h:'<?php  echo @$v_5["h"]; ?>', m:'<?php  echo @$v_5["m"]; ?>' }<?php
      }} ?>],
      <?php  echo count(@$d__["prev_timeline"]);?>,
      <?php if(@$d__["cur_pic"] && count(@$d__["cur_pic"])){ if (@count(@$d__["cur_pic"]))
        { foreach((@$d__["cur_pic"]) as $k_6=>$v_6){?> {id:'<?php  echo @$v_6["id"]; ?>', p:'<?php  echo @$v_6["p"]; ?>', f:'<?php  echo @$v_6["f"]; ?>'}
        <?php }} } else { ?>{}<?php } ?>,
        '<?php  echo @$d__["_page_path"]; ?>',
        '<?php  echo @$d__["src_base"]; ?>',
        '<?php  echo @$d__["cache_base"]; ?>'
    );

  jQuery(
    function(){
      jQuery('.picimga').lightBox();
      wc.InitTimeline();

    }
  );

  </script>

</div>

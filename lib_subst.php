<?php

global $_S;
global $_F;

$_S = "<?php ";
$_F = "?>";
$cycleindex = 0;


function subst_convert_simple2 ($text,$divider = '$',$keepmissing = 0) {
    global $_S,$_F;
    $res_text = '';
    while (preg_match("/^(.*?)".preg_quote($divider)."([a-z_][\w\.]+)".preg_quote($divider)."(.*?)$/is",$text,$res)) {
	if ($keepmissing) {
	    $res_text .= $res[1] . $divider . $res[2] . $divider;
	} else {
	    $res_text .= $res[1] . $_S . " echo @\$d__";
	    foreach (explode(".", $res[2]) as $indx) {
		$res_text .= is_numeric($indx)?("[$indx]"):("[\"$indx\"]");
	    }
	    $res_text .= "; " . $_F;
	}
	$text =  $res[3];
    }
    return $res_text . $text;
}

function subst_stripexp($exp) {
    global $_S,$_F,$cycleindex;
    $count = 0;
    $exp = preg_replace("/".preg_quote($_S)."=(.*?)".preg_quote($_F)."/","$1",$exp);
    $exp = preg_replace("/".preg_quote($_S)."\s+echo\s+(.*?);?\s{0,}".preg_quote($_F)."/","$1",$exp);
    $exp = preg_replace('/\'(\@?\$\w+\[\"\w+\"\])\'/',"$1",$exp);
    $exp = preg_replace('/\$\-/','_records_list_num',$exp);
    $exp = preg_replace('/\$\@/','_records_count',$exp);
    while (preg_match('/(["\'])(.*?)\1/',$exp,$res) && ($count<1000)) {
	$t = ':'.preg_quote($res[1].$res[2].$res[1]).':';
	$exp = @preg_replace($t,'###'.$count.'###',$exp);
	$arr[$count] = $res[2];
	$count++;
    }
    while (preg_match('/(([a-zA-Z]\w+)\((.*?)\))/',$exp,$res) && ($count<1000)) {
	$t = '/'.preg_replace('|/|','\/',preg_quote($res[1])).'/';
	$exp = preg_replace($t,'#--#'.$count.'#--#'.$res[3].'#-#-#',$exp);
	$arr[$count] = $res[2];
	$count++;
    }
    $exp = preg_replace('/(?<!"|\w|\$|<\?)([a-zA-Z_]\w*)(?!")/','@$d__["\1"]',$exp);
    $exp = preg_replace('/###(\d+)###/e','"\'".$arr[\1]."\'"',$exp);
    $exp = preg_replace('/#\-\-#(\d+)#\-\-#/e','$arr[\1]."("',$exp);
    $exp = preg_replace('/#\-\#\-#/',')',$exp);
    if (preg_match("/^[a-zA-Z]\w+$/",$exp)) {
	$exp = '@$d__['.$exp.']';
    }
    return $exp;
}

function subst_convert($text,$data = array(), $options = array()) {
    global $_S,$_F,$cycleindex,$page_data;
    global $LAST_PROCESSED_TEMPLATE;


    $res = array();
    $divider = @$options['divider']?$options['divider']:'$';
    $res_text = '';
    $res = array();
    $text = preg_replace("/\<\!\-\-\-(.*?)\-\-\-\>/s","",$text);
    $text = preg_replace("/(\<\?(xml)(.*?)\?\>)/","<?php echo'<'.'?'; ?>$2$3<?php echo'?'.'>'; ?>",$text);
    $text = preg_replace("/".preg_quote($divider)."(\(module\s+\w+(\(.*?\))?\))".preg_quote($divider)."/",'#%#%#$1#%#%#',$text);
    $text = preg_replace("/".preg_quote($divider)."(\(group\s+\d+([,\-]\d+)?(,\d+)?\))".preg_quote($divider)."/",'#%#%#$1#%#%#',$text);
    $text = preg_replace("/".preg_quote($divider)."(\(group_if\s+\d+\)\?\".*?\"\:\".*?\")".preg_quote($divider)."/",'#%#%#$1#%#%#',$text);
    if (count($data)) {
	$preg = '/' . preg_quote($divider) .'\(([\"\']?)(\$?)(' . join('|',array_keys($data)).')\2\1\)\?(.)(.*?)\4\:\4(.*?)\4'.preg_quote($divider).'/se';
	$text = preg_replace($preg,'($data[\'$3\'])?\'$5\':\'$6\'',$text);
	$text = preg_replace("/".preg_quote($divider)."(".join('|',array_keys($data)).")".preg_quote($divider)."/ise", "\$data['\$1']",$text);
    }
    $text = subst_convert_simple2($text);
    $safety_count=0;
    while ($safety_count < 2000) {
	$safety_count++;
	if (preg_match('/^(.*?)\\\?\$\((.*?)\)(\\\?\$|\?|\#|\%)(.*?)$/s',$text,$res)) {
	    $res_text .= $res[1];
	    $exp = $res[2];
	    $oper = $res[3];
	    $text = $res[4];
	    switch ($oper) {
		case '$':
		    if (preg_match('/\#(\w+)\((.*?)(?<!\\\)\)/',$exp,$res)) {
			$exp = $res[2];
			$funcname = $res[1];
			switch ($funcname) {
			    case 'show_all_params':
				$exp = "{$_S} echo show_all_params(\$d__); {$_F}";
				break;
			    case 'include':
				if (preg_match("|(.*?)(\[(.*?)\])?$|",$exp,$res)) {
				    if ($LAST_PROCESSED_TEMPLATE) {
					$tmplname = preg_replace("/^_this_\:\:/",$LAST_PROCESSED_TEMPLATE.'::',$res[1]);
					$tmp =  $LAST_PROCESSED_TEMPLATE;
					$exp = get_static_tmpl($tmplname, $page_data['_page_lang']);
					$LAST_PROCESSED_TEMPLATE = $tmp;
				    } else {
					$exp = "--[process error: '_this_' directive cannot be processed]--";
				    }
				    $local_params = array();
				    if (@$res[2]) {
					preg_match_all("|\s{0,}(\w+)\=\'(.*?)(?<!\\\\)\'|",trim($res[2]),$r,PREG_SET_ORDER);
					if (count($r)) {
					    foreach($r as $row) {
						$local_params[$row[1]] = preg_replace('/\\\(.)/',"$1",$row[2]);
					    }
					}
				    }
				    if (count($local_params)) {
					$tmp = "with params ";
					foreach ($local_params as $k=>$v) {$tmp .= "[$k]=[$v] ";}
				    } else {
					$tmp = "";
				    }

				    $exp = "{$_S}\n/*------------# INCLUDE from '$tmplname' $tmp --------------------*/\n{$_F}".subst_convert($exp,$local_params)."{$_S}\n/*------------# END of INCLUDE from '$tmplname'-----------*/\n{$_F}";
				}
				break;
			    case 'require':
				if (preg_match("|(.*?)(\[(.*?)\])?$|",$exp,$res)) {
				    if ($LAST_PROCESSED_TEMPLATE) {
					$tmplname = preg_replace("/^_this_\:\:/",$LAST_PROCESSED_TEMPLATE.'::',$res[1]);
					$tmp =  $LAST_PROCESSED_TEMPLATE;
					$exp = get_static_tmpl($tmplname);
					$LAST_PROCESSED_TEMPLATE = $tmp;
				    } else {
					$exp = "--[process error: '_this_' directive cannot be processed]--";
				    }
				    $local_params = array();
				    if (@$res[2]) {
					preg_match_all("|\s{0,}(\w+)\=\'(.*?)\'|",trim($res[2]),$r,PREG_SET_ORDER);
					if (count($r)) {
					    foreach($r as $row) {
						$local_params[$row[1]] = $row[2];
					    }
					}
				    }
				    if (count($local_params)) {
					$tmp = "with params ";
					foreach ($local_params as $k=>$v) {$tmp .= "[$k]=[$v] ";}
				    } else {
					$tmp = "";
				    }
				    $exp = "{$_S}\n/*------------# INCLUDE from '$tmplname' $tmp --------------------*/\n{$_F}".subst_convert($exp,$local_params)."{$_S}\n/*------------# END of INCLUDE from '$tmplname'-----------*/\n{$_F}";
				}
				break;
			    default:
				$exp = subst_stripexp($exp);
				$exp = "{$_S} echo {$funcname}({$exp}); {$_F}";
			}
			$text = $exp . $text;
		    } else {
			$exp = subst_stripexp($exp);
			$text = "{$_S} echo {$exp};{$_F}" . $text;
		    }
		    break;
		case '?':
		    $exp = subst_stripexp($exp,$data);
		    if (preg_match('/(.)(.*?)\1:\1(.*?)\1\\\?\$(?!d__\[)(.*?)$/s',$text,$res)) {
			$tmp1 = $res[2];
			$tmp2 = $res[3];
			$text = "{$_S}\n/*--IF ($exp)--*/\n if($exp){{$_F}" . $tmp1 . (($tmp2)?("{$_S}\n} else { \n{$_F}". $tmp2):"") . "{$_S}\n} /*-- end of IF ($exp)--*/\n{$_F}" . $res[4];
		    } else {
			$exp = "[Bad IF expression]";
		    }
		    break;
		case '#':
		    if (preg_match('/(.)(.*?)\1\\\?\$(?!d__\[)(.*?)$/s',$text,$res)) {
			$tmp = $res[2];
			$tmp = preg_replace('/__curcycleindex__/',$cycleindex,$tmp);
			$tmp = preg_replace('/(?<!\\\)\$\#(\w+)\$/',"{$_S} echo @\$v_{$cycleindex}[\"$1\"]; {$_F}",$tmp);
			$tmp = preg_replace('/(?<!\\\)\$[\_\-]/',"{$_S} echo \$k_{$cycleindex}; {$_F}",$tmp);
			$tmp = preg_replace('/(?<!\\\)\$\@/',"{$_S} echo count($exp)-1; {$_F}",$tmp);
			$tmp = preg_replace('/\\\\\$/','$',$tmp);
			if (preg_match("/(.*?),(.*?)(,(.*?))?(,(.*?))?$/",$exp,$r2)) {
			    $exp = subst_stripexp($exp);
			    $r2[2] = subst_stripexp($r2[2]);
			    if (isset($r2[4])) {
				$r2[4] = subst_stripexp($r2[4]);
			    }
			    if ($r2[1]=='null') {
				if (@$r2[6]) {
				    $direction = (trim($r2[6])=='--')?0:1;
				} else {
				    $direction = ($r2[2] > $r2[4])?0:1;
				}
				if ($direction) {
				    $text = "{$_S}/*--FOR ($exp)  --*/\n for(\$k_{$cycleindex} = {$r2[2]}; \$k_{$cycleindex} <= {$r2[4]}; \$k_{$cycleindex}++ ){ {$_F}" . $tmp . "{$_S}\n} /*--end of FOREACH ($exp)--*/\n{$_F}" . $res[3];
				} else {
				    $text = "{$_S}/*--FOR ($exp)  --*/\n for(\$k_{$cycleindex} = {$r2[2]}; \$k_{$cycleindex} >= {$r2[4]}; \$k_{$cycleindex}-- ){ {$_F}" . $tmp . "{$_S}\n} /*--end of FOREACH ($exp)--*/\n{$_F}" . $res[3];
				}
			    } else {
				$r2[1] = subst_stripexp($r2[1]);
				$text = "{$_S}/*--FOREACH ($exp)--*/\n if (@is_array({$r2[1]})) { foreach(@array_slice({$r2[1]},{$r2[2]}".(@$r2[3]?(",{$r2[4]}-{$r2[2]}+1"):"").") as \$k_{$cycleindex}=>\$v_{$cycleindex}){{$_F}" . $tmp . "{$_S}\n}} /*--end of FOREACH ($exp)--*/\n{$_F}" . $res[3];
			    }
			} else {
			    $exp = subst_stripexp($exp,$data);
				$text = "{$_S}/*--FOREACH ($exp)--*/\n if (@count({$exp})) { foreach(($exp) as \$k_{$cycleindex}=>\$v_{$cycleindex}){{$_F}" . $tmp . "{$_S}\n}} /*--end of FOREACH ($exp)--*/\n{$_F}" . $res[3];
			}
			$cycleindex++;
		    } else {
			$exp = "[Bad CYCLE expression]";
		    }
		    break;
		case '%':
		    $exp = subst_stripexp($exp);
		    if (preg_match('/(.)(.*?)case\s+(.*?):(.*?)[\n\r]{1,2}\1\\\?\$(?!d__\[)(.*?)$/s',$text,$res)) {
			$text = "{$_S}/*--SWITCH ($exp)--*/\n switch($exp){\ncase '{$res[3]}':{$_F}" . preg_replace("/[\n\r]{1,2}case\s+(.*?):/","{$_S}break;\ncase '$1':{$_F}",$res[4]) . "{$_S}\n} /*--end of SWITCH ($exp)--*/\n{$_F}" . $res[5];
		    } else {
			$text = "[Bad SWITCH expression]";
		    }
		    break;
		}
	    } else {
		$text = $res_text . $text;
		$text = preg_replace("/".preg_quote($_S)."=(\s{0,})(.*?)".preg_quote($_F)."/","$_S=$1$2$_F",$text);
		$text = preg_replace('/\@{2,}\$d__\[/','@$d__[',$text);
		$text = preg_replace("/".preg_quote($_F).preg_quote($_S)."/s", "", $text);
		$text = preg_replace('/%dollar%/i','$',$text);
		return $text;
	    }
	}
	return "[Strange case][$text]";
}

function subst_savetmpl($tmplname,$text) {
    if ($fd=@fopen(SERVER_ROOT()."php/tmplcache/".$tmplname.".php","w")) {
	fwrite($fd,$text);
	fclose($fd);
	chmod(SERVER_ROOT()."php/tmplcache/{$tmplname}.php",0644);
    } else {
	die("Cannot create file '".SERVER_ROOT()."php/tmplcache/{$tmplname}.php' in templates cache folder");
    }
}

function subst_gentmplfunc($tmplname,$text) {
    return "<"."?php\nfunction $tmplname(\$d__){ global \$globals; global \$page_data; ob_start(); //  echo array_check(\$d__);?".">" . $text . "<"."?php \$result_text = ob_get_contents(); ob_end_clean(); return \$result_text; } /*--end of template function '$tmplname'--*/\n?".">";
}

function subst_tmplname_gen($tmplfile) {
    $slash_divider   = "__";
    $section_divider = "__section__";
    $tmplfile = preg_replace("/\//",$slash_divider,$tmplfile);
    $tmplfile = preg_replace("/::/",$section_divider,$tmplfile);
    $tmplfile = preg_replace('/\.\./',"dir_up",$tmplfile);
    $tmplfile = preg_replace('/\./',"_dot_",$tmplfile);
    $tmplfile = preg_replace("/[^\w]/","_",$tmplfile);
    $tmplfile = $slash_divider . $tmplfile . $slash_divider;
    return $tmplfile;
}

?>
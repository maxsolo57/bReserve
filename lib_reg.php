<?php



require_once('lib_subst.php');

$tmpl_buffer = array();
$EXPIRE_BLOCK_TABLE_INTERVAL = "5 minute";
$LAST_PROCESSED_TEMPLATE = '';



function read_from_file ($filename,$section = '',$silent = 0) {

    $root = SERVER_ROOT();
    $text = '';
    echo "<!-- {$_SERVER['REMOTE_ADDR']} -->";
    if ($fd = @fopen("$root$filename","r")) {;
	$text = @fread($fd, filesize(SERVER_ROOT().$filename));
	fclose($fd);
    } else {
	if (!$silent) {
	    $text = "--[error: (file not found) '$root$filename' ]--";
	}
    }
    if ($section != '') {
	if (preg_match('|(\<\!\-\-)?\$_section_start\:\:'.$section.'\s{0,}\$\-+\>?\s{0,}[\n\r]+(.*?)(\<\!\-\-)?\$_section_start\:\:|s',$text,$res)) {
	    $text = $res[2];
	} else {
	    if (!$silent) {
		$text = "--[error: (section not found) '{$section}' not found in '$root$filename' ]--";
	    } else {
		$text = '';
	    }
	}
    }
    return $text;
}

function show_all_params($data) {
    $text = '<table border=1 width=100% cellpadding=0 cellspacing=0 bgcolor=silver style="color:black">';
    foreach ($data as $key=>$val) {
	$text .= '<tr><td valign="top" width=10>';
	if (is_array($val)) {
	    $text .= $key . '(array)</td><td valign=top>' . show_all_params($val);
	} else {
	    $val = preg_replace('/</','&lt;',$val);
	    $val = preg_replace('/>/','&gt;',$val);
	    $text .= $key . '</td><td valign=top>' . $val;
	}
	$text .= '</td></tr>';
    }
    $text .= '</table>';
    return $text;
}


function array_check($a,$notags=0) {
    if (!$notags) {
	if (is_array($a)) {
	    $text = '<table border=1 width=100% cellpadding=0 cellspacing=0 bgcolor=silver style="color:black">';
	    foreach ($a as $key=>$val) {
		$text .= '<tr><td valign="top" width=10>';
		if (is_array($val)) {
		    $text .= $key . '(array)</td><td valign=top>' . array_check($val);
		} else {
		    $val = preg_replace('/</','&lt;',$val);
		    $val = preg_replace('/>/','&gt;',$val);
		    $text .= $key . '</td><td valign=top>' . $val;
		}
		$text .= '</td></tr>';
	    }
	    $text .= '</table>';
	} else {
	    $text = "<table border=1 width=100% cellpadding=0 cellspacing=0 bgcolor=silver style=\"color:black\">$a<tr><td></td></tr></table>";
	}
    } else {
	if (is_array($a)) {
	    $text = '';
	    foreach ($a as $key=>$val) {
		if (is_array($val)) {
		    $text .= str_repeat(" ",($notags-1)*10) . $key .':';
		    $text .= "{ " . str_repeat(" ",($notags-1)*10) . array_check($val,$notags+1) . "}\n";
		} else {
		    $text .=  " " . $key . ':';
		    $val = preg_replace('/</','&lt;',$val);
		    $val = preg_replace('/>/','&gt;',$val);
		    $text .=   $val;
		}
		$text .= "";
	    }
	} else {
	    $text = "$a";
	}
    }
    return $text;
}

function read_tmpl_from_file_core($file, $section = '', $silent = 0) {

    global $tmpl_buffer, $ae_mode;
    if (isset($tmpl_buffer[$file . '____' . $section])) {
	return $tmpl_buffer[$file . '____' . $section];
    } else {
	$text = read_from_file($file . '.tmpl', $section, $silent);
	$tmpl_buffer[$file . '____' . $section] = $text;
	return $text;
    }
}

function read_tmpl_from_file($file, $section = '', $theme, $silent = 0) {

    global $ae_mode;
    if ($theme) {
	$tmpl = read_tmpl_from_file_core("$file$theme", $section, 1);
	if ($tmpl) { return $tmpl; }
	$tmpl = read_tmpl_from_file_core($file, "$section$theme", 1);
	if ($tmpl) { return $tmpl; }
    }

    return read_tmpl_from_file_core($file, $section, $silent);
}

function get_tmpl_old ($tmpl_name, $section_name, $the_theme, $lang = '', $silent = 0) {

    global $ae_mode, $_GET_TMPL_BASEDIR, $LAST_PROCESSED_TEMPLATE;
    if (($lang != '')&&($lang != 'rus')) {

	$filename = $_GET_TMPL_BASEDIR . $tmpl_name . '.' . $lang;
	$text = read_tmpl_from_file($filename, $section_name, $the_theme, 1);
	if ($text) {
	    $text = preg_replace("/\(_this_\:\:/","({$tmpl_name}::",$text);
	    return $text;
	}
	$filename = $_GET_TMPL_BASEDIR . $tmpl_name;
	$text = read_tmpl_from_file($filename, $section_name . '.' . $lang, $the_theme, 1);
	if ($text) {
	    $text = preg_replace("/\(_this_\:\:/","({$tmpl_name}::",$text);
	    return $text;
	}
	$filename = $_GET_TMPL_BASEDIR . $tmpl_name . '_' . $lang;
	$text = read_tmpl_from_file($filename, $section_name, $the_theme, 1);
	if ($text) {
	    $text = preg_replace("/\(_this_\:\:/","({$tmpl_name}_{$lang}::",$text);
	    return $text;
	}
    }
    $LAST_PROCESSED_TEMPLATE = ($tmpl_name=='_this_')?$LAST_PROCESSED_TEMPLATE:$tmpl_name;
    $filename = $_GET_TMPL_BASEDIR  . $tmpl_name;

    $text = read_tmpl_from_file($filename, $section_name, $the_theme, $silent);
    $text = preg_replace("/\(_this_\:\:/", "($tmpl_name::", $text);

    return $text;
}

function fetch_all_data ($res,$key = '') {
    $data = array();
    if (!@mysql_num_rows($res)) {return array();}
    if ($key) {
	while ($r = mysql_fetch_assoc($res)) {
	    $data[$r[$key]] = $r;
	}
    } else {
	while ($r = mysql_fetch_assoc($res)) {
	    array_push($data,$r);
	}
    }
    return $data;
}



function subst_meta ($tmplname,$data = '') {

    if (!$tmplname) {return "";}
    if (!preg_match("/^\w+$/",$tmplname))  {
	$ret =  "--Bad TMPL name $tmplname came with data " . array_check($data);
    } else {
	require_once(SERVER_ROOT()."php/tmplcache/".$tmplname.".php");
	$ret = $tmplname($data);
    }
    return $ret;
}

function subst_meta_simple ($tmplname,$data = '',$divider = '$',$keepmissing = 0) {
    return subst_meta ($tmplname,$data);
}

function get_static_tmpl ($tmpl_name, $lang = '',$silent = 0) {
    global $ae_mode,$the_theme;
    $tmpl = explode('::',trim($tmpl_name));
    $filename = $tmpl[0];
    $sectname = $tmpl[1]."";
    return get_tmpl_old ($filename, $sectname, $the_theme, $lang, $silent);
}

function get_tmpl3 ($file, $sect, $theme, $_lang = '', $silent = 0) {
    global $ae_mode, $cycleindex, $_GET_TMPL_BASEDIR, $the_theme, $page_data; 

    
    if($_GET_TMPL_BASEDIR == '') {
	$_GET_TMPL_BASEDIR = 'php/';
    }
    
    
    $cycleindex = 0;
    $lang = $_lang;
    if (substr($file,strlen($file)-5)==".tmpl") {
	$file = substr($file, 0, strlen($file)-5);
    }
    if ($lang=='rus') { $lang = ""; }

    if ($lang) {
	$tmplname = get_tmpl3($file.".$lang", $sect, $theme, "", 1);
	if (!$tmplname) {
	    $tmplname = get_tmpl3($file, $sect.".$lang", $theme, "", 1);
	}
	if ($tmplname) {

	    return $tmplname;
	}
	$lang = "";
    }
    if ($theme) {
	$tmplname = get_tmpl3($file . $theme, $sect, "", $lang, 1);
	if (!$tmplname) {
	    $tmplname = get_tmpl3($file, $sect . $theme, "", $lang, 1);
	}
    }
    if (!$tmplname) {

	$global_language = @$page_data['_page_lang']?$page_data['_page_lang']:"";
	$tmpl_file_name = $file . ($sect?"::$sect":"");
	$tmplname = subst_tmplname_gen($tmpl_file_name . ($global_language?".{$global_language}":""));

	$src_time = @filemtime(SERVER_ROOT() . $_GET_TMPL_BASEDIR  . $file . '.tmpl');
	$dst_time = @filemtime(SERVER_ROOT() . $_GET_TMPL_BASEDIR  . "/tmplcache/".$tmplname.".php");
	if (!$src_time || ($src_time >= $dst_time))   {

	    $tmpl = get_tmpl_old($file, $sect, "", $global_language, $silent);
	    if ($tmpl) {

		$tmpl = subst_gentmplfunc($tmplname, subst_convert($tmpl));
		subst_savetmpl($tmplname,$tmpl);
	    } else {
		$tmplname = '';
	    }
	}
    }
    return $tmplname;
}

function get_tmpl ($tmpl_file_name, $lang = '', $silent = 0) {

    global $ae_mode, $the_theme, $_GET_TMPL_BASEDIR;
    $tmpl = explode('::',trim($tmpl_file_name));
    $filename = $tmpl[0];
    $sectname = $tmpl[1]."";

    return get_tmpl3 ($filename, $sectname, $the_theme, $lang, $silent);
}

?>
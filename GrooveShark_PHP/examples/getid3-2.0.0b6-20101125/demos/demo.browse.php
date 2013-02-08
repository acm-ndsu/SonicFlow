<?php
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2006 James Heinrich, Allan Hansen                 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2 of the GPL license,         |
// | that is bundled with this package in the file license.txt and is     |
// | available through the world-wide-web at the following url:           |
// | http://www.gnu.org/copyleft/gpl.html                                 |
// +----------------------------------------------------------------------+
// | getID3() - http://getid3.sourceforge.net or http://www.getid3.org    |
// +----------------------------------------------------------------------+
// | Authors: James Heinrich <info¤getid3*org>                            |
// |          Allan Hansen <ah¤artemis*dk>                                |
// +----------------------------------------------------------------------+
// | demo.browse.php                                                      |
// | getID3() demo file - browse directory and display information.       |
// | dependencies: getid3, extras/abstration.php, getid3.css              |
// +----------------------------------------------------------------------+
//
// $Id: demo.browse.php,v 1.7 2006/12/03 19:28:17 ah Exp $


// Set this directory to the root of your audio files - do not set to "/" !
$root_path   = '/data/getid3/';
$root_path   = 'C:/';

// Set to true for 1280+ width
$wide_screen = true;

// Rewrite and check root_path
$root_path = realpath($root_path);
if (!$root_path || ($root_path == '/')) {
	die('$root_path set to non-existing path or / (latter not allowed)');
}


// Define based on screen width
define('GETID3_COMPRESS_LENGTH', $wide_screen ? 28 : 16);
define('GETID3_FILENAME_LENGTH', $wide_screen ? 28 : 20);


// Misc settings
set_time_limit(300);
error_reporting (E_STRICT | E_ALL);
ignore_user_abort(false);


// Include dependencies
require_once('../getid3/getid3.php');
require_once('../extras/abstraction.php');


// Initialize getID3 engine
$getid3 = new getID3;
$getid3->encoding = 'UTF-8';


// Get time in microseconds
function getmicrotime() {
	list($usec, $sec) = explode(' ', microtime());
	return ((float) $usec + (float) $sec);
}


// HTML header
function CommonHeader($heading) {
	header('Content-Type: text/html; charset=UTF-8');

	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
	echo '<html>';
	echo '<head>';
	echo '<title>getID3() - /demos/demo.browse.php (sample script)</title>';
	echo '<meta http-equiv="Content-Style-Type" content="text/css">';
	echo '<link href="getid3.css" type="text/css" rel="stylesheet">';
	echo '</head>';
	echo '<body>';
	echo '<h1>getID3() - '.htmlentities($heading).'</h1>';

	return true;
}


// HTML footer
function CommonFooter() {

	foreach (array ('iconv', 'zlib', 'exif', 'mysql', 'dba') as $ext) {
		$support[] = (extension_loaded($ext) ? '+' : '-') . $ext;
	}

	echo xml_gen::br();
	echo xml_gen::p('getID3() ' . getid3::VERSION . '.<br>PHP ' . phpversion() . ' (' . implode(xml_gen::space(2), $support) . ').');
	echo xml_gen::p(xml_gen::a('http://getid3.sourceforge.net/', 'http://getid3.sourceforge.net'));

	echo '</body></html>';
}


// Compressed output - js alert to show more
function compress($string) {
	static $i;
	$i++;

	$string2 = str_replace('<br>', ', ', $string);

	if (strlen($string2) <= GETID3_COMPRESS_LENGTH) {
		return $string2;
	}

	$string3 = str_replace('<br>', '\\n', addslashes(str_replace('"', "''", $string)));

	return xml_gen::a("javascript:alert('$string3')", substr($string2, 0, GETID3_COMPRESS_LENGTH-2) . '...');
}




//// Show file info or embedded cover
if (@$_GET['filename']) {

//	if (substr($_GET['filename'], 0, 2) != '\\\\')) {
//		$_GET['filename'] = realpath($_GET['filename']);
//	}

	if (!strstr('*'.$_GET['filename'], '*'.$root_path)) {
//        die('ACCESS DENIED to '. $_GET['filename']);
	}

	function dump(&$var) {
		if (!is_array($var)) {
			if (is_int($var)) {
				return number_format($var);
			}
			if (is_bool($var)) {
				return $var ? 'true' : 'false';
			}

			// string
			return $var;
		} else {
			$t = new Table(3, 'class="dump"');
			foreach ($var as $key => $value) {

				$t->data($key);

				// Show cover
				if (($key == 'data') && isset($var['image_mime']) && isset($var['dataoffset'])) {
					$t->data('embedded image');
					$t->data('<img src="demo.browse.php?filename='.urlencode($_GET['filename']).'&amp;show_img='.md5($value).'">');
					break;
				}

				$type = gettype($value);
				$t->data($type);
				if ($type == 'string') {
					echo '('.strlen($value).')';
				}
				elseif ($type == 'array') {
					echo '('.sizeof($value).')';
				}
				$t->data(null, 'class="dump_'.$type.'"');
				echo dump($value);
			}
			$t->done();
		}
	}


	function dump_img(&$var, $md5) {

		if (is_array($var)) {
			foreach ($var as $key => $value) {
				if ($key == 'data'  &&  isset($var['image_mime'])  &&  isset($var['dataoffset'])) {
					if (md5($value) == $md5) {
						header('Content-type: '.$var['image_mime']);
						echo $value;
						break;
					}
				}
				dump_img($value, $md5);
			}
		}
	}

	$getid3->option_tags_images = true;


	// Show embedded cover
	if (@$_GET["show_img"]) {

		try {
			$getid3->Analyze($_GET['filename']);
			dump_img($getid3->info, $_GET['show_img']);
			die();
		}
		catch (Exception $e) {
			echo xml_gen::p('ERROR: '.$e->message);
		}
	}


	// Show file info
	CommonHeader($_GET['filename']);


	$pd = pathinfo($_GET['filename']);
//var_dump($_GET['filename']);
//var_dump($pd);
	$pd = $pd['dirname'];
	echo xml_gen::p('Browse: ' . xml_gen::a($_SERVER['SCRIPT_NAME'].'?directory='.urlencode($pd), $pd));

	try {
		$getid3->Analyze($_GET['filename']);
		dump($getid3->info);
	}
	catch (Exception $e) {
		echo xml_gen::p('ERROR: ' . $e->message);
	}

	CommonFooter();
	die();
}


//// Browse directory

// Fast scan of directories
$getid3->option_accurate_results = false;

// Create dir variable
$dir = @$_GET["directory"] ? realpath($_GET["directory"]) : $root_path;

// Check that path is wihtin root
if (!strstr('*'.$dir, '*'.$root_path)) {
//    die('ACCESS DENIED to '. $dir);
}

// Begin page
CommonHeader($dir);

// MSIE warning
if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
	echo xml_gen::p('MSIE does not display partial tables, please sit back and wait while scanning the directory...');
	echo xml_gen::p('Alternative use demo.browse.dhtml.php instead. It will display results faster.');
	flush();
}

// Read files and directories
if (is_dir($dir)) {
	if ($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			$full_name = "$dir/$file";
			if (is_file($full_name)) {
				$files[$full_name] = $file;
			}
			elseif ($file[0] != '.') {
				$dirs[$full_name] = $file;
			}
	   }
	   closedir($dh);
   }
}


// Create table
$g = new xml_gen;
$t = new Table(10, 'class="table"', ';odd_files;even_files', 'left;left;right;right;left;left;left;left;left;right');

// Output parent dir
if ($dir != $root_path) {
	$t->data(xml_gen::a($_SERVER['SCRIPT_NAME'].'?directory='.urlencode(realpath($dir.'/..')), '[parent directory]'), 'colspan=10');
	$t->end_row();
}


// Output directories
if (@$dirs) {
	asort($dirs);
	foreach ($dirs as $full_name => $short_name) {
		$t->data(xml_gen::a($_SERVER['SCRIPT_NAME'].'?directory='.urlencode($full_name), $short_name), 'colspan=10');
		$t->end_row();
	}
}

// Output files
if (@$files) {
	asort($files);
	$counter = 0;
	foreach ($files as $full_name => $short_name) {

		// Table header
		if (!$counter--) {

			// empty row
			$t->data('&nbsp;');
			$t->end_row();

			$t->data('Filename',       'class="header"');
			$t->data('Format',         'class="header"');
			$t->data('Length',         'class="header"');
			$t->data('Bitrate',        'class="header"');
			$t->data('Audio',          'class="header"');
			$t->data('Artist',         'class="header"');
			$t->data('Title',          'class="header"');
			$t->data('Tags',           'class="header"');
			$t->data('Warnings',       'class="header"');
			$t->data('Scan&nbsp;Time', 'class="header"');
			//$t->data('Edit&nbsp;Tags', 'class="header"');
			$counter = 19;
		}

		$link_name = $short_name;
		if (strlen($short_name) > GETID3_FILENAME_LENGTH) {
			if (preg_match('/^(.*)\.([a-zA-Z0-9]{1,5})$/', $short_name, $r)) {
				$link_name = substr($r[1], 0, GETID3_FILENAME_LENGTH-2-strlen($r[2])) . '...' . $r[2];
			}
			else {
				$link_name = substr($short_name, 0, GETID3_FILENAME_LENGTH-2);
			}
		}
		$t->data(xml_gen::a($_SERVER['SCRIPT_NAME'].'?filename='.urlencode($full_name), $link_name, 'title="'.$short_name.'"'));

		try {

			$time = getmicrotime();
			$getid3->Analyze($full_name);
			$time = getmicrotime() - $time;

			$t->data(@$getid3->info['fileformat']);
			if (@$getid3->info['audio']['dataformat'] && $getid3->info['audio']['dataformat'] != $getid3->info['fileformat']) {
				echo '/' . @$getid3->info['audio']['dataformat'];
			}
			if (@$getid3->info['video']['dataformat'] && $getid3->info['video']['dataformat'] != $getid3->info['fileformat'] && $getid3->info['video']['dataformat'] != @$getid3->info['audio']['dataformat']) {
				echo '/' . @$getid3->info['video']['dataformat'];
			}
			$t->data(@$getid3->info['playtime_string'].xml_gen::space(2));
			$t->data((@$getid3->info['bitrate'] ? number_format($getid3->info['bitrate']/1000) . 'k' : '') . xml_gen::space(2));
			$t->data(@$getid3->info['audio']['sample_rate'] ? number_format($getid3->info['audio']['sample_rate']) . '/' .  (@$getid3->info['audio']['bits_per_sample'] ? $getid3->info['audio']['bits_per_sample'] . '/' : '') .  @$getid3->info['audio']['channels'] : '');

			$artist = $title = '';
			if (@$getid3->info['tags']) {
				foreach ($getid3->info['tags'] as $tag => $tag_info) {
					if (@$getid3->info['tags'][$tag]['artist'] || @$getid3->info['tags'][$tag]['title']) {
						$artist = @implode('<br>', @$getid3->info['tags'][$tag]['artist']);
						$title  = @implode('<br>', @$getid3->info['tags'][$tag]['title']);
						break;
					}
				}
			}
			$t->data(compress($artist));
			$t->data(compress($title));

			$t->data(@implode(",&nbsp;", @array_keys(@$getid3->info['tags'])));

			$t->data(compress(@implode("<br>", @$getid3->info['warning'])));

			$t->data(number_format($time*1000) . ' ms');

			//$t->data();
		}
		catch (Exception $e) {
			$time = getmicrotime() - $time;
			$t->data('ERROR: ' . $e->message, 'class=error colspan=8');
			$t->data(number_format($time*1000) . ' ms');
			//$t->data();
		}

		// send outut to browser
		flush();
	}
}

// Finish table
$t->done();

// Finish page
CommonFooter();

?>

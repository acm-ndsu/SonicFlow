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
// | Authors: James Heinrich <infoקetid3*org>                            |
// |          Allan Hansen <ahסrtemis*dk>                                |
// +----------------------------------------------------------------------+
// | demo.browse.dhtml.php                                                |
// | getID3() demo file - browse directory and display information using  |
// | DHTML techniques compatible with MSIE5 and Mozilla.                   |
// | dependencies: getid3, extras/abstration.php, getid3.css              |
// +----------------------------------------------------------------------+
//
// $Id: demo.browse.dhtml.php,v 1.4 2006/12/03 19:28:17 ah Exp $


// Set this directory to the root of your audio files - do not set to "/" !
$root_path   = '/data/getid3/';

// Set to true for 1280+ width
$wide_screen = true;


// Rewrite and check root_path
$root_path = realpath($root_path);
if (!$root_path || $root_path == '/') {
    die('$root_path set to non-existing path or / (latter not allowed)');
}


// Define based on screen width
define('GETID3_COMPRESS_LENGTH', $wide_screen ? 28 : 16);
define('GETID3_FILENAME_LENGTH', $wide_screen ? 28 : 20);


// Misc settings
set_time_limit(20*3600);
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
    echo "<meta http-equiv='Content-Style-Type' content='text/css'>";
    echo "<link href='getid3.css' type='text/css' rel='stylesheet'>";
    echo '</head>';
    echo '<body>';
    echo "<h1>getID3() - $heading</h1>";
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

    $string3 = str_replace("<br>", "\\n", addslashes(str_replace('"', "''", $string)));

    return xml_gen::a("javascript:alert('$string3')", substr($string2, 0, GETID3_COMPRESS_LENGTH-2) . '...');
}




//// Show file info or embedded cover
if (@$_GET['filename']) {

    $_GET['filename'] = realpath($_GET['filename']);

    if (!strstr('*'.$_GET['filename'], '*'.$root_path)) {
        die('ACCESS DENIED to '. $_GET["filename"]);
    }

    function dump(&$var) {

        if (!is_array($var)) {
            if (is_int($var)) {
                return number_format($var);
            }
            if (is_bool($var)) {
                return $var ? 'true' : 'false';
            }
            return $var;
        }
        else {
            $t = new Table(3, "class=dump");
            foreach ($var as $key => $value) {

                $t->data($key);

                // Show cover
                if ($key == 'data'  &&  isset($var['image_mime'])  &&  isset($var['dataoffset'])) {

                    $t->data('embedded image');
                    $t->data("<img src='demo.browse.php?filename=".urlencode($_GET['filename'])."&show_img=".md5($value)."'>");
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
                $t->data(null, 'class=dump_'.$type);
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
                        header("Content-type: " . $var['image_mime']);
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
            dump_img($getid3->info, $_GET["show_img"]);
            die();
        }
        catch (Exception $e) {
            echo xml_gen::p('ERROR: ' . $e->message);
        }
    }


    // Show file info
    CommonHeader($_GET['filename']);

    $pd = pathinfo($_GET['filename']);
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
    die('ACCESS DENIED to '. $dir);
}

// Begin page
CommonHeader("$dir");

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
$t = new Table(10, "class='table'", ';odd_files;even_files', 'left;left;right;right;left;left;left;left;left;right');

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

// Output file rows
if (@$files) {
    asort($files);
    $counter = $i = 0;
    foreach ($files as $full_name => $short_name) {

        // Table header
        if (!$counter--) {

            // empty row
            $t->data('&nbsp;');
            $t->end_row();

            $t->data('Filename',       'class=header');
            $t->data('Format',         'class=header');
            $t->data('Length',         'class=header');
            $t->data('Bitrate',        'class=header');
            $t->data('Audio',          'class=header');
            $t->data('Artist',         'class=header');
            $t->data('Title',          'class=header');
            $t->data('Tags',           'class=header');
            $t->data('Warnings',       'class=header');
            $t->data('Scan&nbsp;Time', 'class=header');
            //$t->data('Edit&nbsp;Tags', 'class=header');
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
        $i++;
        $t->row_attr("id='row$i'");
        $t->data(xml_gen::a($_SERVER['SCRIPT_NAME'].'?filename='.urlencode($full_name), $link_name, 'title="'.$short_name.'"'));

        $t->data('Scanning in progress... Please wait.', "colspan=8 id=wait$i");
        $t->data(null, "id=scan$i");
        //$t->data();
    }
}

// Finish table
$t->done();

// Finish page
CommonFooter();


// DHTML/PHP update scripts
echo "
<script>

function upd_entry(row_num, format, playtime, bitrate, audio, artist, title, tags, warnings, ms) {

    row = document.getElementById('row' + row_num);

    row.deleteCell(1);

    cell = row.insertCell(1);   cell.innerHTML = format;
    cell = row.insertCell(2);   cell.innerHTML = playtime;
    cell = row.insertCell(3);   cell.innerHTML = bitrate;
    cell = row.insertCell(4);   cell.innerHTML = audio;
    cell = row.insertCell(5);   cell.innerHTML = artist;
    cell = row.insertCell(6);   cell.innerHTML = title;
    cell = row.insertCell(7);   cell.innerHTML = tags;
    cell = row.insertCell(8);   cell.innerHTML = warnings;

    document.getElementById('scan' + row_num).innerHTML = ms;
}

function upd_error(row_num, msg, ms) {

    document.getElementById('wait' + row_num).innerHTML = '<span class=error>ERROR: ' + msg + '</span>';
    document.getElementById('scan' + row_num).innerHTML = ms;

}

</script>
";

function upd_entry($row_num, $format, $playtime, $bitrate, $audio, $artist, $title, $tags, $warnings, $ms) {

    $time = number_format($ms*1000) . " ms";

    $format   = addslashes($format);    // str_replace("\"", "\\\"", $format);
    $artist   = addslashes($artist);    // str_replace("\"", "\\\"", $artist);
    $title    = addslashes($title);     // str_replace("\"", "\\\"", $title);
    $warnings = addslashes($warnings);  // str_replace("\"", "\\\"", $warnings);

    echo "\n<script>upd_entry($row_num, \"$format\", '$playtime', '$bitrate', '$audio', \"$artist\", \"$title\", '$tags', \"$warnings\", '$time');</script>";
    flush();
}

function upd_error($row_num, $msg, $ms) {

    $msg  = str_replace("\"", "\\\"", $msg);
    $time = number_format($ms*1000) . " ms";

    echo "\n<script>upd_error($row_num, \"$msg\", '$time');</script>";
    flush();
}



// Analyze files
if (@$files) {
    $i = 0;
    foreach ($files as $full_name => $short_name) {

        $i++;

        try {

            $time = getmicrotime();
            $getid3->Analyze($full_name);
            $time = getmicrotime() - $time;

            $format = @$getid3->info['fileformat'];
            if (@$getid3->info['audio']['dataformat'] && $getid3->info['audio']['dataformat'] != $getid3->info['fileformat']) {
                $format .= '/' . @$getid3->info['audio']['dataformat'];
            }
            if (@$getid3->info['video']['dataformat'] && $getid3->info['video']['dataformat'] != $getid3->info['fileformat'] && $getid3->info['video']['dataformat'] != @$getid3->info['audio']['dataformat']) {
                $format .= '/' . @$getid3->info['video']['dataformat'];
            }

            $playtime = @$getid3->info['playtime_string'];
            $bitrate  = (@$getid3->info['bitrate'] ? number_format($getid3->info['bitrate']/1000) . 'k' : '');

            $audio    = (@$getid3->info['audio']['sample_rate'] ? number_format($getid3->info['audio']['sample_rate']) . '/' .  (@$getid3->info['audio']['bits_per_sample'] ? $getid3->info['audio']['bits_per_sample'] . '/' : '') .  @$getid3->info['audio']['channels'] : '');

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
            $artist   = compress($artist);
            $title    = compress($title);
            $tags     = @implode(",&nbsp;", @array_keys(@$getid3->info['tags']));
            $warnings = compress(@implode("<br>", @$getid3->info['warning']));

            upd_entry($i, $format, $playtime, $bitrate, $audio, $artist, $title, $tags, $warnings, $time);

        }
        catch (Exception $e) {
            $time = getmicrotime() - $time;

            upd_error($i, $e->message, $time);
        }
    }
}


?>

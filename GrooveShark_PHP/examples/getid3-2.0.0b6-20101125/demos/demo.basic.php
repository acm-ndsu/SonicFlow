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
// | Authors: James Heinrich <infoØgetid3*org>                            |
// |          Allan Hansen <ahØartemis*dk>                                |
// +----------------------------------------------------------------------+
// | demo.basic.php                                                       |
// | getID3() demo file - showing the most basic use of getID3().         |
// +----------------------------------------------------------------------+
//
// $Id: demo.basic.php,v 1.3 2006/11/16 22:11:58 ah Exp $


// Enter your filename here 
$filename = '/data/getid3/aiff_wave.aiff';

// Include getID3() library (can be in a different directory if full path is specified)
require_once('../getid3/getid3.php');

// Initialize getID3 engine
$getid3 = new getID3;

// Tell getID3() to use UTF-8 encoding - must send proper header as well.
$getid3->encoding = 'UTF-8';

// Tell browser telling it use UTF-8 encoding as well.
header('Content-Type: text/html; charset=UTF-8');

// Analyze file 
try {

    $getid3->Analyze($filename);

    // Show audio bitrate and length
    echo 'Bitrate:  ' . @$getid3->info['audio']['bitrate'] . '<br>'; 
    echo 'Playtime: ' . @$getid3->info['playtime_string']  . '<br>';

}
catch (Exception $e) {
    
    echo 'An error occured: ' .  $e->message;
}

?>
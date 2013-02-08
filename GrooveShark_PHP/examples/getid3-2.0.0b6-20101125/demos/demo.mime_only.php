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
// | demo.mime_only.php                                                   |
// | getID3() demo file - scan single file and return only the MIME type. |
// +----------------------------------------------------------------------+
//
// $Id: demo.mime_only.php,v 1.3 2006/11/16 22:11:58 ah Exp $


// Enter your filename here 
$filename = '/data/getid3/aiff_wave.aiff';

// Include getID3() library (can be in a different directory if full path is specified)
require_once('../getid3/getid3.php');

// Initialize getID3 engine
$getid3 = new getID3;

// Do not parse file or tags
$getid3->option_analyze     = false;
$getid3->option_tag_id3v1   = false;
$getid3->option_tag_id3v2   = false;
$getid3->option_tag_lyrics3 = false;
$getid3->option_tag_apetag  = false;

// Analyze file 
try {

    $getid3->Analyze($filename);

    // Show audio bitrate and length
    echo 'Mime-type:  ' . @$getid3->info['mime_type'];

}
catch (Exception $e) {
    
    echo 'An error occured: ' .  $e->message;
}

?>
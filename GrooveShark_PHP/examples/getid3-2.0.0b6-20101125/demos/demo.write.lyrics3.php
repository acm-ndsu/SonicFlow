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
// | demo.write.lyrics3.php                                               |
// | getID3() demo file - showing how to write lyrics3 2.00 tags.         |
// +----------------------------------------------------------------------+
//
// $Id: demo.write.lyrics3.php,v 1.1 2006/11/16 22:39:59 ah Exp $


// Enter your filename here 
$filename = '/data/getid3/test.mp3';

// Include getID3() library (can be in a different directory if full path is specified)
require_once('../getid3/getid3.php');

// Include desired writer module
require_once('../getid3/write.lyrics3.php');

// Instantiate desired tag class
$tw = new getid3_write_lyrics3($filename);

// Attempt to read current tag
if ($tw->read()) {
    print 'File contains tag already; artist is "' . $tw->artist . '"<br>';
}

// Attempt to write new tag  -- NOTE: all values must be in ISO-8859-1
try {
    $tw->title      = 'A new title';
    $tw->artist     = 'A new artist';
    $tw->album      = 'A new album';
    $tw->author     = 'A new author';
    $tw->comment    = 'A new comment';
    $tw->images     = 'image.jpg';
    
    $tw->synched    = true;
    $tw->lyrics     = "[00:02]Let's talk about time\r\n[00:02]tickin' away every day\r\n[00:05]so wake on up before it's gone away\r\n";

    $tw->write();
    print 'New tag written<br>';
}
catch (Exception $e) {
    print $e->message;
}

// Attempt to remove tag
try {
    $tw->remove();
    print 'Tag removed<br>';
}
catch (Exception $e) {
    print $e->message;
}

?>
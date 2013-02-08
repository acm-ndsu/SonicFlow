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
// | demo.write.id3v2.php                                                 |
// | getID3() demo file - showing how to write id3v2 tags with getID3().  |
// +----------------------------------------------------------------------+
//
// $Id: demo.write.id3v2.php,v 1.1 2006/12/03 23:58:31 ah Exp $


// Enter your filename here 
$filename = '/data/getid3/test.mp3';

// Include getID3() library (can be in a different directory if full path is specified)
require_once('../getid3/getid3.php');

// Include desired writer module
require_once('../getid3/write.id3v2.php');

// Instantiate desired tag class
$tw = new getid3_write_id3v2($filename);

// Attempt to read current tag
if ($tw->read()) {
    print 'File contains tag already; artist is "' . $tw->artist . '"<br>';
}

// Attempt to write new tag  -- NOTE: all values must be in ISO-8859-1
try {
    $tw->title      = 'title';
    $tw->artist     = 'artist';
    $tw->album      = 'album';
    $tw->year       = 2005;
    $tw->genre      = 'Techno';
    unset($tw->genre_id);
    $tw->comment    = 'comment';
    $tw->track      =  11;

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
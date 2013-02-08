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
// | demo.write.apetag.php                                                |
// | getID3() demo file - showing how to write APEtags with getID3().     |
// +----------------------------------------------------------------------+
//
// $Id: demo.write.apetag.php,v 1.3 2006/11/16 22:11:59 ah Exp $


// Enter your filename here 
$filename = '/data/getid3/test.mp3';

// Include getID3() library (can be in a different directory if full path is specified)
require_once('../getid3/getid3.php');

// Include desired writer module
require_once('../getid3/write.apetag.php');

// Instantiate desired tag class
$tw = new getid3_write_apetag($filename);

// Attempt to read current tag
if ($tw->read()) {
    
    print 'File contains tag already; artist is "' . $tw->comments['artist'] . '"<br>';
}

// Attempt to write new tag  -- NOTE: all values must be in UTF-8
try {
    $tw->comments['artist'] = 'getID3() Testing';
    $tw->comments['date']   = array ('1960 (recorded)', '1999 (remastered)');
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
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
// | demo.cache.mysql.php                                                 |
// | Sample script demonstrating the use of the MySQL caching             |
// | extension for getID3()                                               |
// +----------------------------------------------------------------------+
//                              
// $Id: demo.cache.dbm.php,v 1.2 2006/11/02 10:47:59 ah Exp $


require_once('../getid3/getid3.php');
require_once('../getid3/extension.cache.dbm.php');

$getid3 = new getid3_cached_dbm('db4', '/tmp/getid3_cache.dbm', '/tmp/getid3_cache_lock.lock');

$r = $getid3->Analyze('/path/to/files/filename.mp3');

echo '<pre>';
var_dump($r);
echo '</pre>';

// uncomment to clear cache
// $getid3->clear_cache();

?>
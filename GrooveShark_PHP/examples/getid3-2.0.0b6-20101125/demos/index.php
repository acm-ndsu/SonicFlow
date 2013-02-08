<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>getID3() - /demos</title>
<meta http-equiv='Content-Style-Type' content='text/css'>
<link href='getid3.css' type='text/css' rel='stylesheet'>
</head>

<body><h1>getID3() - /demos</h1>        
        

<p>This directory contains a number of examples of how to use <a HREF="http://www.getid3.org">getID3()</a>.</p> 

<p><a href="demo.basic.php">demo.basic.php</a> shows the most basic usage. You only need to set $filename to run it.</p>

<p><a href="demo.browse.php">demo.browse.php</a> lists directory contents and detailed file information. You need to set $root_path. If you have a large screen, set $wide_screen to true as well.</p>

<p><a href="demo.browse.dhtml.php">demo.browse.dhtml.php</a> same as above, but displays results faster using DHTML.</p>

<p><a href="demo.mysql.php">demo.mysql.php</a> shows how to store output from getID3() in a mysql database in a sane way, how to search and create some reports.</p>

<p><a href="demo.cache.mysql.php">demo.cache.mysql.php</a> and <a href="demo.cache.dbm.php">demo.cache.dbm.php</a> shows how to dramatically speed up re-scanning by caching to either a mysql or dbm-style database.</p>

<p><a href="demo.mime_only.php">demo.mime_only.php</a> shows how to get mime type only without parsing the file or tags. You only need to set $filename to run it.</p>

<p><a href="demo.audioinfo.class.php">demo.audioinfo.class.php</a> is an example class, that you can modify to suit your specific needs.</p>

<p>demo.write......php</a> show how to write tags in different formats. You only need to set $filename to run it.</p>

</body>
</html>
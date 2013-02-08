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
// | demo.mysql.php                                                       |
// | getID3() demo file - shows how to store output from getID3() in a    |
// | mysql database in a sane way, how to search and create some reports. |
// | dependencies: getid3                                                 |
// |               demos/demo.mysql.sample_data.sql                       |
// |               extras/mysql.php                                       |
// |               extras/abstraction.php                                 |
// +----------------------------------------------------------------------+
//
// $Id: demo.mysql.php,v 1.5 2006/11/02 10:47:59 ah Exp $



// Include dependencies
require_once('../getid3/getid3.php');
require_once('demo.audioinfo.class.php');
require_once('../extras/mysql.php');
require_once('../extras/abstraction.php');


// Connect to mysql
$dbh = new mysql;
$dbh->database = 'getid3';
$dbh->host     = 'localhost';
$dbh->username = 'getid3';
$dbh->password = 'getid3';
try {
    $dbh->connect();
}
catch (Exception $e) {
    CommonHeader('Could not connect to mysql.');
    echo xml_gen::p('Error returned: ' . $e->getmessage());
    echo xml_gen::p('Set mysql connect parameters in demos/demo.mysql.php ln 38-42.');
    CommonFooter();
    die();
}


// Where to scan recursively for audio files
$audio_path  = '/bulk/music';


// Misc settings
set_time_limit(6000);
error_reporting (E_STRICT | E_ALL);
ignore_user_abort(1);


switch (@$_GET["action"]) {
    
    case 'analyze':
        
        CommonHeader('Scanning directory ' . $audio_path);
        CreateTables();
        AnalyzeDirectory();
        break;
        
        
     case 'create':
        
        CommonHeader('Creating empty database');
        CreateTables();
        echo xml_gen::p('done');
        break;
        
        
    case 'import':
    
        CommonHeader('Importing sample data');    
        echo xml_gen::p('Please wait while importing demo.mysql.sample_data.sql - 97k lines.');
        CreateTables();
        ImportSampleData();
        echo xml_gen::p('done');
        break;
        
        
    case 'genres':
    
        CommonHeader('Genre Distribution');    
        Summary();
        GenreReport();
        break;        
        
        
    case 'artists':
    
        CommonHeader('Genre Distribution');    
        Summary();
        ArtistReport();
        break;        
        

    case 'formats':
    
        CommonHeader('Format/Encoder Distribution');    
        Summary();
        FormatReport();
        FormatEncoderReport();
        FormatEncoderOptionsReport();
        break;        

    
    default:
        
        CommonHeader('demo.mysql.php - menu') ;

        $g = new xml_gen;
        
        echo $g->p('This demo shows one way of storing getID3() data in a database. The demo is not complete and will not be completed by the getID3() development team. If you find it useful and wish to complete it, please send the completed demo to us and we will add it to the next version.');

        echo $g->h3('Initial steps');
        echo $g->ul(
            $g->li($g->a('demo.mysql.php?action=create',  'Empty/Create empty database')) .
            $g->li($g->a('demo.mysql.php?action=import',  'Import sample data') . ' highly recommended - then check out the reports') .
            $g->li($g->a('demo.mysql.php?action=analyze', 'Analyze your own audio files') . ' located in ' . $audio_path . ' (change that path in demo.mysql.php ln 56. This will delete all entries in the database!') 
        );
        
        echo $g->h3('Statistics/Reports');
        echo $g->ul(
            $g->li($g->a('demo.mysql.php?action=genres',  'Genre Distribution')) .
            $g->li($g->a('demo.mysql.php?action=artists', 'Artist Distribution')) .
            $g->li($g->a('demo.mysql.php?action=formats', 'Format/Encoder Distribution')) .
            $g->li("List duplicate files by md5data") .
            $g->li("List duplicate files by artist+title and playtime") . 
            $g->li("List files without Genre, title, ...")
        );
        
        echo $g->h3('Searching');
        echo $g->ul(
            $g->li('Search Metadata/Tags') 
        );
        

}
CommonFooter();
die();



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



function CreateTables() {
    
    global $dbh;
    
    $dbh->query("DROP TABLE IF EXISTS getid3_file");
    $dbh->query("DROP TABLE IF EXISTS getid3_comment");
    $dbh->query("DROP TABLE IF EXISTS getid3_format_name");
    $dbh->query("DROP TABLE IF EXISTS getid3_bitrate_mode");
    $dbh->query("DROP TABLE IF EXISTS getid3_channel_mode");
    $dbh->query("DROP TABLE IF EXISTS getid3_encoder_options");
    $dbh->query("DROP TABLE IF EXISTS getid3_encoder_version");
    $dbh->query("DROP TABLE IF EXISTS getid3_tag");
    $dbh->query("DROP TABLE IF EXISTS getid3_field");
    $dbh->query("DROP TABLE IF EXISTS getid3_value");
    $dbh->query("CREATE TABLE getid3_file            (id int(11) NOT NULL auto_increment, filename varchar(255) NOT NULL default '', filemtime int(11) NOT NULL default '0', filesize int(11) NOT NULL default '0', format_name_id int(11) NOT NULL default '0', encoder_version_id int(11) NOT NULL default '0', encoder_options_id int(11) NOT NULL default '0', bitrate_mode_id int(11) NOT NULL default '0', channel_mode_id int(11) NOT NULL default '0', sample_rate int(11) NOT NULL default '0', bits_per_sample int(11) NOT NULL default '0', lossless tinyint(4) NOT NULL default '0', playtime float NOT NULL default '0', avg_bit_rate float NOT NULL default '0', md5data varchar(32) NOT NULL default '', replaygain_track_gain float NOT NULL default '0', replaygain_album_gain float NOT NULL default '0', PRIMARY KEY  (id), UNIQUE KEY filename (filename), KEY md5data (md5data), KEY format_name_id (format_name_id), KEY encoder_version_id (encoder_version_id), KEY encoder_options_id (encoder_options_id), KEY bitrate_mode_id (bitrate_mode_id), KEY channel_mode_id (channel_mode_id)) TYPE=MyISAM");
    $dbh->query("CREATE TABLE getid3_comment         (id int(11) NOT NULL auto_increment, file_id int(11) NOT NULL default '0', tag_id int(11) NOT NULL default '0', field_id int(11) NOT NULL default '0', value_id int(11) NOT NULL default '0', PRIMARY KEY  (id), KEY file_id (file_id), KEY tag_id (tag_id), KEY field_id (field_id), KEY value_id (value_id)) TYPE=MyISAM");
    $dbh->query("CREATE TABLE getid3_format_name     (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM");
    $dbh->query("CREATE TABLE getid3_bitrate_mode    (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM");
    $dbh->query("CREATE TABLE getid3_channel_mode    (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM");
    $dbh->query("CREATE TABLE getid3_encoder_options (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM");
    $dbh->query("CREATE TABLE getid3_encoder_version (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM");
    $dbh->query("CREATE TABLE getid3_tag             (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM");
    $dbh->query("CREATE TABLE getid3_field           (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM");
    $dbh->query("CREATE TABLE getid3_value           (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM");
}



function ImportSampleData() {

    global $dbh;

    $fp = fopen('demo.mysql.sample_data.sql', 'r');
    $i = 0;
    while (!feof($fp)) {          
        $line = fgets($fp, 4096);
        if ($line == "\n") {
            continue;
        }
        $dbh->query(str_replace(";\n", '', $line)); 
        if (!($i%650)) {
            echo '.';
            flush();
        }
        $i++;
    }                                 
    fclose($fp);                  
}



// Recursive Directory Scanning 
function ScanDirectory(&$path, &$files) {
    
    if (!is_dir($path)) {
        throw new getid3_exception('Directory ' . $path . ' does not exist.');
    }
    
    if (!is_readable($path)) {
        throw new getid3_exception('Directory ' . $path . ' is not readable by PHP.');
    }
    
    // Read files and directories
    if ($dh = opendir($path)) {
        while (($file = readdir($dh)) !== false) {
            $filename = "$path/$file";
            if (is_file($filename)) {
                $files[$filename] = $file;
            }
            elseif ($file[0] != '.') {
                ScanDirectory($filename, $files);
            }
       }
       closedir($dh);
    }
}



// Look up or insert value in lookup table
function Lookup($name, $table) {

    if (!$name) {
        return 0;
    }
    
    // NOTE: It might be a very good idea to use some memory for caching in order to save queries.

    $name = addslashes($name);

    global $dbh;
    
    $dbh->query("select id from getid3_$table where name='$name'");
    if ($dbh->next_record()) {
        return $dbh->f('id');
    }
    
    $dbh->query("insert into getid3_$table (name) values ('$name')");
    return $dbh->insert_id();
}



function AnalyzeDirectory() {
    
    global $audio_path, $dbh;
    
    // Scan $audio_path
    try {
    
        // Build array containing filenames
        $files = array ();
        ScanDirectory($audio_path, $files);
        
        // Initialize getID3 engine
        $getid3 = new getID3;
        $getid3->encoding               = 'UTF-8';
        $getid3->option_md5_data        = true;
        $getid3->option_md5_data_source = true;
        
        // Scan all files
        foreach ($files as $filename => $name) {
    
            try {
                $getid3->Analyze($filename);
                
                if (!@$getid3->info['audio']) {
                    xml_gen::p($name . ' skipped - not an audio file.');
                    continue;
                }
        
                // Extract data            
                $filemtime          = filemtime($filename);
                $filesize           = filesize($filename);
                $filename_sls       = addslashes(utf8_encode($filename));
                
                $format_name        = @$getid3->info['fileformat'] . (@$getid3->info['audio']['dataformat'] != @$getid3->info['fileformat'] ? '/' . @$getid3->info['audio']['dataformat'] : '' );
                $format_name_id     = Lookup($format_name,                               'format_name');
                $encoder_version_id = Lookup(@$getid3->info['audio']['encoder'],         'encoder_version');
                $encoder_options_id = Lookup(@$getid3->info['audio']['encoder_options'], 'encoder_options');
                $bitrate_mode_id    = Lookup(@$getid3->info['audio']['bitrate_mode'],    'bitrate_mode');
                $channel_mode_id    = Lookup(@$getid3->info['audio']['channelmode'],     'channel_mode');
                
                $sample_rate        =   (int)@$getid3->info['audio']['sample_rate'];
    	        $bits_per_sample    =   (int)@$getid3->info['audio']['bits_per_sample'];
    	        $lossless           =   (int)@$getid3->info['audio']['lossless'];
    
    	        $playtime           = (float)@$getid3->info['playtime_seconds'];
    	        $avg_bit_rate  	    = (float)@$getid3->info['bitrate'];
    	        $rg_track_gain  	= (float)@$getid3->info['replay_gain']['track']['adjustment'];
    	        $rg_album_gain      = (float)@$getid3->info['replay_gain']['album']['adjustment'];
    	        
    	        $md5data  	    = addslashes(@$getid3->info['md5_data_source'] ? @$getid3->info['md5_data_source'] : @$getid3->info['md5_data']);
                
                // Insert file entry
                $dbh->query("insert into getid3_file (filename, filemtime, filesize, format_name_id, encoder_version_id, encoder_options_id, bitrate_mode_id, channel_mode_id, sample_rate, bits_per_sample, lossless, playtime, avg_bit_rate, md5data, replaygain_track_gain, replaygain_album_gain) values ('$filename_sls', $filemtime, $filesize, $format_name_id, $encoder_version_id, $encoder_options_id, $bitrate_mode_id, $channel_mode_id, $sample_rate, $bits_per_sample, $lossless, $playtime, $avg_bit_rate, '$md5data', $rg_track_gain, $rg_album_gain)");
                $file_id = $dbh->insert_id();
                
                // Loop thru tags
                if (@$getid3->info['tags']) {
                    foreach ($getid3->info['tags'] as $tag_name => $tag_data) {
                        
                        // Loop thru fields
                        foreach ($tag_data as $field_name => $values) {
                            
                            // Loop thru values
                            foreach ($values as $value) {
    
                                $tag_id   = Lookup($tag_name,   'tag');
                                $field_id = Lookup($field_name, 'field');
                                $value_id = Lookup($value,      'value');
                                
                                // Insert comments entry
                                $dbh->query("insert into getid3_comment (file_id, tag_id, field_id, value_id) values ($file_id, $tag_id, $field_id, $value_id)");
                            }
                        }
                    }
                }
                           
                echo xml_gen::p('#' . $file_id . ' - ' . utf8_encode($filename) . ' OK.');
                flush();
            }
            catch (Exception $e) {
                echo xml_gen::p_err($name . ' skipped - getID3() threw the exception: ' . $e->getmessage());
            }
        }
    }
    catch (Exception $e) {
        
        echo xml_gen::p_err('An error occured: ' .  $e->getmessage());
    }
}



function Summary() {
    
    global $dbh;
    
    $dbh->query('select count(id) as counter, sum(filesize)/1024/1024 as sumsize, sum(playtime) as sumplay, sum(avg_bit_rate)/1000 as sumbitr  from getid3_file');
    $dbh->next_record();
    $counter = $dbh->f('counter');
    $sumsize = $dbh->f('sumsize');
    $sumplay = $dbh->f('sumplay');
    $sumbitr = $dbh->f('sumbitr');

    echo xml_gen::h3('General Statistics');
    
    $t = new Table(3, 'class=table', null, 'left;right;left');
    
    $t->data('Total number of tracks:');
    $t->data(number_format($counter));
    $t->data();
    
    $t->data('Total size of tracks:');
    $t->data(number_format($sumsize/1024));
    $t->data('Gb');
    
    $t->data('Average size of tracks:');
    $t->data(number_format($sumsize/$counter));
    $t->data('Mb');
    
    $t->data('Total length of tracks:');
    $t->data(number_format($sumplay/3600/24, 1));
    $t->data('days');
    
    $t->data('Average length of tracks:');
    $t->data(number_format($sumplay/$counter/60, 1));
    $t->data('minutes');
    
    $t->data('Average bitrate:');
    $t->data(number_format($sumbitr/$counter));
    $t->data('kbps');
    
    $dbh->query("select count(C.file_id), V.name from getid3_comment C, getid3_field F, getid3_value V where C.field_id = F.id and F.name = 'artist' and C.value_id = V.id group by V.id");
    $sumarts = $dbh->num_rows();
    
    $t->data('Number of artist:');
    $t->data(number_format($sumarts));
    $t->data();
    
    $dbh->query("select count(C.file_id), V.name from getid3_comment C, getid3_field F, getid3_value V where C.field_id = F.id and F.name = 'genre' and C.value_id = V.id group by V.id");
    $sumgenr = $dbh->num_rows();
    
    $t->data('Number of genres:');
    $t->data(number_format($sumgenr));
    $t->data();
    
    $t->done();
}



function GenreReport() {
    
    echo xml_gen::h3('Genre Distribution - (a file can have multiple genres).');
    
    global $dbh;
    
    $t = new Table(9, 'class=table', null, 'left;right;left;right;left;right;left;right;left');
    
    $dbh->query("select V.name, count(X.id) as counter, sum(X.filesize)/1024/1024/1024 as sumsize, sum(X.playtime)/3600 as sumplay, sum(X.avg_bit_rate)/1000 as sumbitr from getid3_comment C, getid3_field F, getid3_value V, getid3_file X where C.field_id = F.id and F.name = 'genre' and C.value_id = V.id and C.file_id = X.id group by V.id  order by V.name");
    
    while ($dbh->next_record()) {
        
        $t->data($dbh->f('name'));
        $t->data(number_format($dbh->f('counter')));
        $t->data('files' . xml_gen::space(3));
        
        $t->data(number_format($dbh->f('sumsize')));
        $t->data('Gb'. xml_gen::space(3));
        
        $t->data(number_format($dbh->f('sumplay'), 1));
        $t->data('hours'. xml_gen::space(3));
        
        $t->data(number_format($dbh->f('sumbitr')/$dbh->f('counter')));
        $t->data('kbps (avg)');
    }
    
    $t->done();
}



function ArtistReport() {
    
    echo xml_gen::h3('Artist Distribution - (a file can have multiple artists).');
    
    global $dbh;
    
    $t = new Table(9, 'class=table', null, 'left;right;left;right;left;right;left;right;left');
    
    $dbh->query("select V.name, count(X.id) as counter, sum(X.filesize)/1024/1024/1024 as sumsize, sum(X.playtime)/3600 as sumplay, sum(X.avg_bit_rate)/1000 as sumbitr from getid3_comment C, getid3_field F, getid3_value V, getid3_file X where C.field_id = F.id and F.name = 'artist' and C.value_id = V.id and C.file_id = X.id group by V.id order by V.name");
    
    while ($dbh->next_record()) {
        
        $t->data($dbh->f('name'));
        $t->data(number_format($dbh->f('counter')));
        $t->data('files' . xml_gen::space(3));
        
        $t->data(number_format($dbh->f('sumsize')));
        $t->data('Gb'. xml_gen::space(3));
        
        $t->data(number_format($dbh->f('sumplay'), 1));
        $t->data('hours'. xml_gen::space(3));
        
        $t->data(number_format($dbh->f('sumbitr')/$dbh->f('counter')));
        $t->data('kbps (avg)');
    }
    
    $t->done();
}




function FormatReport() {
    
    echo xml_gen::h3('File Format Distribution');
    
    global $dbh;
    
    $t = new Table(9, 'class=table', null, 'left;right;left;right;left;right;left;right;left');
    
    $dbh->query("select N.name, count(X.id) as counter, sum(X.filesize)/1024/1024/1024 as sumsize, sum(X.playtime)/3600 as sumplay, sum(X.avg_bit_rate)/1000 as sumbitr from getid3_file X, getid3_format_name N where N.id = X.format_name_id group by N.id order by N.name");
    while ($dbh->next_record()) {
        
        $t->data($dbh->f('name'));
        
        $t->data(number_format($dbh->f('counter')));
        $t->data('files' . xml_gen::space(3));
        
        $t->data(number_format($dbh->f('sumsize')));
        $t->data('Gb'. xml_gen::space(3));
        
        $t->data(number_format($dbh->f('sumplay'), 1));
        $t->data('hours'. xml_gen::space(3));
        
        $t->data(number_format($dbh->f('sumbitr')/$dbh->f('counter')));
        $t->data('kbps (avg)');
    }
    
    $t->done();
}



function FormatEncoderReport() {
    
    echo xml_gen::h3('Encoder Distribution - (a file can have multiple genres).');
    
    global $dbh;
    
    $t = new Table(10, 'class=table', null, 'left;left;right;left;right;left;right;left;right;left');
    
    $dbh->query("select N.name as format_name, E.name as encoder_version, count(X.id) as counter, sum(X.filesize)/1024/1024/1024 as sumsize, sum(X.playtime)/3600 as sumplay, sum(X.avg_bit_rate)/1000 as sumbitr from getid3_file X, getid3_format_name N, getid3_encoder_version E where N.id = X.format_name_id and E.id = X.encoder_version_id group by N.id,E.id order by N.name, E.name");
    
    while ($dbh->next_record()) {
        
        $t->data($dbh->f('format_name'). xml_gen::space(3));
        $t->data($dbh->f('encoder_version'). xml_gen::space(3));
        
        $t->data(number_format($dbh->f('counter')));
        $t->data('files' . xml_gen::space(3));
        
        $t->data(number_format($dbh->f('sumsize')));
        $t->data('Gb'. xml_gen::space(3));
        
        $t->data(number_format($dbh->f('sumplay'), 1));
        $t->data('hours'. xml_gen::space(3));
        
        $t->data(number_format($dbh->f('sumbitr')/$dbh->f('counter')));
        $t->data('kbps (avg)');
    }
    
    $t->done();
}




function FormatEncoderOptionsReport() {
    
    echo xml_gen::h3('Format/Encoder/Options Distribution');
    
    global $dbh;
    
    $t = new Table(11, 'class=table', null, 'left;left;left;;right;left;right;left;right;left;right;left');
    
    // NOTE: Left joining because encoder_options might not be set....
    $dbh->query("select N.name as format_name, E.name as encoder_version, O.name as encoder_options, count(X.id) as counter, sum(X.filesize)/1024/1024/1024 as sumsize, sum(X.playtime)/3600 as sumplay, sum(X.avg_bit_rate)/1000 as sumbitr from getid3_file X left join getid3_encoder_options O on O.id = X.encoder_options_id, getid3_format_name N, getid3_encoder_version E where N.id = X.format_name_id and E.id = X.encoder_version_id  group by N.id,E.id,O.id order by N.name, E.name");
    
    while ($dbh->next_record()) {
        
        $t->data($dbh->f('format_name'). xml_gen::space(3));
        $t->data($dbh->f('encoder_version'). xml_gen::space(3));
        $t->data($dbh->f('encoder_options'). xml_gen::space(3));
        
        $t->data(number_format($dbh->f('counter')));
        $t->data('files' . xml_gen::space(3));
        
        $t->data(number_format($dbh->f('sumsize')));
        $t->data('Gb'. xml_gen::space(3));
        
        $t->data(number_format($dbh->f('sumplay'), 1));
        $t->data('hours'. xml_gen::space(3));
        
        $t->data(number_format($dbh->f('sumbitr')/$dbh->f('counter')));
        $t->data('kbps (avg)');
    }
    
    $t->done();
}

   
?>

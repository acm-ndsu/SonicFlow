<?php
/*****************************************************************************
 * Sonic Flow library functions. Contains all functions for interacting with *
 * all parts of the Sonic Flow system.                                       *
 *****************************************************************************/

require_once('config.php');
require_once('assets/includes/database.class.php');

$db = new Database('localhost');
$db->connect($config['pg_user'], $config['pg_pass'], $config['pg_db']);
$db->setDieOnFail(true);

// the number of seconds that must pass between each song request
define('SONG_REQUEST_LIMIT', 1800);

require_once('assets/includes/tinysong.php');
require_once('assets/includes/song.class.php');
require_once('assets/includes/server_functions.php');
require_once('assets/includes/self_interaction.php');
require_once('assets/includes/grooveshark.php');
require_once('assets/includes/gracenote.php');

?>

<?php
/*****************************************************************************
 * Sonic Flow library functions. Contains all functions for interacting with *
 * all parts of the Sonic Flow system.                                       *
 *****************************************************************************/

require_once('config.php'); 
require_once('assets/includes/tinysong.php');
require_once('assets/includes/song.class.php');
require_once('assets/includes/server_functions.php');
require_once('assets/includes/self_interaction.php');
require_once('assets/includes/grooveshark.php');
require_once('assets/includes/gracenote.php');

$dbconn = pg_connect(getConnectionString());
pg_prepare($dbconn,'itemCheck','SELECT id FROM $1 WHERE id = $2');
pg_prepare($dbconn,'addSong',  'INSERT INTO songs   VALUES ($1,$2,$3)');
pg_prepare($dbconn,'addArtist','INSERT INTO artists VALUES ($1,$2)');
pg_prepare($dbconn,'addAlbum', 'INSERT INTO albums  VALUES ($1,$2,$3,$4));
?>

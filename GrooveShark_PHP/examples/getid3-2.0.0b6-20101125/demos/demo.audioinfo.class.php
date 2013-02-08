<?php

// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Placed in public domain by Allan Hansen, 2002. Share and enjoy!      |
// +----------------------------------------------------------------------+
// | /demo/demo.audioinfo.class.php                                       |
// |                                                                      |
// | Example wrapper class to extract information from audio files        |
// | through getID3().                                                    |
// |                                                                      |
// | getID3() returns a lot of information. Much of this information is   |
// | not needed for the end-application. It is also possible that some    |
// | users want to extract specific info. Modifying getID3() files is a   |
// | bad idea, as modifications needs to be done to future versions of    |
// | getID3().                                                            |
// |                                                                      |
// | Modify this wrapper class instead. This example extracts certain     |
// | fields only and adds a new root value - encoder_options if possible. |
// | It also checks for mp3 files with wave headers.                      |
// +----------------------------------------------------------------------+
// | Example code:                                                        |
// |   require_once('getid3.php');                                        |
// |   require_once('demo.audioinfo.class.php');                          |
// |   $au = new AudioInfo();                                             |
// |   print_r($au->Info('file.flac');                                    |
// +----------------------------------------------------------------------+
// | Authors: Allan Hansen <ahØartemis*dk>                                |
// +----------------------------------------------------------------------+
//
// $Id: demo.audioinfo.class.php,v 1.1.1.1 2004/08/23 00:01:26 ah Exp $




/**
* Class for extracting information from audio files with getID3().
*/

final class AudioInfo
{
    // Public variables
    public $error;
    public $info;           // can easily be made private - public for debugging only

    // Private variables
    private $result;
    private $getid3;


    // Constructor
    public function __construct($md5_data = false, $sha1_data = false) {

        // Initialize getID3 engine
        $this->getid3 = new getID3;
        $this->getid3->option_md5_data        = $md5_data;
        $this->getid3->option_md5_data_source = true;
        $this->getid3->option_sha1_data       = $sha1_data;
        $this->getid3->encoding               = 'UTF-8';
    }



    // Extract information -
    public function Info($file) {

        // Reset
        $this->error = null;

        // Analyze file
        try {
            $this->info = $this->getid3->analyze($file);
        }
        catch (Exception $e) {
            $this->error = $e->message;
            return;
        }

        // Ignore non-audio files
        if (!@$this->info['audio']) {
            return;
        }

        // Init wrapper object
        $this->result = new stdClass;
        $this->result->format_name     = @$this->info['fileformat'].(@$this->info['audio']['dataformat'] && $this->info['audio']['dataformat'] != @$this->info['fileformat'] ? '/'.@$this->info['audio']['dataformat'] : '');
        $this->result->encoder_version = @$this->info['audio']['encoder'];
        $this->result->encoder_options = @$this->info['audio']['encoder_options'];
        $this->result->bitrate_mode    = @$this->info['audio']['bitrate_mode'];
        $this->result->channels        = @$this->info['audio']['channels'];
        $this->result->sample_rate     = @$this->info['audio']['sample_rate'];
        $this->result->bits_per_sample = @$this->info['audio']['bits_per_sample'];
        $this->result->playing_time    = @$this->info['playtime_seconds'];
        $this->result->avg_bit_rate    = @$this->info['audio']['bitrate'];
        $this->result->tags            = @$this->info['tags'];
        $this->result->comments        = @$this->info['comments'];
        $this->result->warning         = @$this->info['warning'];
        $this->result->md5             = @$this->info['md5_data_source'] ? $this->info['md5_data_source'] : @$this->info['md5_data'];

        // Post getID3() data handling based on file format
        $method = @$this->info['fileformat'].'Info';
        if (@$this->info['fileformat'] && method_exists($this, $method)) {
            $this->$method();
        }

        // No post function defined, make format name upper case
        else {
            $this->result->format_name = strtoupper($this->result->format_name);
        }

        return $this->result;
    }



    // post-getID3() data handling for Wave files.
    private function riffInfo() {

        if ($this->info['audio']['dataformat'] == 'wav') {

            $this->result->format_name = 'Wave';

        } elseif (!preg_match('#^mp[1-3]$#', $this->info['audio']['dataformat'])) {

            $this->result->format_name = 'riff/'.$this->info['audio']['dataformat'];
        }
    }



    // post-getID3() data handling for Monkey's Audio files.
    private function macInfo() {

        $this->result->format_name     = 'Monkey\'s Audio';
    }



    // post-getID3() data handling for Lossless Audio files.
    private function laInfo() {

        $this->result->format_name     = 'La';
    }



    // post-getID3() data handling for Ogg Vorbis files.
    private function oggInfo() {

        if ($this->info['audio']['dataformat'] == 'vorbis') {

            $this->result->format_name     = 'Ogg Vorbis';

        } else if ($this->info['audio']['dataformat'] == 'flac') {

            $this->result->format_name = 'Ogg FLAC';

        } else if ($this->info['audio']['dataformat'] == 'speex') {

            $this->result->format_name = 'Ogg Speex';

        } else {

            $this->result->format_name = 'Ogg '.$this->info['audio']['dataformat'];
        }
    }



    // post-getID3() data handling for Musepack files.
    private function mpcInfo() {

        $this->result->format_name     = 'Musepack';
    }



    // post-getID3() data handling for Real files.
    private function realInfo() {

        $this->result->format_name     = 'Real';
    }


}


?>
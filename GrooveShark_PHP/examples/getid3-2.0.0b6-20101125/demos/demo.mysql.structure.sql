
DROP TABLE IF EXISTS getid3_file;
DROP TABLE IF EXISTS getid3_comment;
DROP TABLE IF EXISTS getid3_format_name;
DROP TABLE IF EXISTS getid3_bitrate_mode;
DROP TABLE IF EXISTS getid3_channel_mode;
DROP TABLE IF EXISTS getid3_encoder_options;
DROP TABLE IF EXISTS getid3_encoder_version; 
DROP TABLE IF EXISTS getid3_tag;
DROP TABLE IF EXISTS getid3_field;
DROP TABLE IF EXISTS getid3_value;

CREATE TABLE getid3_file            (id int(11) NOT NULL auto_increment, filename varchar(255) NOT NULL default '', filemtime int(11) NOT NULL default '0', filesize int(11) NOT NULL default '0', format_name_id int(11) NOT NULL default '0', encoder_version_id int(11) NOT NULL default '0', encoder_options_id int(11) NOT NULL default '0', bitrate_mode_id int(11) NOT NULL default '0', channel_mode_id int(11) NOT NULL default '0', sample_rate int(11) NOT NULL default '0', bits_per_sample int(11) NOT NULL default '0', lossless tinyint(4) NOT NULL default '0', playtime float NOT NULL default '0', avg_bit_rate float NOT NULL default '0', md5data varchar(32) NOT NULL default '', replaygain_track_gain float NOT NULL default '0', replaygain_album_gain float NOT NULL default '0', PRIMARY KEY  (id), UNIQUE KEY filename (filename), KEY md5data (md5data), KEY format_name_id (format_name_id), KEY encoder_version_id (encoder_version_id), KEY encoder_options_id (encoder_options_id), KEY bitrate_mode_id (bitrate_mode_id), KEY channel_mode_id (channel_mode_id)) TYPE=MyISAM;
CREATE TABLE getid3_comment         (id int(11) NOT NULL auto_increment, file_id int(11) NOT NULL default '0', tag_id int(11) NOT NULL default '0', field_id int(11) NOT NULL default '0', value_id int(11) NOT NULL default '0', PRIMARY KEY  (id), KEY file_id (file_id), KEY tag_id (tag_id), KEY field_id (field_id), KEY value_id (value_id)) TYPE=MyISAM;
CREATE TABLE getid3_format_name     (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM;
CREATE TABLE getid3_bitrate_mode    (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM;
CREATE TABLE getid3_channel_mode    (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM;
CREATE TABLE getid3_encoder_options (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM;
CREATE TABLE getid3_encoder_version (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM;
CREATE TABLE getid3_tag             (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM;
CREATE TABLE getid3_field           (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM;
CREATE TABLE getid3_value           (id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', PRIMARY KEY  (id), UNIQUE KEY name (name)) TYPE=MyISAM;
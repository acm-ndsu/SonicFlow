import grooveshark
import grooveshark.classes.song
import sys
import subprocess

config = dict()
config_file = open('/var/www/SonicFlow/config.cfg', 'r')
for line in config:
	line = line.strip()
	if line != '' and line[0] != '#':
		key, value = line.split("=", 2)
		config[key] = value


client = grooveshark.Client()
client.init()
resp = {'Name': sys.argv[1], 'SongID': sys.argv[2], 'ArtistName': sys.argv[3], 'ArtistID': sys.argv[4],\
	'AlbumName': sys.argv[5], 'AlbumID': sys.argv[6], 'CoverArtFilename': sys.argv[7], 'TrackNum': sys.argv[8],\
	'Popularity': sys.argv[9], 'EstimateDuration': sys.argv[10]}
s = grooveshark.classes.song.Song.from_response(resp, client.connection)
s.download(directory='/var/www/SonicFlow/assets/songs', sys.argv[11])

subprocess.call(['psql', '-d', config['pg_db'], '-U', config['pg_user'], '-W', config['pg_pass'], '-c', "UPDATE queue SET cached='1' WHERE id='" + sys.argv[11] + "';"])

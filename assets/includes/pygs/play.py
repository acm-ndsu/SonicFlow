import grooveshark
import grooveshark.classes.song
import sys
import subprocess

client = grooveshark.Client()
client.init()
resp = {'Name': sys.argv[1], 'SongID': sys.argv[2], 'ArtistName': sys.argv[3], 'ArtistID': sys.argv[4],\
	'AlbumName': sys.argv[5], 'AlbumID': sys.argv[6], 'CoverArtFilename': sys.argv[7], 'TrackNum': sys.argv[8],\
	'Popularity': sys.argv[9], 'EstimateDuration': sys.argv[10]}
s = grooveshark.classes.song.Song.from_response(resp, client.connection)
print s
stream = s.stream
print stream
url = stream.url
print url
subprocess.call(['mplayer', '-cache', '8192', '-prefer-ipv4', url])

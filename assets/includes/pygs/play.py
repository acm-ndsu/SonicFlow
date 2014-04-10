import grooveshark
import grooveshark.classes.song
import sys
import subprocess

client = grooveshark.Client()
client.init()

resp = {'Name': sys.argv[0], 'SongID': sys.argv[1]}
s = grooveshark.classes.song.Song.fromResponse(resp, client.connection)

subprocess.call(['mplayer', '-cache', '8192', song.stream.url])

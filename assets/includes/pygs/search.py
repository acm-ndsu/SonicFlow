import grooveshark
import sys
import json

client = grooveshark.Client()
client.init()
songs = []
for s in client.search(sys.argv[1]):
	sdata = {}
	sdata['Name'] = s.__dict__['_name']
	sdata['SongID'] = s.__dict__['_id']
	sdata['ArtistName'] = s.__dict__['_artist_name']
	sdata['ArtistID'] = s.__dict__['_artist_id']
	sdata['AlbumID'] = s.__dict__['_album_id']
	sdata['AlbumName'] = s.__dict__['_album_name']
	sdata['CoverArtFilename'] = s.__dict__['_cover_url']
	sdata['TrackNum'] = s.__dict__['_track']
	sdata['Popularity'] = s.__dict__['_popularity']
	sdata['EstimateDuration'] = s.__dict__['_duration']
	songs.append(sdata);
print json.dumps(songs)

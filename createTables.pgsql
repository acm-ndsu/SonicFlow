/*
  Steps to get postgresql ready:
	1.  Install the postgresql package

	2.a # sudo -u postgres psql template1
	  b   ALTER  USER postgres  WITH ENCRYPTED PASSWORD 'new_password';
	  c   CREATE USER sonicflow WITH ENCRYPTED PASSWORD 'new_password';
	  d   CREATE DATABASE sonicflow;
	  e   GRANT ALL PRIVILEGES ON DATABASE sonicflow TO username;
	  c   \q  -- exits the file

	3.a # nano /etc/postgresql/9.1/main/pg_hba.conf
	  b   find the line toward the bottom:   local	all	postgres	peer
	  c   replace "peer" with: md5
		NOTE: Do the above only if you wish to disable local passwordless
		logins from user postgres. This will make `sudo -u postgres psql
		template1` require the password specified in step 2.a
	  d   do the same for the line:   local	all	all	peer

	4. /etc/init.d/postgresql restart

	5.  # cat createTables | psql -d dbname -U username

	6.a Open postgresql: # psql -d dbname -U username
	  b Verify that tables have been created
	
	7. # apt-get install php5-pgsql
*/

DROP TABLE IF EXISTS queue,songs,albums, artists,users,queuetimes;

/* artistid, name */
CREATE TABLE IF NOT EXISTS artists (
	id	integer PRIMARY KEY,
	name		varchar(255)
);

/* albumid, name, artistid, art */
CREATE TABLE IF NOT EXISTS albums (
	id		integer PRIMARY KEY,
	name		varchar(255),
	artistid	integer references artists(id),
	location	varchar(255)
);

/* songid, title, albumid */
CREATE TABLE IF NOT EXISTS songs (
	id	integer PRIMARY KEY,
	title	varchar(255),
	albumid	integer references albums(id),
	duration integer,
	popularity integer,
	track	integer
);

/* id, songid */
CREATE TABLE IF NOT EXISTS queue (
	id	SERIAL PRIMARY KEY,
	songid	integer references songs(id)
);

/* uid, lastqueued */
CREATE TABLE IF NOT EXISTS users (
	id		integer PRIMARY KEY,
	lastqueued	integer
);

/* songid, lastqueued, uid */
CREATE TABLE IF NOT EXISTS queuetimes (
	songid		integer PRIMARY KEY references songs(id),
	lastqueued	integer NOT NULL,
	uid		integer references users(id)
);


TRUNCATE TABLE artists    CASCADE;
TRUNCATE TABLE albums     CASCADE;
TRUNCATE TABLE songs      CASCADE;
TRUNCATE TABLE queue      CASCADE;
TRUNCATE TABLE users      CASCADE;
TRUNCATE TABLE queuetimes CASCADE;

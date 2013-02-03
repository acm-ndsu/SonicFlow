/*
  Steps to get postgresql ready:
	1.  # apt-get install postgresql

	2.a # sudo -u postgres psql template1
	  b   ALTER USER postgres WITH ENCRYPTED PASSWORD 'new_password';
	  c   CREATE USER username WITH ENCRYPTED PASSWORD 'new_password';
	  d   CREATE DATABASE dbname;
	  e   GRANT ALL PRIVILEGES ON DATABASE dbname TO username;
	  c   \q  -- exits the file

	3.a # nano /etc/postgresql/9.1/main/pg_hba.conf
	  b   find the line toward the bottom:   local	all	postgres	peer
	  c   replace "peer" with: md5
	  d   do the same for the line:   local	all	all	peer

	4. /etc/init.d/postgresql restart

	5.  # cat createTables | postgres -d dbname -U username

	6.a Open postgresql: # postgres -d dbname -U username
	  b Verify that tables have been created
	
	7. # apt-get install php5-pgsql
*/

CREATE TABLE IF NOT EXISTS songs (
	gid	integer PRIMARY KEY,
	artist	varchar(40) NOT NULL,
	title	varchar(40) NOT NULL,
	album	varchar(40)
);

CREATE TABLE IF NOT EXISTS queue (
	id	SERIAL PRIMARY KEY,
	gid	varchar(10) references songs(gid),
	arturl	varchar(255)
);

TRUNCATE TABLE queue CASCADE;
TRUNCATE TABLE songs CASCADE;

INSERT INTO songs (gid, title, album, artist) VALUES
(28470323, "Stark", "Vom Selben Stern", "Ich + Ich"),
(37154545, "Wave No Flag", "After the War", "Mono Inc."),
(27439188, "Führe Mich", "Liebe Ist Für Alle Da", "Rammstein");

/*
	sample Songs:
	gid	title	artist	album	url
	28470323	Stark	Ich + Ich	Vom selben Stern	https://web.content.cddbp.net/cgi-bin/content-thin?id=BDCA88E242E821CF&client=12885248&class=cover&origin=front&size=small&type=image/jpeg&tag=023atoFpXBvFjP1sgbOPlcPHsewW-d0GWWrDIYPgUvqmZ5lM68xd11eA
	37154545	Wave No Flag	Mono Inc.	After the War	https://web.content.cddbp.net/cgi-bin/content-thin?id=6C3D0992E0B2A3AD&client=12885248&class=cover&origin=front&size=small&type=image/jpeg&tag=02mmKMyRNmVy3ylicDYeMxNOeZeMqUcnqCEaswZVhz1fx.miZyDr-mrw
	27439188	Führe mich	Rammstein	Liebe ist für alle da	https://web.content.cddbp.net/cgi-bin/content-thin?id=2C3C8A9DC1F297AA&client=12885248&class=cover&origin=front&size=small&type=image/jpeg&tag=021IhjEiQPa67RB5givTD03vRCjoDY3eTm3k1bc-UnqTbnGyyvMqyw7w
*/

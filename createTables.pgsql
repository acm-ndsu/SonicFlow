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
	gid	varchar(10) PRIMARY KEY,
	artist	varchar(40) NOT NULL,
	title	varchar(40) NOT NULL,
	album	varchar(40)
);

CREATE TABLE IF NOT EXISTS queue (
	id	SERIAL PRIMARY KEY,
	gid	varchar(10) references songs(gid),
	arturl	varchar(255)
);

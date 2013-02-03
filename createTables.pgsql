CREATE TABLE IF NOT EXISTS queue (
	id	SERIAL,
	gid	varchar(10) NOT NULL,
	artist	varchar(40) NOT NULL,
	title	varchar(40) NOT NULL,
	album	varchar(40),
	arturl	varchar(255)
);

CREATE TABLE IF NOT EXISTS results (
	id	SERIAL,
	gid	varchar(10) NOT NULL,
	artist	varchar(40) NOT NULL,
	title	varchar(40) NOT NULL,
	album	varchar(40)
);

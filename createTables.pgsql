CREATE TABLE IF NOT EXISTS results (
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

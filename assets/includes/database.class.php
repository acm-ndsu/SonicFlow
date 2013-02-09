<?php

/**
 * Performs operations on a PostgreSQL database.
 */

class Database {
	
	private $host;
	private $port;
	private $options;
	private $connection;

	public function __construct($host, $port = 5432) {
		$this->host = $host;
		$this->port = $port;
		$this->options = array();
	}

	/**
	 * Adds a long-style user option.
	 *
	 * @param $name The name of the option.
	 * @param $value The value of the option.
	 */
	public function setOption($name, $value) {
		$this->options[$name] = $value;
	}

	/**
	 * Starts the connection to the database.
	 *
	 * @param $user The user to connect with.
	 * @param $pass The password to connect with.
	 * @param $dbname The name of the database to connect to.
	 *
	 * @return Whether the connection was successful.
	 */
	public function connect($user, $pass, $dbname) {
		$this->user = $user;
		$this->dbname = $dbname;
		$connString = $this->buildConnectionString($user, $pass,
			$dbname);
		$this->connection = pg_connect($connString);
		return ($this->connection === FALSE) ? FALSE : TRUE;
	}

	/**
	 * Builds the connection string for sending to pg_connect().
	 *
	 * @paeam $user The user to connect as.
	 * @param $pass The password to use for connecting.
	 * @param $dbname The name of the database to use.
	 *
	 * @return The string to pass to pg_connect() to initialize the
	 * connection.
	 */
	private function buildConnectionString($user, $pass, $dbname) {
		$con = '';
		$con .= "host='{$this->host}' ";
		$con .= "port='{$this->port}' ";
		$con .= "dbname='$dbname' ";
		$con .= "user='$user' ";
		$con .= "password='$pass' ";
		$con .= $this->buildOptionsString();
		return $con;
	}

	/**
	 * Gets the options string. This consists of all the user options
	 * specified as long-style options.
	 *
	 * @return The options string.
	 */
	private function buildOptionsString() {
		$opts = '';
		if (!empty($this->options)) {
			$opts = "options='";
			foreach ($this->options as $name => $val) {
				$opts .= '--' . $name . '=' . $val . ' ';
			}
			$opts .= "'";
		}
		return $opts;
	}

}

?> 

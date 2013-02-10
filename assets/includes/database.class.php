<?php

/**
 * Performs operations on a PostgreSQL database.
 */

class Database {
	
	private $host;
	private $port;
	private $options;
	private $connection;
	private $error;
	private $result;
	private $dieFail;

	public function __construct($host, $port = 5432) {
		$this->host = $host;
		$this->port = $port;
		$this->options = array();
		$this->dieFail = false;
	}

	/**
	 * Prepares a query.
	 *
	 * @param $name The name of the statement.
	 * @param $sql The SQL in the statement.
	 *
	 * @return Whether the statement preparation was successful.
	 */
	public function prepare($name, $sql) {
		$result = pg_prepare($this->connection, $name, $sql);
		$this->setError($result);
		$this->checkDie($result);
		return ($result) ? TRUE : FALSE;
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
	 * Executes a prepared statement.
	 *
	 * @param $statement The name of the statement to execute.
	 * @param $params The parameters to the statement.
	 *
	 * @return Whether the query was successful.
	 */
	public function execute($statement, $params) {
		$result = pg_execute($this->connection, $statement, $params);
		$this->result = $result;
		$this->setError($result);
		$this->checkDie($result);
		return pg_fetch_array($result);
	}

	/**
	 * Gets the number of rows that were affected by the last query.
	 *
	 * @return The number of rows that were affected.
	 */
	public function getAffected() {
		return pg_affected_rows($this->result);
	}

	/**
	 * Gets the results array of the last query.
	 *
	 * @return The results array of the last query, if there were any
	 * results; otherwise NULL.
	 */
	public function getResults() {
		$results = pg_fetch_all($this->result);
		return ($results) ? $results : NULL;
	}

	/**
	 * Gets the error that happened on the last query.
	 *
	 * @return The error that occured, or NULL if there was no error.
	 */
	public function getError() {
		return $this->error;
	}
	
	/**
	 * Sets whether the script should die on failure.
	 */
	public function setDieOnFail($dof) {
		$this->dieFail = $dof;
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
		return ($this->connection) ? TRUE : FALSE;
	}

	/**
	 * Closes the connection to the database.
	 *
	 * @return Whether the close was successful.
	 */
	public function close() {
		return pg_close($this->connection);
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

	/**
	 * Sets the last error of this Database to be the error of the last
	 * result.
	 *
	 * @param $result The result to set the error from.
	 */
	private function setError($result) {
		$e = pg_result_error($result);
		$this->error = ($e) ? $e : NULL;
	}
	
	private function checkDie($result) {
		if ($result === FALSE && $this->dieFail) {
			die ($this->getError());
		}
	}

}

?> 

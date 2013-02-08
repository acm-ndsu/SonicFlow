<?php

// +----------------------------------------------------------------------+
// | PHP version 5.1.4                                                    |
// +----------------------------------------------------------------------+
// | Placed in public domain by Allan Hansen, 2003-2006.                  |
// | Share and enjoy!                                                     |
// +----------------------------------------------------------------------+
// | Class inspired by DB_Sql class from  http://phplib.netuse.de/        |
// +----------------------------------------------------------------------+
// | Updates and other scripts by Allan Hansen here:                      |
// |     http://www.artemis.dk/php/                                       |
// |                                                                      |
// | Similar abstraction layers for other databases are avalible there.   |
// +----------------------------------------------------------------------+
// | mysql.php                                                            |
// | MySQL Abstraction Layer.                                             |
// +----------------------------------------------------------------------+
// | Authors: Allan Hansen <ah@artemis.dk>                                |
// +----------------------------------------------------------------------+
//
// $Id: mysql.php,v 1.3 2007/01/08 09:41:11 ah Exp $





/**
* MySQL Abstraction Layer.
*/

class mysql
{

    // Connect variables
    public $host;
    public $database;
    public $username;
    public $password;
    public $persistent;

    // "Constants"
    public $field_quote      = "`";
    public $table_quote      = "`";
    public $ping_string      = "''";
    public $support_auto_inc = true;

    // "Read-only" variables
    public $record           = array ();
    public $error;

    protected $auto_commit = true;
    protected $transaction = false;
    protected $connection  = false;
    protected $cursor      = false;
    protected $failed      = false;




    /**
    * Contructor
    *
    * @param    string  query   SQL query to run immediately.
    */

    public function __construct($query = false)
    {
        // Make sure that php has mysql support
        if (!function_exists("mysql_pconnect")) {
            die("PHP not compiled with mysql support.");
        }

        if ($query) {
            $this->query($query);
        }
    }






    /**
    * Destructor
    */

    public function __destuct()
    {
        // Destroy cursor
        $this->free();

        // Unset connection - garbage collector will take care of the rest
        unset($this->connection);
    }





    /**
    * Connect to mysql server
    *
    * @return   int                 mysql connection or false
    */

    public function connect()
    {
        // Establish connection, select database
        if (!$this->connection) {

            // Connect
            $connect_function = $this->persistent ? "mysql_pconnect" : "mysql_connect";
            $this->connection = @$connect_function($this->host, $this->username, $this->password, true);
            if (!$this->connection) {
                return $this->fail("connect() failed.");
            }

            // Select database
            if (!@mysql_select_db($this->database, $this->connection)) {
                return $this->fail("Cannot use database $this->database.");
            }

            // Get mysql version
            $this->cursor = mysql_query('select @@version as version', $this->connection);
            $re = mysql_fetch_array($this->cursor);

            // For MySQL5+ set names (charset) to that of database;
            if ($re[0] >= '5') {

                $this->cursor = mysql_query('select @@character_set_database as charset', $this->connection);
                $re = mysql_fetch_array($this->cursor);
                $charset = $re[0];

                mysql_query("set names '$charset'", $this->connection);
            }
        }

        return $this->connection;
    }






    /**
    * Discard the query result / cursor.
    */

    public function free()
    {
        @mysql_free_result($this->cursor);
        $this->cursor = false;
    }






    /**
    * Perform a query.
    *
    * @param    string  query   SQL query.
    * @return   int             mysql cursor or false
    */

    public function query($query, $debug=false)
    {
        // No empty queries
        if (empty($query)) {
            return;
        }

        // Print debug info
        if ($debug) {
            echo "<hr>$query<br>";
            flush();
        }

        // Connect if not already
        if (!$this->connect()) {
            return;
        }

        // New query, discard previous result.
        if ($this->cursor) {
            $this->free();
        }

        // Perform query
        $this->cursor = @mysql_query($query, $this->connection);

        // Error handling - mysql errors - i.e. duplicate key
        if (mysql_errno($this->connection)) {
            $this->error =  mysql_errno($this->connection) . ": " . mysql_error($this->connection);
        }

        // Error handling - invalid queries
        if (!$this->cursor) {
            return $this->fail("Invalid SQL: $query.");
        }

        // Success - return cursor
        return $this->cursor;
    }






    /**
    * Walk result set.
    *
    * @return   bool           Success
    */

    public function next_record()
    {
        // No pending query
        if (!$this->cursor) {
            return $this->fail("next_record() called with no query pending.");
        }

        // Fetch
        $this->record = @mysql_fetch_array($this->cursor);

        // Handle EOD/error
        if (!is_array($this->record)) {

            // Handle error
            if (mysql_errno($this->connection)) {
                $this->error =  mysql_errno($this->connection) . ": " . mysql_error($this->connection);
            }

            // Autofree
            $this->free();
        }

        // Return success
        return is_array($this->record);
    }






    /**
    * Number of affected rows, last SQL operation.
    */

    public function affected_rows()
    {
        return @mysql_affected_rows($this->connection);
    }





    /**
    * Number of rows in result set.
    */

    public function num_rows()
    {
        return @mysql_num_rows($this->cursor);
    }





    /**
    * Number of fields in result set.
    */

    public function num_fields()
    {
        return @mysql_num_fields($this->cursor);
    }





    /**
    * Value of field in result set.
    */

    public function f($name)
    {
        return $this->record[$name];
    }





    /**
    * Insert id from AutoIncrement, last insert operation.
    */

    public function insert_id()
    {
        return @mysql_insert_id ($this->connection);
    }





    /**
    * Begin transaction.
    *
    * @return   bool                 Success
    */

    public function begin()
    {
        // transaction already begun
        if ($this->transaction) {
            return;
        }

        if ($this->auto_commit) {
            $this->auto_commit = false;
            if (!$this->query("set autocommit=0")) {
                return;
            }
        }

        if (!$this->query("begin")) {
            return;
        }
        $this->transaction = true;
        $this->failed      = false;
        return true;
    }





    /**
    * Commit transaction.
    *
    * @return   bool                 Success
    */

    public function commit()
    {
        // transaction not begun
        if (!$this->transaction) {
            return;
        }

        if (!$this->query("commit")) {
            return;
        }

        // commit can succeed if query() failed
        if ($this->failed) {
            $this->rollback();
            return;
        }

        $this->transaction = false;
        return true;
    }





    /**
    * Rollback transaction.
    *
    * @return   bool                 Success
    */

    public function rollback()
    {
        // transaction not begun
        if (!$this->transaction) {
            return;
        }

        if (!$this->query("rollback")) {
            return;
        }
        $this->transaction = false;
        return true;
    }






    /**
    * Return array of tables names
    *
    * @return   array of string     Table names
    */

    public function tables()
    {
        $this->query("show tables");

        $result = array ();

        while ($this->next_record()) {
            $result[] = $this->record[0];
        }

        return $result;
    }





    /**
    * Show table meta data.
    *
    * @param    string  table       Table to show. If false, use query result.
    */

    public function metadata($table = false)
    {
        // Connect if not already
        $this->connect();

        // Init
        $count  = 0;
        $id     = 0;
        $result = array();

        // From table
        if ($table) {
            $id = @mysql_list_fields($this->database, $table);
            if (!$id) {
                return $this->fail("Metadata query failed.");
            }
        }

        // From query result
        else {
            $id = $this->cursor;
            if (!$id) {
                return $this->fail("No query specified.");
            }
        }

        $count = @mysql_num_fields($id);

        // Build result
        for ($i=0; $i<$count; $i++) {
            $result[$i]["table"] = @mysql_field_table ($id, $i);
            $result[$i]["name"]  = @mysql_field_name  ($id, $i);
            $result[$i]["type"]  = @mysql_field_type  ($id, $i);
            $result[$i]["len"]   = @mysql_field_len   ($id, $i);
            $result[$i]["flags"] = @mysql_field_flags ($id, $i);
        }

        // Free the if we were called on a table
        if ($table) {
            @mysql_free_result($id);
        }

        // Return
        return $result;
    }






    /**
    * Fail and produce error handling.
    */

    protected function fail($msg)
    {
        // flag transaction as failed
        $this->failed = true;

        $this->error = @mysql_errno($this->connection) . ": " . @mysql_error($this->connection);

        /*
        // Rollback transaction if applicable
        if ($this->transaction) {
            $this->rollback();
        }
        */

        throw new Exception($msg . ' - ' . $this->error);
    }




    /**
    * Convert mysql datetime to UNIX style int
    */

    public static function datetime2int($timestamp)
    {
        if (preg_match('#^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$#', $timestamp, $r)) {
            list(, $year, $month, $day, $hour, $minute, $second) = $r;
            return mktime($hour, $minute, $second, $month, $day, $year);
        }
    }




    /**
    * Convert UNIX style int to mysql datetime
    */

    public static function int2datetime($int)
    {
        return date('Y-m-d H:i:s', $int);
    }


}

?>
<?php
/**
 * PHP PDO Database wrapper class using Singleton Pattern
 * Performs almost all database related operations
 *
 * @package    database
 * @subpackage
 * @author     Anand Ghaywankar (anand@weboniselab.com)
 * @created 23 June 2013
 * @copyright reseverd (All rights are reserved to weboniselab)
 */


class Database {

    /**
     * Store the single instance of Database class
     * @static Database $instance
     */
    private static $instance;

    /**
     * Database server address
     * @access private
     * @var String $server
     */
    private $server = "localhost"; //database server

    /**
     * Database login name
     * @access private
     * @var String $user
     */
    private $user = "root";

    /**
     * Database login password
     * @access private
     * @var String $pass
     */
    private $pass = "";

    /**
     * Database name to be accessed
     * @access private
     * @var String $database
     */
    private $database = "";

    /**
     * Store error messages for Databases if any
     * @access private
     * @var String $error
     */
    private $error = "db_wrapper";

    /**
     * Store database connection
     * @access private
     * @var PDO $link_id
     */
    private $link_id = 0;

    /**
     * Represents a prepared statement
     * @access private
     * @var PDOStatement $query_id
     */
    private $query_id = 0;

    /**
     * TO set debug mod on
     * @var bool
     * @accedd public
     *
     */
    public $debug = true;


    /**
     * @access private
     *
     */

    private $query;

    /**
     * Constructor to initialize the database connection variables
     * @access private
     * @param String $server
     * @param String $user
     * @param String $pass
     * @param String $database
     * @return Boolean
     */
    private function __construct($server = null, $user = null, $pass = null, $database = null) {

        if ($server == null || $user == null || $database == null) {
            return false;
        }

        $this->server = $server;
        $this->user = $user;
        $this->pass = $pass;
        $this->database = $database;
        return true;
    }


    /**
     * Obtain an instance of Database object
     * @static
     * @param String $server
     * @param String $user
     * @param String $pass
     * @param String $database
     * @return Database If object does not exists create new and return else return already created object
     */
    public static function obtain($server = null, $user = null, $pass = null, $database = null) {
        if (!self::$instance) {
            self::$instance = new Database($server, $user, $pass, $database);
        }
        return self::$instance;
    }


    /**
     * Connect to a Database host and select database using variable initialized above
     * @access public
     * @return Boolean If Database connection successfull return true else return false
     */
    public function connect_pdo() {
        try {
            $this->link_id = new PDO("mysql:host=" . $this->server . ";dbname=" . $this->database . "", $this->user, $this->pass);
            $this->link_id->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//        $this->displayError('connected');

        } catch (PDOException $e) {
            //$this->error = $e->getMessage();
            $this->displayError($e->getMessage());
            //return false;
        }


        $this->server = '';
        $this->user = '';
        $this->pass = '';
        $this->database = '';
        return true;
    }


    /**
     * Close a conection to Database host
     * @access public
     * @return
     */
    public function close() {
        $this->link_id = null;
    }


    /**
     * Select Query Constructior
     * @Params : array Fields
     * @Access : Public
     * @return
     */

    public function select($fields = array('*')) {

        $this->query = 'SELECT ' . join(', ', $fields);
        return $this;
    }

    /**
     * @param $table
     * @param $params
     * @return string
     */

    public function from($table) {
        $this->query .= " FROM `" . trim($table) . "`";

        return $this;
    }


    /**
     * @param $table
     * @param $params
     * @return string
     */

    public function where($condition = array()) {
        if (empty($condition)) {
            $this->query .= ' WHERE 1';
        } else {
            $queryParam = ' WHERE ';
            foreach ($condition AS $key => $value) {
                $field = strtolower($key);
                if (strpos($field, '<') || strpos($field, '>') || strpos($field, '<=') || strpos($field, '>=')) {
                    $queryParam .= $field . " '" . $value . "' AND ";
                } else {
                    $queryParam .= $field . " = '" . $value . "' AND ";
                }
            }

            $this->query .= substr($queryParam, 0, -5);
        }
        return $this;
    }

    /**
     * @param $limit
     * @return string
     *
     */

    public function limit($limit) {
        if ($limit != '') {
            $this->query .= ' LIMIT ' . $limit;
        }

        return $this;
    }

    /**
     * @param array $order
     * @return string
     */

    public function order($fieldName, $order = 'ASC') {
        if (!empty($fieldName)) {
            $this->query .= ' ORDER BY `' . $fieldName . '` ' . strtoupper($order);
        }
        return $this;
    }

    /**
     * @param array $param
     * @return string
     */

    public function join($params = array()) {
        if (!empty($params)) {
            $tables = array_keys($params);
            $fields = array_values($params);

            for ($i = 0; $i < count($tables) - 1; $i++) {
                $this->query .= ' LEFT JOIN ' . '`' . $tables[0] . '`' . ' ON(`' . $tables[$i] . '`.`' . $fields[$i] . '`=`' . $tables[$i + 1] . '`.`' . $fields[$i + 1] . '`)';
            }
        }
        return $this;
    }

    /**
     * @param string
     * @retun string
     */
    public function group($field) {
        if (!empty($field)) {
            $this->query .= " GROUP BY " . $field;
        }
        return $this;
    }

    /**
     * Fetches and returns all the results (not just one row)
     * @access public
     * @param String $sql
     * @param Array $params
     * @return Mixed The complete records as an Associative Array or Empty in case if the query_id (i.e. if the query did not execute) is not set.
     */
    public function get() {
        $sql = $this->query;
//        echo $sql;exit;
        $query_id = $this->execute($sql);

        if ($query_id === false)
            return false;
        $out = array();

        while ($row = $this->fetch_pdo()) {

            $out[] = $row;
        }
        $this->free_result_pdo();

        return $out;
    }


    /**
     * Fetches and returns all the results (not just one row)
     * @access public
     * @param String $sql
     * @param Array $params
     * @return Mixed The complete records as an Associative Array or Empty in case if the query_id (i.e. if the query did not execute) is not set.
     */
    public function getFirst() {
        $sql = $this->query;
        $query_id = $this->execute($sql);

        if ($query_id === false)
            return false;
        $out = $this->fetch_pdo();
        $this->free_result_pdo();
        return $out;
    }


    /**
     * Prepares and executes a sql query
     * @access private
     * @param String $sql
     * @param Array $params
     * @return Boolean Returns TRUE on success or FALSE on failure.
     */
    private function execute($sql, $params = array()) {
        try {
            $this->query_id = $this->link_id->prepare($sql);
            $i = 1;
            foreach ($params as $key => $val) {

                $type = $this->getPDOConstantType($val);
                $this->query_id->bindValue($i, $val, $type);
                ++$i;
            }

            return $this->query_id->execute();
        } catch (PDOException $e) {
            $this->displayError($e->getMessage());
//            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Fetches and returns results one line at a time
     * @access private
     * @return Mixed The first record as an Associative Array or Empty in case if the query_id (i.e. if the query did not execute) is not set.
     */
    private function fetch_pdo() {

        $record = "";

        if (isset($this->query_id)) {
            $record = $this->query_id->fetch(PDO::FETCH_ASSOC);
        }


        return $record;
    }

    /**
     * Frees the resultset.
     * @access private
     * @return
     */
    private function free_result_pdo() {
        $this->query_id->closeCursor();
    }


    /**
     * Does an update query with an array for data and array as a param for the where clause
     * @access public
     * @param String $table
     * @param Array $data is an assoc array with keys are column names and values as the actual values
     * @param Array $where
     * @return Boolean Returns TRUE on success or FALSE on failure.
     */
    public function update($table, $data, $where = array()) {

        if (empty($data))
            return false;
        $q = "UPDATE `$table` SET ";

        foreach ($data as $key => $val) {

            if (empty($val)) ;
            else $q .= "`$key`=?, ";
        }
        $q = rtrim($q, ', ') . ' WHERE ';

        foreach ($where as $key => $val) {
            if (empty($val)) ;
            else $q .= "`$key`=? AND ";
        }
        $q = rtrim($q, 'AND ') . ' ;';

        try {
            $this->query_id = $this->link_id->prepare($q);
            $i = 1;
            foreach ($data as $key => $val) {
                $type = $this->getPDOConstantType($val);
                $this->query_id->bindValue($i, $val, $type);
                ++$i;
            }

            foreach ($where as $key => $val) {

                $type = $this->getPDOConstantType($val);
                $this->query_id->bindValue($i, $val, $type);
                ++$i;
            }

            return $this->query_id->execute();
        } catch (PDOException $e) {
            $this->throughError($e->getMessage());
            return false;
        }
    }

    /**
     * Does an delete query with an array for data and array as a param for the where clause
     * @access public
     * @param String $table
     * @param Array $condition
     * @return Boolean Returns TRUE on success or FALSE on failure.
     */
    public function delete($table, $condition = array()) {
        if (empty($condition))
            return false;
        $q = "DELETE FROM  `$table` WHERE ";
        $i = 1;
        foreach ($condition as $key => $val) {
            if (empty($val)) ;
            else $q .= "`$key`=? AND ";
        }

        $q = rtrim($q, 'AND ') . ' ;';

        try {
            $this->query_id = $this->link_id->prepare($q);
            $i = 1;
            foreach ($condition as $key => $val) {
                $type = $this->getPDOConstantType($val);
                $this->query_id->bindValue($i, $val, $type);
                ++$i;
            }

            return $this->query_id->execute();
        } catch (PDOException $e) {
            $this->throughError($e->getMessage());
            return false;
        }
        return true;
    }



    /**
     * Checks the data type of the value that is passed
     * @access public
     * @param Mixed $value
     * @return Integer Returns the corresponding PDO ID of the data type.
     */
    public function getPDOConstantType($value) {
        switch (true) {
            case is_int($value):
                $type = PDO::PARAM_INT;
                break;
            case is_bool($value):
                $type = PDO::PARAM_BOOL;
                break;
            case is_null($value):
                $type = PDO::PARAM_NULL;
                break;
            default:
                $type = PDO::PARAM_STR;
        }
        return $type;
    }

    /**
     * Throw
     * @param string $msg
     *
     */
    public function displayError($msg = '') {

        // if no debug, done here
        if (!$this->debug) return false; ?>
    <table align="center" border="1" cellspacing="0" style="background:white;color:black;width:80%;">
        <tr>
            <th colspan=2>Database Response</th>
        </tr>
        <tr>
            <td align="right" valign="top">Message:</td>
            <td><?php echo $msg; ?></td>
        </tr>
        <?php if (!empty($this->error)) echo '<tr><td align="right" valign="top" nowrap>MySQL Response:</td><td>' . $this->error . '</td></tr>'; ?>
        <tr>
            <td align="right">Date:</td>
            <td><?php echo date("l, F j, Y \a\\t g:i:s A"); ?></td>
        </tr>
        <?php if (!empty($_SERVER['REQUEST_URI'])) echo '<tr><td align="right">Script:</td><td><a href="' . $_SERVER['REQUEST_URI'] . '">' . $_SERVER['REQUEST_URI'] . '</a></td></tr>'; ?>
        <?php if (!empty($_SERVER['HTTP_REFERER'])) echo '<tr><td align="right">Referer:</td><td><a href="' . $_SERVER['HTTP_REFERER'] . '">' . $_SERVER['HTTP_REFERER'] . '</a></td></tr>'; ?>
    </table>
    <?php
    }
}

?>
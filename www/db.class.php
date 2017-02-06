<?php
class DB{
	public static function query($query)
	{
		return dbClass::getInstance()->query($query);
	}

	public static function escape($str)
	{
		return dbClass::getInstance()->escape($str);
	}

	public static function getInsId()
	{
		return dbClass::getInstance()->getInsId();
	}
}
class dbClass{
	private static $instance = null;

	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			if (function_exists('mysqli_connect')) {
				self::$instance = new mysqliClass();
			} else {
				self::$instance = new mysqlClass();
			}
		}
		return self::$instance;
	}
}
class mysqlClass{
	private $dbLink = '';

	public function __construct()
	{//При объявлении класса сразу коннектицо к БД и сохраняет линк коннекта
		$this->dbLink = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die ('SQL_Error: ' . mysql_error());
		mysql_select_db(DB_NAME, $this->dbLink) or die ('SQL_Error: ' . mysql_error());
		mysql_query('SET NAMES `utf8`', $this->dbLink);
	}

	public function query($query, $indKey = '')
	{
		if (empty($query)) return false;
		if (strpos($query, 'information_schema')) return false;
		$result = mysql_query($query, $this->dbLink);
		$return = array();
		while ($row = mysql_fetch_assoc($result)) {
			if (isset($row[$indKey])) {
				$return[$row[$indKey]] = $row;
			} else {
				$return[] = $row;
			}
		}
		return $return;
	}

	public function escape($str, $stripTags = true)
	{
		if ($stripTags) $str = strip_tags($str);
		return mysql_real_escape_string($str, $this->dbLink);
	}

	public function getInsId()
	{
		return mysql_insert_id($this->dbLink);
	}

	public function __destruct()
	{
		mysql_close($this->dbLink);
	}
}
class mysqliClass{
	private $dbLink = '';

	public function __construct()
	{
		$this->dbLink = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die ('SQLi_Error: cant connect to DB, please configure inc/config.php');
		mysqli_query($this->dbLink, 'SET NAMES `utf8`');
	}

	public function query($query)
	{
		if (empty($query)) return false;
		if (strpos($query, 'information_schema')) return false;
		$result = mysqli_query($this->dbLink, $query);
		$return = array();
		while ($row = @mysqli_fetch_assoc($result)) {
			if (isset($row[$indKey])) {
				$return[$row[$indKey]] = $row;
			} else {
				$return[] = $row;
			}
		}
		return $return;
	}

	public function escape($str)
	{
		return mysqli_real_escape_string($this->dbLink, $str);
	}

	public function getInsId()
	{
		return mysqli_insert_id($this->dbLink);
	}

	public function __destruct()
	{
		if ($this->dbLink) mysqli_close($this->dbLink);
	}
}
<?php
namespace Data;

enum LogType {
	case Notice;
	case Message;
	case Result;
	case Query;
	case Error;
}

class Connection extends \mysqli {
	// Properties
	private string $collation = 'UTF8';
	private bool $debug = false;
	private bool $debugShowDateTime = true;
	private bool $debugBreakLineBeforeQuery = false;

	// Properies setters & getters
	public function setDebug(bool $debug = true) {
		$this -> debug = $debug;
		$this -> logText(LogType::Notice, content: 'mysql.php is in debug mode!');
	}
	public function setCollation(string $collation) {
		$this -> collation = $collation;
	}
	public function configDebug(?bool $showDateTime = null, ?bool $breakLineBeforeQuery = null) {
		if (!is_null($showDateTime))
			$this -> debugShowDateTime = $showDateTime;
		if (!is_null($breakLineBeforeQuery))
			$this -> debugBreakLineBeforeQuery = $breakLineBeforeQuery;
	}

	// Override methods
	public function __construct(string $hostname, string $username, #[\SensitiveParameter] string $password, string $database = null, int $port = null, string $socket = null, bool $debug = false) {
		mysqli_report(MYSQLI_REPORT_ERROR);
		$this -> setDebug($debug);
		$this -> logText(LogType::Message, content: 'Establish connection to database');
		parent::__construct(
			hostname: $hostname,
			username: $username,
			password: $password,
			database: $database,
			port: $port,
			socket: $socket
		);
		if ($this -> connect_error) {
			$this -> logText(LogType::Error, content: $this -> connect_error);
		} else {
			$this -> logText(LogType::Result, content: 'Connection established');
			$this -> query('SET NAMES '.$this -> collation);
		}
	}
	public function query(string $query, int $result_mode = \MYSQLI_STORE_RESULT): \mysqli_result|bool {
		$this -> logText(LogType::Query, content: $query);
		$result = parent::query(
			query: $query,
			result_mode: $result_mode
		);
		$this -> logText(LogType::Error, content: $this -> error);
		$this -> logText(LogType::Result, content: empty($result) ? 'Failed' : 'Successully');
		return $result;
	}

	// Common methods
	private function logText(LogType $type = LogType::Message, string $content = '', bool $onlyDebug = true) {
		if (empty($content)) return;
		if (($onlyDebug && $this -> debug) || (!$onlyDebug)) {
			$dateTime = ($this -> debugShowDateTime) ? date('d/m/y h:i:s A')."\t" : '';
			$beginOfLine = (($this -> debugBreakLineBeforeQuery) && ($type == LogType::Query)) ? PHP_EOL : '';
			$endOfLine = PHP_EOL;
			echo $beginOfLine.$dateTime.($type -> name).': '.$content.$endOfLine;
		}
	}

	// Data processing methods
	public function createTable(string $table, array $columns) {
		$fields = '';
		foreach ($columns as $key => $value) if ($fields) { $fields = "$fields, $key $value"; } else { $fields = "$key $value"; }
		if ($fields) {
			$query = "CREATE TABLE $table ($fields)";
			$result = $this -> query($query);
			if ($result) { return true; }
		}
		return false;
	}
	public function dropTable(string $table) {
		$query = 'DROP TABLE '.$table;
		$result = $this -> query($query);
		return $result;
	}
	public function insertRecord(string $table, array $data) {
		$field = ''; $value = '';
		foreach ($data as $key => $_value) {
			if ($field) { $field = "$field, $key"; } else { $field = $key; }
			if ($value) { $value = "$value, '$_value'"; } else { $value = "'$_value'"; }
		}
		if ($field && $value) {
			$query = "INSERT INTO $table ($field) VALUES ($value)";
			$result = $this -> query($query);
			return $result;
		}
		return false;
	}
	public function updateRecord(string $table, array $data, string $condition) {
		$raw = '';
		foreach ($data as $key => $value) if ($raw) { $raw = "$raw, $key = '$value'"; } else { $raw = "$key = '$value'"; }
		if ($raw) {
			$query = "UPDATE $table SET $raw WHERE $condition";
			$result = $this -> query($query);
			if ($result) { return true; }
		}
		return false;
	}
	public function deleteRecord(string $table, string $condition = '') {
		if ($condition) { $where = 'WHERE'; } else { $where = ''; }
		$query = "DELETE FROM $table $where $condition";
		$result = $this -> query($query);
		return ($result != false);
	}
	public function fetchRecord(string $table, string $fields = '*', string $condition = '', string $order = ''): ?array {
		$data_row = $this -> fetchData(
			table: $table,
			fields: $fields,
			fetchArray: false,
			condition: $condition,
			order: $order,
			page: 1,
			step: 1
		);
		if ($data_row) return $data_row[0];
		return null;
	}
	public function fetchData(string $table, string $fields = '*', bool $fetchArray = false, string $condition = '', string $order = '', int $page = 1, int $step = 100) {
		if ($fetchArray) $fetchType = MYSQLI_NUM; else $fetchType = MYSQLI_ASSOC;
		$fields = explode(',', str_replace(' ', '', $fields));
		$field = '';
		$from = ($page - 1) * $step;
		if ($condition) { $where = 'WHERE'; } else { $where = ''; }
		if ($order) { $orderBy = 'ORDER BY'; } else { $orderBy = ''; }
		foreach ($fields as $value) {
			if ($field) { $field = $field.', '.$value; } else { $field = $value; }
		}
		if ($field) {
			$query = "SELECT $field FROM $table $where $condition $orderBy $order LIMIT $from, $step";
			$result = $this -> query($query);
			if ($result) {
				$data = $result -> fetch_all($fetchType);
				return $data;
			}
		}
		return [];
	}
	public function countRecords(string $table, string $field = '*', string $condition = '') {
		if ($condition) { $where = 'WHERE'; } else { $where = ''; }
		$query = "SELECT COUNT($field) FROM $table $where $condition";
		$result = $this -> query($query);
		if ($result) {
			$data = $result -> fetch_array(MYSQLI_NUM);
			return intval($data[0]);
		}
		return 0;
	}
}

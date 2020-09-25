<?php
define('db_host', 'db_host');
define('db_user', 'db_user');
define('db_pass', 'db_pass');
define('db_name', 'db_name');
define('collation', 'UTF8');

$db = db();
if(isset($_GET['debug'])) { var_dump($db); echo $query.PHP_EOL; }

function db() {
	$db = new mysqli(db_host, db_user, db_pass, db_name);
	if ($db) { $db -> query('SET NAMES '.collation); return $db; }
	echo '<h1>Database error !</h1>'; exit;
	return false;
}
function create_table($table_name, $columns) {
	global $db;
	$fields = '';
	foreach ($columns as $key => $value) if ($fields) { $fields = "$fields, $key $value"; } else { $fields = "$key $value"; }
	if ($fields) {
		$query = "CREATE TABLE $table_name ($fields)";
		if (isset($_GET['debug'])) echo $query.PHP_EOL;
		// return $query;
		$result = $db -> query($query);
		if ($result) { return true; }
	}
	return false;
}
function drop_table($table_name) {
	global $db;
	$query = 'DROP TABLE '.$table_name;
	if (isset($_GET['debug'])) echo $query.PHP_EOL;
	$result = $db -> query($query);
	return $result;
}
function new_record($table_name, $data) {
	global $db;
	$field = '';
	$value = '';
	foreach ($data as $key => $_value) {
		if ($field) { $field = "$field, $key"; } else { $field = $key; }
		if ($value) { $value = "$value, '$_value'"; } else { $value = "'$_value'"; }
	}
	if ($field && $value) {
		$query = "INSERT INTO $table_name ($field) VALUES ($value)";
		if (isset($_GET['debug'])) echo $query.PHP_EOL;
		$result = $db -> query($query);
		return $result;
	}
	return false;
}
function update_record($table_name, $data, $condition) {
	global $db;
	$raw = '';
	foreach ($data as $key => $value) if ($raw) { $raw = "$raw, $key = '$value'"; } else { $raw = "$key = '$value'"; }
	if ($raw) {
		$query = "UPDATE $table_name SET $raw WHERE $condition";
		if (isset($_GET['debug'])) echo $query.PHP_EOL;
		$result = $db -> query($query);
		if ($result) { return true; }
	}
	return false;
}
function delete_record($table_name, $condition = '') {
	global $db;
	if ($condition) { $where = 'WHERE'; } else { $where = ''; }
	$query = "DELETE FROM $table_name $where $condition";
	if (isset($_GET['debug'])) echo $query.PHP_EOL;
	$result = $db -> query($query);
	return ($result != false);
}
function fetch_record($table_name, $fields = '*', $condition = '', $order = '') {
	$data_row = fetch_data($table_name, $fields, false, $condition, $order, 0, 1);
	if ($data_row) return $data_row[0];
	return null;
}
function fetch_data($table_name, $fields = '*', $fetch_array = false, $condition = '', $order = '', $from = 0, $step = 100) {
	global $db;
	if ($fetch_array) $fetch_type = MYSQLI_NUM; else $fetch_type = MYSQLI_ASSOC;
	if (is_string($fields)) {
		$fields = str_replace(' ', '', $fields);
		$fields = explode(',', $fields);
	}
	$field = '';
	if ($condition) { $where = 'WHERE'; } else { $where = ''; }
	if ($order) { $order_by = 'ORDER BY'; } else { $order_by = ''; }
	foreach ($fields as $key => $value) {
		if ($field) { $field = $field.', '.$value; } else { $field = $value; }
	}
	if ($field) {
		$query = "SELECT $field FROM $table_name $where $condition $order_by $order LIMIT $from, $step";
		if (isset($_GET['debug'])) echo $query.PHP_EOL;
		$result = $db -> query($query);
		if ($result) {
			$return = []; $index = 0;
			if ($result -> num_rows > 0)
				while ($row = $result -> fetch_array($fetch_type)) {
					$return[$index] = $row;
					$index++;
				}
			return $return;
		}
	}
	return [];
}
function count_record($table_name, $field = '*', $condition = '') {
	global $db;
	if ($condition) { $where = 'WHERE'; } else { $where = ''; }
	$query = "SELECT COUNT($field) FROM $table_name $where $condition";
	if (isset($_GET['debug'])) echo $query.PHP_EOL;
	$result = $db -> query($query);
	if ($result) {
		$data = $result -> fetch_array(MYSQLI_NUM);
		return intval($data[0]);
	}
	return 0;
}
?>

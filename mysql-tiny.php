<?php
define('db_host', 'db_host');
define('db_user', 'db_user');
define('db_pass', 'db_pass');
define('db_name', 'db_name');
define('collation', 'UTF8');

$db = db();
if(isset($_GET['debug'])) var_dump($db);
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
		// return $query;
		$result = $db -> query($query);
		if ($result) { return true; }
	}
	return false;
}
function drop_table($table_name) {
	global $db;
	$query = 'DROP TABLE '.$table_name;
	$result = $db -> query($query);
	return $result;
}
function new_record($table, $values) {
	global $db;
	$field = '';
	$value = '';
	foreach ($values as $key => $_value) {
		if ($field) { $field = "$field, $key"; } else { $field = $key; }
		if ($value) { $value = "$value, '$_value'"; } else { $value = "'$_value'"; }
	}
	if ($field && $value) {
		$query = "INSERT INTO $table ($field) VALUES ($value)";
		// echo $query.PHP_EOL;
		$result = $db -> query($query);
		return $result;
	}
	return false;
}
function update_record($table, $values, $condition) {
	global $db;
	$append = '';
	foreach ($values as $key => $value) if ($append) { $append = "$append, $key = '$value'"; } else { $append = "$key = '$value'"; }
	if ($append) {
		$query = "UPDATE $table SET $append WHERE $condition";
		$result = $db -> query($query);
		if ($result) { return true; }
	}
	return false;
}
function delete_record($table, $condition = '') {
	global $db;
	if ($condition) { $where = 'WHERE'; } else { $where = ''; }
	$query = "DELETE FROM $table $where $condition";
	$result = $db -> query($query);
	return ($result != false);
}
function fetch_row($table, $fields, $condition = '') {
	global $db;
	$field = '';
	if ($condition) { $where = 'WHERE'; } else { $where = ''; }
	foreach ($fields as $key => $value) {
		if ($field) { $field = $field.', '.$value; } else { $field = $value; }
	}
	if ($field) {
		$query = "SELECT $field FROM $table $where $condition LIMIT 1";
		$result = $db -> query($query);
		if ($result) {
			if ($result -> num_rows > 0) return $result -> fetch_assoc();
		}
	}
	return null;
}
function fetch_data_assoc($table, $fields, $condition = '', $order = '', $from = 0, $step = 100) {
	global $db;
	//$from = ($page - 1) * $step;
	$field = '';
	if ($condition) { $where = 'WHERE'; } else { $where = ''; }
	if ($order) { $order_by = 'ORDER BY'; } else { $order_by = ''; }
	foreach ($fields as $key => $value) {
		if ($field) { $field = $field.', '.$value; } else { $field = $value; }
	}
	if ($field) {
		$query = "SELECT $field FROM $table $where $condition $order_by $order LIMIT $from, $step";
		// echo $query.PHP_EOL;
		$result = $db -> query($query);
		if ($result) {
			$return = [];
			$index = 0;
			if ($result -> num_rows > 0) while ($row = $result -> fetch_assoc()) {
				$return[$index] = $row;
				$index++;
			}
			return $return;
		}
	}
	return [];
}
function fetch_data_array($table, $fields, $condition = '', $order = '', $from = 0, $step = 100) {
	global $db;
	//$from = ($page - 1) * $step;
	$field = '';
	if ($condition) { $where = 'WHERE'; } else { $where = ''; }
	if ($order) { $order_by = 'ORDER BY'; } else { $order_by = ''; }
	foreach ($fields as $key => $value) {
		if ($field) { $field = "$field, $value"; } else { $field = $value; }
	}
	if ($field) {
		$query = "SELECT $field FROM $table $where $condition $order_by $order LIMIT $from, $step";
		//echo $query.PHP_EOL;
		$result = $db -> query($query);
		if ($result) {
			$return = [];
			$index = 0;
			if ($result -> num_rows > 0) while ($row = $result -> fetch_array(MYSQLI_NUM)) {
				$return[$index] = $row;
				$index++;
			}
			return $return;
		}
	}
	return [];
}
function count_row($table, $field = '*', $condition = '') {
	global $db;
	if ($condition) { $where = 'WHERE'; } else { $where = ''; }
	$query = "SELECT COUNT($field) FROM $table $where $condition";
	$result = $db -> query($query);
	if ($result) {
		$data = $result -> fetch_array(MYSQLI_NUM);
		return intval($data[0]);
	}
	return 0;
}
?>

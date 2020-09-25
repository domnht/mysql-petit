# mySQL Petit
**mySQL Petit** is a tiny `php` file that I've created to interaction with mySQL database. It's use for writting a query and handling the result for you. **mySQL Petit** is the good choice for small project that don't use any frameworks.

## Installation
There are two simple steps to start:
1. Download the source file and copy to your project directory.
2. Import this tiny library to your project by `include`, `include_once`, `require` or `require_once`.
```php
require_once('mysql-petit.php');
```

## Config
Before start, you should config the database information. Just open the source file and change value of `db_host`, `db_user`, `db_pass`, `db_name`. You can also set the collation by changing value of `collation` constant.

Here is a sample code:
```php
define('db_host', 'localhost');
define('db_user', 'root');
define('db_pass', 'root');
define('db_name', 'demo');
define('collation', 'UTF8'); // You should use UTF8 here
```

## Global variable
**mySQL Petit** use global variable `$db` for the database connection. That means you can use this to execute your own queries. Or, if you want to create a connection your own, just call `db()`, and it is what you want.

## Table processing
There are two methods that can help you create and delete table from your database.

### Create a table
You can create a table by calling `create_table`.
```php
create_table(String $table_name, Array $columns) : Bool
```
**Parameters**
- `$table_name`: name of the table.
- `$columns` is an array of `[$string column_name => string $options]`.
For example, you want to create a table which name is `user` and its structure is in the bellow table:

| Name | Type | Options |
| --- | --- | --- |
| user_id | int | not null, primary key |
| user_name | varchar(50) | |
| user_email | varchar(100) | |
| user_gender | tinyint | default 0 |
| user_password | varchar(100) | |
| date_modified | timestamp | default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP |

So `$columns` should create your table by this code:
```php
$table_name = 'user';
$columns = [
	'user_id' => 'INT PRIMARY KEY',
	'user_name' => 'VARCHAR(50)',
	'user_email' => 'VARCHAR(100)',
	'user_gender' => 'TINYINT DEFAULT 0',
	'user_password' => 'VARCHAR(100)',
	'date_modified' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
];
$result = create_table($table_name, $columns); // return true if success
```

### Delete a table
You may want to drop a table, so let's call `drop_table` for this.
```php
drop_table(String $table_name) : Bool
```
which `$table_name` is name of the table you want to delete.

## Record processing
### Insert record
To insert a record, use `new_record` method, the syntax is follows:
```php
new_record(String $table_name, Array $data) : Bool
```
**Parameters**
- `$table_name`: name of the table you want to insert data.
- `$data`: an array of `[$string field_name => string $value]`.
Here is sample code for inserting a row to above example table:
```php
$table_name = 'user';
$data = [
	'user_id' => time(),
	'user_name' => 'Foo',
	'user_email' => 'someone@domain.com',
	'user_gender' => '1',
	'user_password' => md5('password')
];
// I ignored date_modified column because I've set its default value is CURRENT_TIMESTAMP
$result = new_record($table_name, $data);
```

### Update record(s)
By the way calling `update_record` function, you can update one (or more) record.
```php
update_record(String $table_name, Array $data[, String $condition = '']) : Bool
```
**Parameters**
- `$table_name`: name of the table you want to insert data.
- `$data`: a data array that likely in `new_record` method.
- `$condition`: the condition statement, which is the same in mysql query.
For example, to update record from above table, whose `user_name` begin with `'F'` and `user_gender` equals `0`:
```php
$table_name = 'user';
$data = [
	'user_name' = 'Bar',
]; // The number of fields is depend on your need and your database config.
$condition = "user_name LIKE 'F%' AND user_gender = 0";
// You see, the syntax is the same with mySQL query syntax!
$result = update_record($table_name, $data, $condition);
```

### Delete record(s)
It also supports deleting a record or more. And the method name is `delete_record`.
```php
delete_record(String $table_name [, String $condition = '']) : Bool
```
The **parameters** is the same with two above methods. Please note that there is no `$data` variable because it is not neccessary while deleting.

## Fetch data
Do you want to fetch only one or some rows? Both options is supported.
### Fetch one record
To fetch a record, you can use `fetch_record` method. This will return an array if success, and `null` when there are any errors.

You may ask me whether you want to use `GROUP BY` in your query. You can append it to `$condition`. In this case, if you don't have any filter in `$condition`, you will get a syntax-error query. To solve this, you should use an alway-true statement like `1`, that is `1 GROUP BY some_fields`.
```php
// In case you had a string of fields name, or would like get all fields ('*')
fetch_record(String $table_name [, String $fields = '*' [, String $condition = '' [, String $order = '']]]) : Array?
// In case you got an array of fields, then use the syntax bellow
fetch_record(String $table_name [, Array  $fields = '*' [, String $condition = '' [, String $order = '']]]) : Array?
```
**Parameters**
- `$table_name`, `$fields`, `$condition` is the same with above methods.
- `$order`: order or arrange options in query.
Follow my example:
```php
$table_name = 'user';
$fields = 'user_id,user_name,user_email,user_gender';
// or $fields = ['user_id', 'user_name', 'user_email', 'user_gender'];
$condition = "user_email = 'someone@domain.com'";
$row = fetch_record($table_name, $fields, $condition);
```
### Fetch more than one record.
`fetch_data` may meet your need. Here is the syntax:
```php
fetch_data(String $table_name [, String $fields = '*' [, $fetch_array = false [, $condition = '' [, $order = '' [, $from = 0 [, $step = 100]]]]]]) : Array?;
// bellow syntax is also accepted
fetch_data(String $table_name [, Array  $fields = '*' [, $fetch_array = false [, $condition = '' [, $order = '' [, $from = 0 [, $step = 100]]]]]]) : Array?;
```
The **parameters** is used likely `fetch_record`.

### Count record
The last very important function is `count_record`. It is:
```php
count_record(String $table_name [, String $field = '*', String $condition = '']) : Int
```
**Parameters**
- `$field`: a field name to count. When establish mySQL query, it will be `SELECT COUNT($field) FROM ...`.

In short, it is my very-subjective-created library. So give me a message whether you got any suggestions.
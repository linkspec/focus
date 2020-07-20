<?php
/**
* Performs database set up
*/

require 'config.php';
$db = new mysqli($databaseHost, $databaseUsername, $databasePassword, $database);
// Get list of existing tables
$tables = $db->query("SELECT `table_name`
            FROM information_schema.tables
            WHERE table_schema = 'focus'");

/*  Create the tables if not already exist */
$db->query("CREATE TABLE IF NOT EXISTS " . dbPrefix . "tasks (id INT(10) AUTO_INCREMENT PRIMARY KEY)");
echo 'Created table tasks<br>';
$db->query("CREATE TABLE IF NOT EXISTS " . dbPrefix . "users (id INT(10) AUTO_INCREMENT PRIMARY KEY)");
echo 'Created table users<br>';
$db->query("CREATE TABLE IF NOT EXISTS " . dbPrefix . "blocker_definitions (id INT(10) AUTO_INCREMENT PRIMARY KEY)");
echo 'Created table blocker_definitions<br>';
$db->query("CREATE TABLE IF NOT EXISTS " . dbPrefix . "task_blocker_map (id INT(10) AUTO_INCREMENT PRIMARY KEY)");
echo 'Created table task_blocker_map<br>';
$db->query("CREATE TABLE IF NOT EXISTS " . dbPrefix . "task_task_map (id INT(10) AUTO_INCREMENT PRIMARY KEY)");
echo 'Created table task_blocker_map<br>';


/* Add the columns to tasks */
modifyColumn('tasks', 'name', 'text', 'NULL', '', '', '');
modifyColumn('tasks', 'description', 'text', 'NULL', '', '', '');
modifyColumn('tasks', 'notes', 'text', 'NULL', '', '', '');
modifyColumn('tasks', 'createdate', 'INT(11)', 'NULL', '', '', '');
modifyColumn('tasks', 'lastupdatedate', 'INT(11)', 'NULL', '', '', '');
modifyColumn('tasks', 'deadline', 'INT(11)', 'NULL', '', '', '');
modifyColumn('tasks', 'leadtime', 'INT(11)', 'NULL', '', '', '');
modifyColumn('tasks', 'status', 'int', 'NOT NULL', '', '', '');
modifyColumn('tasks', 'priority', 'int', 'NOT NULL', '', '', '');
modifyColumn('tasks', 'owner', 'varchar(30)', 'NOT NULL', '', '', '');
modifyColumn('tasks', 'timerequired', 'INT(12)', 'NULL', '', '', '');


/* Add the columns to users */
modifyColumn('users', 'vendor', 'text', 'NOT NULL', '', '', '');
modifyColumn('users', 'vendorid', 'varchar(30)', 'NOT NULL', '', '', '');


/* Add the columns to blocker_definitions */
modifyColumn('blocker_definitions', 'ownerid', 'INT(10)', 'NOT NULL', '', '', '');
modifyColumn('blocker_definitions', 'name', 'text', 'NOT NULL', '', '', '');

/* Add the columns to task_blocker_map */
modifyColumn('task_blocker_map', 'taskid', 'INT(10)', 'NOT NULL', '', '', '');
modifyColumn('task_blocker_map', 'blocker_definition_id', 'INT(10)', 'NOT NULL', '', '', '');

/* Add the columns to blocker_definitions */
modifyColumn('task_task_map', 'ownerid', 'INT(10)', 'NOT NULL', '', '', '');
modifyColumn('task_task_map', 'owningtask', 'INT(10)', 'NOT NULL', '', '', '');
modifyColumn('task_task_map', 'requiredtask', 'INT(10)', 'NOT NULL', '', '', '');






/*
* This function will either create the column, or modify it, to match the given parameters
*
*/
function modifyColumn($table, $columnName, $type, $null, $key, $default, $extra)
{
  /* Check if the column already exists */
  global $db;
  $tableExists = false;
  $columns =  $db->query("SHOW COLUMNS FROM " . $table);
  foreach($columns as $column)
  {
    if($column['Field'] == $columnName){ $tableExists = true; }
  }

  if($tableExists)
  {
    // The column already exists, modify it
    $db->query("ALTER TABLE " . $table . " MODIFY COLUMN " . $columnName . " " . $type . " " . $null . " " . $key . " " . $default . " " . $extra);
    echo 'Modified column ' . $columnName . '<br>';
  }
  else {
    // New column, create it
    $db->query("ALTER TABLE " . $table . " ADD COLUMN " . $columnName . " " . $type . " " . $null . " " . $key . " " . $default . " " . $extra);
    echo 'Created column ' . $columnName . '<br>';
  }





}

?>

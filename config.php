<?php

session_start();

// The base URL of your application
$baseurl = 'https://dev-focus.futcher.org.uk';
$callbackUri = $baseurl . "/callback.php";

// Databse connection variables
$databaseHost = 'localhost';
$database = 'focus';
$databaseUsername = 'focus';
$databasePassword = 'RplgiWY9TciaYqH1Ee35';
define('dbPrefix', '');

// Google auth variables
$client_id = '429157958080-au1mf0vl522djnk1h4n9s80ts993j9ed.apps.googleusercontent.com';
$client_secret = 'nD82VAOZAg91fSD98O7S6tqf';

/**
* Creates and returns a new mysqli object
*
* $return object Returns a mysqli object
*/
function newMysqliObject()
{
  global $databaseHost;
  global $databaseUsername;
  global $databasePassword;
  global $database;

  $db = new mysqli($databaseHost, $databaseUsername, $databasePassword, $database);

  return $db;
}


require 'classes/task.php';
require 'classes/user.php';
require 'classes/blocker.php';
?>

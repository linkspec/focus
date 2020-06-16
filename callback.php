<?php

require 'config.php';

// Handles callbacks from external applications

// Extract the callback vars
$code = $_GET['code'];
$scope = $_GET['scope'];
$authuser = $_GET['authuser'];
$hd = $_GET['hd'];
//$consent = $_GET['consent'];

// Access the current users user object
$user = new user();

// Save the code
$user->saveGoogleAuthCode($code);

// Exchange the code for an access token
$user->requestAccessToken();

// Fetch the end users email
$user->fetchUserGoogleInfo();

// Check if the user is already in the database and add if not
if(!$user->checkIfUserInDatabase()){
    $user->addToDatabase();
}


echo '<meta http-equiv="refresh" content="0; URL=\'' . $baseurl . '\'" />'

?>
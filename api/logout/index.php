<?php

/**
 * Handles logout requests. This simply destroys the current session
 */

session_start();

// Destory the cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// And the session on the server
session_destroy();

?>
<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$timeout = 1800;

if (isset($_SESSION['LAST_ACTIVITY'])) {

    if ((time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {

        session_unset();

        session_destroy();

        header("Location: ../auth/login.php?timeout=1");

        exit();

    }

}

$_SESSION['LAST_ACTIVITY'] = time();
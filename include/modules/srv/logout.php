<?php
session_start();
include 'DBMovies.php';

if (isset($_SESSION['username'])) {
    DBMovies::logger('[OUT]');
    unset($_SESSION['username']);
}
?>

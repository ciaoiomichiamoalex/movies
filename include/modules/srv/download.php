<?php
session_start();
include 'DBMovies.php';

if (isset($_SESSION['username'])) {
    $res = DBMovies::download($_SESSION['username']);
    echo $res;
}
?>

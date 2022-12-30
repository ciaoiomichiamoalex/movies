<?php
session_start();
include 'DBMovies.php';

if (isset($_POST['username'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $res = DBMovies::login($username, $password);

    if ($res == '1') {
        $_SESSION['username'] = $_POST['username'];
        DBMovies::logger('[IN]');
    }
    echo $res;
}
?>

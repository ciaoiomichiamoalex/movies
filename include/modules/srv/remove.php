<?php
session_start();
include 'DBMovies.php';

if (isset($_SESSION['username'])) {
    $name = $_POST['name'];
    $review = $_POST['review'];
    $res = DBMovies::remove($name, $review);
}
?>

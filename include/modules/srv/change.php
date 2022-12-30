<?php
session_start();
include 'DBMovies.php';

if (isset($_SESSION['username'])) {
    $name = $_POST['name'];
    $review = $_POST['review'];
    $named = $_POST['named'];
    $reviewed = $_POST['reviewed'];

    $res = DBMovies::change($name, $review, $named, $reviewed);
    echo $res;
}
?>

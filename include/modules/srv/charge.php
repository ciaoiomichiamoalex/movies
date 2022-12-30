<?php
session_start();
include 'DBMovies.php';

if (isset($_SESSION['username']) && isset($_FILES['file']['name'])) {
    $filename = $_FILES['file']['name'];
    $location = $_SERVER['DOCUMENT_ROOT'].'/resources/loads/'.$filename;

    $extension = pathinfo($location, PATHINFO_EXTENSION);
    $extension = strtolower($extension);

    $res = '0';
    if ($extension == 'csv') {
        if (move_uploaded_file($_FILES['file']['tmp_name'], $location));
        $res = DBMovies::charge($location, $filename);
    }
    echo $res;
}
?>

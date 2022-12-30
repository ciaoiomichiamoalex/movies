<?php
session_start();
include 'DBMovies.php';

if (isset($_SESSION['username'])) {
    switch ($_POST['value']) {
        case 'Name':
            $value = 'nome';
            break;

        case 'Review':
            $value = 'recensione';
            break;

        default:
            $value = 'ordine';
            break;
    }

    switch ($_POST['type']) {
        case 'Ascending':
            $type = 'ASC';
            break;

        default:
            $type = 'DESC';
            break;
    }

    if ($value == 'recensione') $order = $value.' '.$type.', nome';
    else $order = $value.' '.$type;

    $search = $_POST['search'];
    $rows = DBMovies::show($order, $search);
    $res = '';

    foreach ($rows as $row) {
        $res = $res.'<div class="col-12 element">'.PHP_EOL.
            '<div class="card m-3 p-3 shadow-sm">'.PHP_EOL.
            '<div class="row g-0">'.PHP_EOL.
            '<div class="col-10 ps-3 pe-2 text-truncate user-select-none element-name">'.$row['nome'].'</div>'.PHP_EOL.
            '<div class="col-2 text-center user-select-none element-review">'.$row['recensione'].'</div>'.PHP_EOL.
            '</div>'.PHP_EOL.'</div>'.PHP_EOL.'</div>';
    }
    echo $res;
}
?>

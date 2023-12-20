<?php

include_once "InteractionsMatrix.php";
$interactionsMatrix = array(
    array(5, 4, 1, 4, 0),
    array(3, 1, 2, 3, 3),
    array(4, 3, 4, 3, 5),
    array(3, 3, 1, 5, 4)
);

$interactionsMatrix2 = array(
    array(4, 0, 0, 5, 1, 0, 0),
    array(5, 5, 4, 0, 0, 0, 0),
    array(0, 0, 0, 2, 4, 5, 0),
    array(0, 3, 0, 0, 0, 0, 3)
);

$interactionsMatrix3 = array(
    array(9, 6, 8, 4, 0),
    array(2, 10, 6, 0, 8),
    array(5, 9, 0, 10, 7),
    array(0, 10, 7, 8, 0),
);

$selectedUserIndex = 0;
$selectedItemIndex = 4;

$matrix = new InteractionsMatrix($interactionsMatrix);
$prediction = $matrix->countPrediction($selectedUserIndex, $selectedItemIndex);

//$biggie = $matrix->populateMatrix();
//
//echo "<br><br><br>";
//foreach ($biggie as $row) {
//    print_r($row);
//    echo "<br><br>";
//}
//
//echo $biggie[1][1];
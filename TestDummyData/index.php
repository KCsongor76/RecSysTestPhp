<?php
$interactionsMatrix = array(
    array(5, 4, 1, 4, 0),
    array(3, 1, 2, 3, 3),
    array(4, 3, 4, 3, 5),
    array(3, 3, 1, 5, 4)
);

$interactionsMatrix2 = [
    [5, 3, 0, 0, 2],
    [4, 0, 0, 0, 0],
    [1, 1, 0, 0, 0],
    [0, 1, 5, 4, 4],
    [0, 0, 0, 1, 0],
];

$interactionsMatrix3 = array(
    array(4, 0, 0, 5, 1, 0, 0),
    array(5, 5, 4, 0, 0, 0, 0),
    array(0, 0, 0, 2, 4, 5, 0),
    array(0, 3, 0, 0, 0, 0, 3)
);

function averageRatings($matrix, $selectedItem): array
{
    $averageRatingsArray = [];

    for ($i = 0; $i < count($matrix); $i++) {
        $sum = 0;
        for ($j = 0; $j < count($matrix[$i]); $j++) {
            if ($j != $selectedItem) {
                $sum += $matrix[$i][$j];
            }
        }
        $averageRatingsArray[] = $sum / 4;
    }
    return $averageRatingsArray;
}

function removeBias($matrix, $avgRatings): array
{
    $biasRemovedMatrix = $matrix;

    for ($i = 0; $i < count($matrix); $i++) {
        for ($j = 0; $j < count($matrix[$i]) - 1; $j++) {
            $biasRemovedMatrix[$i][$j] -= $avgRatings[$i];
        }
    }

    return $biasRemovedMatrix;
}

function countSimilarities($biasRemovedMatrix, $selectedItemIndex, $selectedUserIndex): array
{
    $similarityArray = [];
    for ($i = 1; $i < count($biasRemovedMatrix); $i++) {
        $numerator = 0;
        $denominatorLeft = 0;
        $denominatorRight = 0;
        for ($j = 0; $j < count($biasRemovedMatrix[$i]); $j++) {
            if ($j != $selectedItemIndex) {
                $numerator += $biasRemovedMatrix[$selectedUserIndex][$j] * $biasRemovedMatrix[$i][$j];
                $denominatorLeft += pow($biasRemovedMatrix[$selectedUserIndex][$j], 2);
                $denominatorRight += pow($biasRemovedMatrix[$i][$j], 2);
            }
        }
        $denominator = sqrt($denominatorLeft) * sqrt($denominatorRight);
        # TODO: exception: div/0
        $similarity = $numerator / $denominator;
        $similarityArray[] = $similarity;
    }
    return $similarityArray;
}

function countPrediction($matrix, $selectedItemIndex, $selectedUserIndex)
{
    $numerator = 0;
    $denominator = 0;

    $averageRatingsArray = averageRatings($matrix, $selectedItemIndex);
    $biasRemovedMatrix = removeBias($matrix, $averageRatingsArray);
    $similarityArray = countSimilarities($biasRemovedMatrix, $selectedItemIndex, $selectedUserIndex);

    for ($i = 0; $i < count($similarityArray); $i++) {
        $numerator += $similarityArray[$i] * ($matrix[$i + 1][4] - $averageRatingsArray[$i + 1]);
        echo "<br>";
        echo "$i: $similarityArray[$i] * (" . $matrix[$i + 1][4] . " - " . $averageRatingsArray[$i + 1] . ")";
        $denominator += abs($similarityArray[$i]);
    }
    echo "<br>";
    echo "<br>";

    $prediction = $averageRatingsArray[0] + $numerator / $denominator;
    echo "$numerator<br>";
    echo "$denominator<br>";
    return $prediction;
}

$selectedUserIndex = 0;
$selectedItemIndex = 4;

# TODO: now we know, that the first line's last rating is unknown, but need to generalize...


$averageRatingsArray = averageRatings($interactionsMatrix, $selectedItemIndex);
print_r($averageRatingsArray);
echo "<br>";

$biasRemovedMatrix = removeBias($interactionsMatrix, $averageRatingsArray);
print_r($biasRemovedMatrix);
echo "<br>";

$similarityArray = countSimilarities($biasRemovedMatrix, $selectedItemIndex, $selectedUserIndex);
print_r($similarityArray);
echo "<br>";

$prediction = countPrediction($interactionsMatrix, $selectedItemIndex, $selectedUserIndex);

echo "Prediction: $prediction";
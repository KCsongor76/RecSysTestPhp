<?php
include_once 'Rating.php';

class InteractionsMatrix extends AbstractInteractionsMatrix
{
    private array $matrix;


    /**
     * @param array $matrix
     */
    public function __construct(array $matrix)
    {
        $this->matrix = $matrix;
    }

    public function populateMatrix(): array
    {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "movielens_db_2";
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        // Assuming you have a connection to your database ($dbConnection)

        // Fetch all ratings from the database
        $query = "SELECT userId, movieId, rating FROM ratings";
        $result = mysqli_query($conn, $query);

        $interactionsMatrix = []; // Initialize the interactions matrix

        // Process the fetched ratings and construct the interactions matrix
        while ($row = mysqli_fetch_assoc($result)) {
            $userId = $row['userId'];
            $movieId = $row['movieId'];
            $rating = $row['rating'];

//            $rating = new Rating($userId, $movieId, $r);

            // If the user is not yet in the interactions matrix, create an entry for the user
            if (!isset($interactionsMatrix[$userId])) {
                $interactionsMatrix[$userId] = [];
            }

            // Set the rating for the user-movie pair in the interactions matrix
            $interactionsMatrix[$userId][$movieId] = $rating;
        }
        echo "<br><br>";

        print_r($interactionsMatrix);
        return $interactionsMatrix;
    }


    /**
     * Loops through every item of the matrix, counts every
     * row's average (ignores the 0 value cells), put's this average
     * into an array, and returns it.
     *
     * e.g.: $interactionsMatrix = <br>
     * <br> [4, 0, 0, 5, 1, 0, 0] => 10/3 = 3.33
     * <br> [5, 5, 4, 0, 0, 0, 0] => 14/3 = 4.66
     * <br> [0, 0, 0, 2, 4, 5, 0] => 11/3 = 3.66
     * <br> [0, 3, 0, 0, 0, 0, 3] => 6/2 = 3
     * <br><br>
     * => returned array = [3.33, 4.66, 3.66, 3]
     * @return array
     */
    private function averageRatings(): array
    {
        $averageRatingsArray = [];
        foreach ($this->matrix as $userRatingRow) {
            $sum = 0;
            $n = 0;
            foreach ($userRatingRow as $userRating) {
                if ($userRating != 0) {
                    $sum += $userRating;
                    $n++;
                }
            }
            if ($n == 0) {
                #TODO: undefined/null ?
                $averageRatingsArray[] = 0;
            } else {
                $averageRatingsArray[] = $sum / $n;
            }
        }
        return $averageRatingsArray;
    }

    /**
     * Takes the average ratings 1D array as its parameter.
     * From the interactions matrix's every row, it removes the corresponding
     * average (ignores the 0 valued cells), and returns the bias removed matrix. <br>
     *
     * e.g.: $avgRatings = [3.33, 4.66, 3.66, 3] <br><br>
     * $interactionsMatrix =
     * <br> [4, 0, 0, 5, 1, 0, 0] (-3.33)
     * <br> [5, 5, 4, 0, 0, 0, 0] (-4.66)
     * <br> [0, 0, 0, 2, 4, 5, 0] (-3.66)
     * <br> [0, 3, 0, 0, 0, 0, 3] (-3) <br><br>
     * returns $biasRemovedMatrix =
     * <br> [0.66, 0, 0, 1.66, -2.33, 0, 0]
     * <br> [0.33, 0.33, -0.66, 0, 0, 0, 0]
     * <br> [0, 0, 0, -1.66, 0.33, 1.33, 0]
     * <br> [0, 0, 0, 0, 0, 0, 0]
     * @param $avgRatings
     * @return array
     */
    private function removeBias($avgRatings): array
    {
        $biasRemovedMatrix = $this->matrix;

        foreach ($biasRemovedMatrix as $i => $userRatingRow) {
            foreach ($userRatingRow as $j => $userRating) {
                if ($userRating != 0) {
                    $biasRemovedMatrix[$i][$j] -= $avgRatings[$i];
//                    $number = $userRating->getRating() - $avgRatings[$i - 1];
//                    $userRating->setRating($number);
                }
            }
        }

        return $biasRemovedMatrix;
    }

    /**
     * Counts every similarity for a selected user (row), using the
     * (sample) Pearson correlation coefficient (= Centered cosine similarity).
     * <br> https://en.wikipedia.org/wiki/Pearson_correlation_coefficient
     * @param $biasRemovedMatrix
     * @param $selectedUserIndex
     * @return array
     */
    private function countSimilarities($biasRemovedMatrix, $selectedUserIndex): array
    {
        $similarityArray = [];
        foreach ($biasRemovedMatrix as $rowIndex => $userRatingRow) {
            if ($rowIndex != $selectedUserIndex) {
                // no need to check with itself.
                $numerator = 0;
                $denominatorLeft = 0;
                $denominatorRight = 0;
                foreach ($userRatingRow as $columnIndex => $userRating) {
                    $selectedUserRating = $biasRemovedMatrix[$selectedUserIndex][$columnIndex];
                    $numerator += $selectedUserRating * $userRating;
                    $denominatorLeft += pow($selectedUserRating, 2);
                    $denominatorRight += pow($userRating, 2);
                }
                $denominator = sqrt($denominatorLeft) * sqrt($denominatorRight);
                if ($denominator == 0) {
                    # TODO: null/undefined ?
                    $similarityArray[] = 0;
                } else {
                    $similarity = $numerator / $denominator;
                    $similarityArray[] = $similarity;
                }
            }
        }
        return $similarityArray;
    }

    /**
     * Counts the prediction for a selected user's selected (not rated) item.
     * @param $selectedUserIndex
     * @param $selectedItemIndex
     * @return float
     */
    public function countPrediction($selectedUserIndex, $selectedItemIndex): float
    {
        $numerator = 0;
        $denominator = 0;

        $averageRatingsArray = $this->averageRatings();
//        echo "Average Ratings Array:<br>";
//        print_r($averageRatingsArray);

        $biasRemovedMatrix = $this->removeBias($averageRatingsArray);
//        echo "<br><br>Bias Removed Matrix:<br>";
//        print_r($biasRemovedMatrix);

        $similarityArray = $this->countSimilarities($biasRemovedMatrix, $selectedUserIndex);
        echo "<br><br>Similarity Array:<br>";
        print_r($similarityArray);

        $columnArray = [];

        for ($i = 0; $i < count($biasRemovedMatrix); $i++) {
            if ($i !== $selectedUserIndex) {
                $columnArray[] = $biasRemovedMatrix[$i][$selectedItemIndex];
            }
        }


//        foreach ($similarityArray as $i => $similarity) {
//            $numerator += $similarity * ($this->matrix[$i + 1][$selectedItemIndex] - $averageRatingsArray[$i + 1]);
//            $denominator += $similarity;
//        }

        array_multisort($similarityArray, SORT_DESC, $columnArray);
        $MAXIMUM_SIMILAR_USERS = 20;

        for ($i = 0; $i < min($MAXIMUM_SIMILAR_USERS, count($similarityArray)); $i++) {
            $numerator += $similarityArray[$i] * $columnArray[$i];
            $denominator += $similarityArray[$i];
        }

        $prediction = $averageRatingsArray[$selectedUserIndex] + $numerator / $denominator;
        echo "<br><br>Numerator= $numerator<br>";
        echo "<br>Denominator= $denominator<br>";
        echo "<br>Prediction= $prediction";
        return $prediction;
    }


}
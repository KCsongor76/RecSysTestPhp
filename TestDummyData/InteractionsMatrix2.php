<?php
include_once 'Rating.php';
include_once 'Similarity.php';

class InteractionsMatrix2 extends AbstractInteractionsMatrix
{
    private array $matrix;

    public function __construct()
    {
        $this->matrix = $this->populateMatrix2();
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
            $r = $row['rating'];


            // If the user is not yet in the interactions matrix, create an entry for the user
            if (!isset($interactionsMatrix[$userId])) {
                $interactionsMatrix[$userId] = [];
            }

            $rating = new Rating($userId, $movieId, $r);
            // Set the rating for the user-movie pair in the interactions matrix
            $interactionsMatrix[$userId][$movieId] = $rating;
        }
        echo "<br><br>";

//        print_r($interactionsMatrix);
        return $interactionsMatrix;
    }


    public function populateMatrix2(): array
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
        $interactionsMatrix = [];
        $query = "SELECT userId FROM users";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $userId = $row["userId"];
            $interactionsMatrix[$userId] = [];
        }

        $query = "SELECT movieId FROM movies";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $movieId = $row["movieId"];
            for ($i = 1; $i < count($interactionsMatrix); $i++) {
                $interactionsMatrix[$i][$movieId] = 0;
            }
        }

        $query = "SELECT userId, movieId, rating FROM ratings";
        $result = mysqli_query($conn, $query);

        // Process the fetched ratings and construct the interactions matrix
        while ($row = mysqli_fetch_assoc($result)) {
            $userId = $row['userId'];
            $movieId = $row['movieId'];
            $r = $row['rating'];


            // If the user is not yet in the interactions matrix, create an entry for the user
//            if (!isset($interactionsMatrix[$userId])) {
//                $interactionsMatrix[$userId] = [];
//            }

            $rating = new Rating($userId, $movieId, $r);
            // Set the rating for the user-movie pair in the interactions matrix
            $interactionsMatrix[$userId][$movieId] = $rating;
        }
        echo "<br><br>";

//        print_r($interactionsMatrix[1]);
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
    protected function averageRatings(): array
    {
        $averageRatingsArray = [];
        foreach ($this->matrix as $userRatingRow) {
            $sum = 0;
            $n = 0;
            foreach ($userRatingRow as $userRating) {
                if ($userRating instanceof Rating) {
                    $sum += $userRating->getRating();
                    $n++;
                }
//                if ($userRating->getRating() != 0) {
//                    $sum += $userRating->getRating();
//                    $n++;
//                }
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
    protected function removeBias($avgRatings): array
    {
        $biasRemovedMatrix = $this->matrix;

        foreach ($biasRemovedMatrix as $i => $userRatingRow) {
            foreach ($userRatingRow as $userRating) {
//                if ($userRating->getRating() !== 0) {
//                    $number = $userRating->getRating() - $avgRatings[$i - 1];
//                    $userRating->setRating($number);
//                }
                if ($userRating instanceof Rating) {
                    $number = $userRating->getRating() - $avgRatings[$i - 1];
                    $userRating->setRating($number);
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
    protected function countSimilarities($biasRemovedMatrix, $selectedUserIndex): array
    {
        $movies = [];

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
        $query = "SELECT movieId FROM movies";
        $result = mysqli_query($conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $movieId = $row['movieId'];
            $movies[] = $movieId;
        }

        $similarityArray = [];
        foreach ($biasRemovedMatrix as $i => $userRatingRow) {
            if ($i !== $selectedUserIndex) {
//                echo $i . "<br>";
                // no need to check with itself.
                $numerator = 0;
                $denominatorLeft = 0;
                $denominatorRight = 0;
                for ($j = 0; $j < count($movies); $j++) {
//                    if (!is_null($biasRemovedMatrix[$selectedUserIndex][$movies[$j]]) && !is_null($biasRemovedMatrix[$i][$movies[$j]])) {
//                        $selectedUserRating = $biasRemovedMatrix[$selectedUserIndex][$movies[$j]];
//                        $numerator = $selectedUserRating->getRating() * $biasRemovedMatrix[$i][$movies[$j]]->getRating();
//                        $denominatorLeft += pow($selectedUserRating->getRating(), 2);
//                        $denominatorRight += pow($biasRemovedMatrix[$i][$movies[$j]]->getRating(), 2);
//                    }
                    if ($biasRemovedMatrix[$selectedUserIndex][$movies[$j]] instanceof Rating && $biasRemovedMatrix[$i][$movies[$j]] instanceof Rating) {
                        $selectedUserRating = $biasRemovedMatrix[$selectedUserIndex][$movies[$j]];
                        $numerator = $selectedUserRating->getRating() * $biasRemovedMatrix[$i][$movies[$j]]->getRating();
                        $denominatorLeft += pow($selectedUserRating->getRating(), 2);
                        $denominatorRight += pow($biasRemovedMatrix[$i][$movies[$j]]->getRating(), 2);
                    }
                }
//                foreach ($userRatingRow as $columnIndex => $userRating) {
//                    if (!is_null($biasRemovedMatrix[$selectedUserIndex][$columnIndex])) {
//                        $selectedUserRating = $biasRemovedMatrix[$selectedUserIndex][$columnIndex]; // TODO:
//                        $numerator += $selectedUserRating->getRating() * $userRating->getRating();
//                        $denominatorLeft += pow($selectedUserRating->getRating(), 2);
//                        $denominatorRight += pow($userRating->getRating(), 2);
//                    }
//                }
                $denominator = sqrt($denominatorLeft) * sqrt($denominatorRight);
                if ($denominator == 0) {
                    # TODO: null/undefined ?
                    $similarityArray[] = 0;
                } else {
                    $similarity = $numerator / $denominator;
                    $similarityArray[] = new Similarity($selectedUserIndex, $i, $similarity);
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
//        echo "<br><br>Similarity Array:<br>";
//        print_r($similarityArray);

        $columnArray = [];

        for ($i = 1; $i < count($biasRemovedMatrix); $i++) {
            if ($i !== $selectedUserIndex && $biasRemovedMatrix[$i][$selectedItemIndex] instanceof Rating) {
                $columnArray[] = $biasRemovedMatrix[$i][$selectedItemIndex]->getRating();
            }
        }


//        foreach ($similarityArray as $i => $similarity) {
//            $numerator += $similarity * ($this->matrix[$i + 1][$selectedItemIndex] - $averageRatingsArray[$i + 1]);
//            $denominator += $similarity;
//        }

        // Sort array of objects based on the similarity attribute
        usort($similarityArray, function ($a, $b) {
            return $b->similarity <=> $a->similarity;
        });

        // Create an array of similarities from objects
        $arrayOfSimilarities = array_column($similarityArray, 'similarity');

        // Sort array2 based on the sorting order of the similarities
        array_multisort($arrayOfSimilarities, SORT_DESC, $columnArray);

        // Display sorted arrays
//        print_r($arrayOfSimilarities);
//        print_r($columnArray);

        // array_multisort($similarityArray, SORT_DESC, $columnArray);
        $MAXIMUM_SIMILAR_USERS = 20;

        for ($i = 0; $i < min($MAXIMUM_SIMILAR_USERS, count($arrayOfSimilarities)); $i++) {
            $numerator += $arrayOfSimilarities[$i] * $columnArray[$i];
            $denominator += $arrayOfSimilarities[$i];
        }

        $prediction = $averageRatingsArray[$selectedUserIndex] + $numerator / $denominator;
        echo "<br><br>Numerator= $numerator<br>";
        echo "<br>Denominator= $denominator<br>";
        echo "<br>Prediction= $prediction";
        return $prediction;
    }


}
<?php

abstract class AbstractRecommendationSystem
{
    /**
     * Makes a connection to the database.
     * @return mysqli|void
     */
    protected function connectToDb()
    {
        // Database credentials
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "movielens_db_2";
        // Create a new connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }

    abstract function __construct(int $selectedUserIndex);

    /**
     * Fills up the $notRatedMovieIndexes with every movie ID (int), which the user didn't rate.
     * @return void
     */
    abstract public function getNotRatedMovieIndexes(): void;

    /**
     * Creates the interactions matrix with other users' ratings, who have rated the selected movie.
     * @param int $selectedMovieIndex
     * @return void
     */
    abstract public function getInteractionsMatrix(int $selectedMovieIndex): void;

    /**
     * Counts the averageArray, the averages of users' ratings.
     * @param int $selectedMovieIndex
     * @return void
     */
    abstract public function getAverageArray(int $selectedMovieIndex): void;

    /**
     * Creates the bias removed matrix by subtracting the corresponding averages
     * from the interactions matrix's elements.
     * @param int $selectedMovieIndex
     * @return void
     */
    abstract public function removeBias(int $selectedMovieIndex): void;

    /**
     * Counts the similarities between the users using
     * the Pearson correlation coefficient.
     * @param int $selectedUserIndex
     * @param int $selectedMovieIndex
     * @return void
     */
    abstract public function countSimilarities(int $selectedUserIndex, int $selectedMovieIndex): void;

    /**
     * Sorts the similarities in descending order.
     * @param int $selectedUserIndex
     * @param int $selectedMovieIndex
     * @return void
     */
    abstract public function sortSimilarities(int $selectedUserIndex, int $selectedMovieIndex): void;

    /**
     * Counts the prediction for the selected movie.
     * @param int $selectedUserIndex
     * @param int $selectedMovieIndex
     * @return void
     */
    abstract public function countPrediction(int $selectedUserIndex, int $selectedMovieIndex): void;

    /**
     * Counts predictions for all not rated movies by the user.
     * @return void
     */
    abstract public function showRecommendations(): void;

    /*
     queue:
        have the selectedUserIndex
        have the selectedMovieIndex
        create interactionsMatrix
        create averageArray
        create biasRemovedInteractionsMatrix
        create similarityArray
        sort similarityArray
        establish the top "n" similar users
        count prediction, add to predictionsArray

        until last movie
     */
}
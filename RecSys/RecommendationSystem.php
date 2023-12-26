<?php

include_once 'AbstractRecommendationSystem.php';
include_once 'Similarity.php';
include_once 'Prediction.php';

class RecommendationSystem extends AbstractRecommendationSystem
{

    protected int $maxSimilarUsers = 3;
    private int $selectedUserIndex;
    private array $notRatedMovieIndexes;
    private array $interactionsMatrix;
    private array $averageArray;
    private array $biasRemovedMatrix;
    private array $similarityArray;
    private array $predictionsArray;

    public function __construct(int $selectedUserIndex)
    {
        $this->selectedUserIndex = $selectedUserIndex;
        $this->notRatedMovieIndexes = [];
        $this->interactionsMatrix = [];
        $this->averageArray = [];
        $this->biasRemovedMatrix = [];
        $this->similarityArray = [];
        $this->predictionsArray = [];
    }

    public function getNotRatedMovieIndexes2(): void
    {
        $conn = $this->connectToDb();
        $sql = "SELECT DISTINCT movieId
            FROM movies
            WHERE movieId NOT IN
            (
                SELECT movieId
                FROM ratings
                WHERE userId = ?
            );";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $this->selectedUserIndex);
        $stmt->execute();
        $result = $stmt->get_result();
        $notRatedMovieIds = $result->fetch_all(MYSQLI_ASSOC);
        $this->notRatedMovieIndexes = array_column($notRatedMovieIds, 'movieId');
        $conn->close();
    }


    public function getInteractionsMatrix2(int $selectedMovieIndex): void
    {
        $conn = $this->connectToDb();

        // Fetch all interactions for the selected user
        $sql = "SELECT movieId, rating FROM ratings WHERE userId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $this->selectedUserIndex);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $rating = $row["rating"];
            $movieId = $row["movieId"];
            $this->interactionsMatrix[$this->selectedUserIndex][$movieId] = $rating;
        }

        // Fetch all interactions for users who rated the selected movie
        $sql = "SELECT userId, movieId, rating FROM ratings WHERE movieId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $selectedMovieIndex);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $userId = $row["userId"];
            $rating = $row["rating"];
            $movieId = $row["movieId"];
            $this->interactionsMatrix[$userId][$movieId] = $rating;
        }

        $conn->close();
    }


    public function getInteractionsMatrix(int $selectedMovieIndex): void
    {
        $conn = $this->connectToDb();
        $sql = "SELECT movieId, rating FROM ratings WHERE userId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $this->selectedUserIndex);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $rating = $row["rating"];
            $movieId = $row["movieId"];
            $this->interactionsMatrix[$this->selectedUserIndex][$movieId] = $rating;
        }
        $conn->close();

        $conn = $this->connectToDb();
        $sql = "SELECT userId FROM ratings WHERE movieId = ?";
        // getting every user who rated the selected movie

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $selectedMovieIndex);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($user = $result->fetch_assoc()) {
            $userId = $user["userId"];

            $sql = "SELECT movieId, rating FROM ratings WHERE userId = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result2 = $stmt->get_result();
            while ($row = $result2->fetch_assoc()) {
                $rating = $row["rating"];
                $movieId = $row["movieId"];
                $this->interactionsMatrix[$userId][$movieId] = $rating;
            }
        }
        $conn->close();
    }

    public function getAverageArray($selectedMovieIndex): void
    {
        foreach ($this->interactionsMatrix as $userId => $userIdArray) {
            $this->averageArray[$userId] = array_sum($userIdArray) / count($userIdArray);
        }
    }

    public function removeBias($selectedMovieIndex): void
    {
        foreach ($this->interactionsMatrix as $userId => $userIdArray) {
            foreach ($userIdArray as $movieId => $rating) {
                $this->biasRemovedMatrix[$userId][$movieId] = $rating - $this->averageArray[$userId];
            }
        }
    }

    public function countSimilarities($selectedUserIndex, $selectedMovieIndex): void
    {
        foreach ($this->biasRemovedMatrix as $userId => $ratings) {
            $numerator = 0;
            $denominatorLeft = 0;
            $denominatorRight = 0;

            foreach ($ratings as $movieId => $rating) {
                $numerator += isset($this->biasRemovedMatrix[$selectedUserIndex][$movieId]) && isset($this->biasRemovedMatrix[$userId][$movieId])
                    ? $this->biasRemovedMatrix[$selectedUserIndex][$movieId] * $this->biasRemovedMatrix[$userId][$movieId]
                    : 0;

                $denominatorRight += isset($this->biasRemovedMatrix[$userId][$movieId])
                    ? pow($this->biasRemovedMatrix[$userId][$movieId], 2)
                    : 0;
            }

            foreach ($this->biasRemovedMatrix[$selectedUserIndex] as $movieId => $rating) {
                $denominatorLeft += isset($this->biasRemovedMatrix[$selectedUserIndex][$movieId])
                    ? pow($this->biasRemovedMatrix[$selectedUserIndex][$movieId], 2)
                    : 0;
            }

            $denominatorLeft = sqrt($denominatorLeft);
            $denominatorRight = sqrt($denominatorRight);
            // TODO: division by 0 - not enough
            try {
                $result = $numerator / ($denominatorLeft * $denominatorRight);
            } catch (DivisionByZeroError $error) {
                $result = 0;
                echo $error->getMessage();
            }
            $this->similarityArray[] = new Similarity($selectedUserIndex, $userId, $result);
        }
    }

    public function sortSimilarities($selectedUserIndex, $selectedMovieIndex): void
    {
        usort($this->similarityArray, function ($similarity1, $similarity2) {
            return $similarity2->getSimilarity() <=> $similarity1->getSimilarity();
        });
    }

    public function countPrediction($selectedUserIndex, $selectedMovieIndex): void
    {
        $average = $this->averageArray[$selectedUserIndex];
        $prediction = $average;
        $numerator = 0;
        $denominator = 0;
        $n = 0;
        foreach ($this->similarityArray as $userId => $similarity) {
            if (!($similarity->getMainUser() === $similarity->getOtherUser()) && isset($this->biasRemovedMatrix[$similarity->getOtherUser()][$selectedMovieIndex])) {
                $numerator += $similarity->getSimilarity() * $this->biasRemovedMatrix[$similarity->getOtherUser()][$selectedMovieIndex];
                $denominator += $similarity->getSimilarity();
                $n++;
            }
            if ($n === $this->maxSimilarUsers) {
                break;
            }
        }
        // TODO: division by 0
        try {
            $prediction += $numerator / $denominator;
        } catch (DivisionByZeroError $e) {
            echo $e->getMessage() . "<br>";
        }
        $this->predictionsArray[] = new Prediction($selectedMovieIndex, $prediction);
        print_r($this->predictionsArray);
    }

    public function showRecommendations(): void
    {
        $this->getNotRatedMovieIndexes();
        foreach ($this->notRatedMovieIndexes as $notRatedMovieIndex) {
            $this->getInteractionsMatrix($notRatedMovieIndex);
            $this->getAverageArray($notRatedMovieIndex);
            $this->removeBias($notRatedMovieIndex);
            $this->countSimilarities($this->selectedUserIndex, $notRatedMovieIndex);
            $this->sortSimilarities($this->selectedUserIndex, $notRatedMovieIndex);
            $this->countPrediction($this->selectedUserIndex, $notRatedMovieIndex);
        }
    }
}
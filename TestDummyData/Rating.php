<?php

class Rating
{
    private int $userId;
    private int $movieId;
    private float $rating;

    /**
     * @param int $userId
     * @param int $movieId
     * @param float $rating
     */
    public function __construct(int $userId, int $movieId, float $rating)
    {
        $this->userId = $userId;
        $this->movieId = $movieId;
        $this->rating = $rating;
    }

    public function getRating(): float
    {
        return $this->rating;
    }

    public function getMovieId(): int
    {
        return $this->movieId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setRating(float $rating): void
    {
        $this->rating = $rating;
    }


    public function __toString(): string
    {
        return $this->getRating();
    }


}
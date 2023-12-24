<?php

class Prediction
{
    private int $movieId;
    private float $prediction;

    /**
     * @param int $movieId
     * @param float $prediction
     */
    public function __construct(int $movieId, float $prediction)
    {
        $this->movieId = $movieId;
        $this->prediction = $prediction;
    }

    public function __toString(): string
    {
        return "$this->movieId: $this->prediction";
    }

    public function getMovieId(): int
    {
        return $this->movieId;
    }

    public function setMovieId(int $movieId): void
    {
        $this->movieId = $movieId;
    }

    public function getPrediction(): float
    {
        return $this->prediction;
    }

    public function setPrediction(float $prediction): void
    {
        $this->prediction = $prediction;
    }

}
<?php

class Similarity
{
    private int $user1;
    private int $user2;
    private float $similarity;

    /**
     * @param int $user1
     * @param int $user2
     * @param float $similarity
     */
    public function __construct(int $user1, int $user2, float $similarity)
    {
        $this->user1 = $user1;
        $this->user2 = $user2;
        $this->similarity = $similarity;
    }

    public function getUser1(): int
    {
        return $this->user1;
    }

    public function setUser1(int $user1): void
    {
        $this->user1 = $user1;
    }

    public function getUser2(): int
    {
        return $this->user2;
    }

    public function setUser2(int $user2): void
    {
        $this->user2 = $user2;
    }

    public function getSimilarity(): float
    {
        return $this->similarity;
    }

    public function setSimilarity(float $similarity): void
    {
        $this->similarity = $similarity;
    }


}
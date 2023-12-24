<?php

class Similarity
{
    private int $mainUser;
    private int $otherUser;
    private float $similarity;

    /**
     * @param int $mainUser
     * @param int $otherUser
     * @param float $similarity
     */
    public function __construct(int $mainUser, int $otherUser, float $similarity)
    {
        $this->mainUser = $mainUser;
        $this->otherUser = $otherUser;
        $this->similarity = $similarity;
    }

    public function getMainUser(): int
    {
        return $this->mainUser;
    }

    public function setMainUser(int $mainUser): void
    {
        $this->mainUser = $mainUser;
    }

    public function getOtherUser(): int
    {
        return $this->otherUser;
    }

    public function setOtherUser(int $otherUser): void
    {
        $this->otherUser = $otherUser;
    }

    public function getSimilarity(): float
    {
        return $this->similarity;
    }

    public function setSimilarity(float $similarity): void
    {
        $this->similarity = $similarity;
    }

    public function __toString(): string
    {
        return "$this->mainUser - $this->otherUser: $this->similarity";
    }


}
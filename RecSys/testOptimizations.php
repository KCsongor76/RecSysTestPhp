<?php

require '../vendor/autoload.php';
include_once '../RecSys/RecommendationSystem.php';

use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;

class NotRatedMovieIndexesBenchmark
{
    private int $selectedUserIndex = 1; // Change this to your actual user index

    /**
     * @BeforeMethods({"setUp"})
     */
    public function setUp(): void
    {
        // Additional setup if needed
    }

    /**
     * @Revs(10)
     * @Iterations(5)
     */
    public function benchOriginalVersion(): void
    {
        $recommendationSystem = new RecommendationSystem($this->selectedUserIndex);
        $recommendationSystem->getInteractionsMatrix2(1);
    }

    /**
     * @Revs(10)
     * @Iterations(5)
     */
    public function benchOptimizedVersion(): void
    {
        $recommendationSystem = new RecommendationSystem($this->selectedUserIndex);
        $recommendationSystem->getInteractionsMatrix(1);
    }
}

//$test = new NotRatedMovieIndexesBenchmark();
//$test->benchOriginalVersion();
//$test->benchOptimizedVersion();
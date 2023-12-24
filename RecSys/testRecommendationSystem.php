<?php
include_once 'RecommendationSystem.php';

$recsys = new RecommendationSystem(1);
//$arr = $recsys->getNotRatedMovieIndexes();
//$arr2 = $recsys->getInteractionsMatrix(4);
//$avg = $recsys->getAverageArray(4);
//$biasRemovedMatrix = $recsys->removeBias(4);
//$similarityArray = $recsys->countSimilarities(1, 4);
////print_r($similarityArray);
//$sortedSimilarityArray = $recsys->sortSimilarities(1, 4);
////print_r($sortedSimilarityArray);
//$recsys->countPrediction(1, 4);

$recsys->showRecommendations();


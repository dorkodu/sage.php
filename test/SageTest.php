<?php

use Dorkodu\Seekr\Seekr;
use Dorkodu\Seekr\Test\TestFunction;
use Dorkodu\Seekr\Test\TestRepository;

$passingTest = new TestFunction(
  "a passing test callback.",
  function () {
    echo "This test will pass :)";
  }
);

# initialize the test repository
$SageTest = new TestRepository();
$SageTest->function($passingTest);

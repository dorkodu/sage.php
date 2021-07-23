<?php

use Dorkodu\Seekr\Seekr;
use Dorkodu\Seekr\Test\{
  TestFunction,
  TestCase,
  TestRepository
};

Seekr::test(
  "returns an undefined item from an array",
  function () {
    $arr = [
      'a' => 1,
      'b' => 2
    ];

    if ($arr['c'] == null) {
      throw new Exception();
    }
  }
);

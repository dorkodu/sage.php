<?php

error_reporting(E_ERROR);

require "loot/loom-weaver.php";

use Dorkodu\Seekr\Seekr;


Seekr::test(
  "return if exists in array",
  function () {
    $arr = [
      'a' => 1,
      'b' => 2
    ];

    if (!array_key_exists("a", $arr)) {
      throw new Exception();
    }
  }
);

# Run Seekr
Seekr::run([
  'detailed' => 1
]);

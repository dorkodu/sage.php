<?php

error_reporting(E_ALL);

require "loot/loom-weaver.php";

use Dorkodu\Seekr\Seekr;
use Sage\Test\Index;
use Sage\Test\Usage;

//? Add tests here, before calling Seekr::run()6
//Seekr::testCase(new Index());
//Seekr::testCase(new Usage());

// Run Seekr
Seekr::run([
  'detailed' => 1
]);

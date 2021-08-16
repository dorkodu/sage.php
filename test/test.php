<?php

error_reporting(E_ALL);

require "loot/loom-weaver.php";
require "Index.php";

use Dorkodu\Seekr\Seekr;
use Sage\Test\Index;

//? Add tests here, before calling Seekr::run()
Seekr::testCase(new Index());

//? Run Seekr
Seekr::run([
  'detailed' => 1
]);

<?php

error_reporting(E_ERROR);

require "loot/loom-weaver.php";
require "SageTest.php";

use Dorkodu\Seekr\Seekr;
use Dorkodu\Seekr\Test\TestFunction;

# Run Seekr
Seekr::run([
  'detailed' => 1
]);

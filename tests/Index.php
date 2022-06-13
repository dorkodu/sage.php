<?php

namespace Sage\Test;

use Exception;

use Dorkodu\Seekr\Seekr;
use Dorkodu\Seekr\Test\TestCase;

class Index extends TestCase
{
  /**
   * This test will pass
   */
  public function testPassing()
  {
    echo "This is the output from a passing test";
  }

  /**
   * This test will fail
   */
  public function testFailing()
  {
    echo "This is the output from a failed test";
    throw new Exception("This is an exception from a failed test.");
  }
}

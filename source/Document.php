<?php

namespace Sage;

use Sage\Query;

class Document
{
  /**
   * The map of queries.
   * @var Query[]
   */
  private $map = [];

  public function toArray()
  {
    return $this->map;
  }

  public function query(string $name)
  {
    return array_key_exists($name, $this->map)
      ? $this->map[$name]
      : null;
  }
}

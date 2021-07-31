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

  /**
   * Returns the query with given name.
   *
   * @param string $name
   * @return Query if has a query with given name
   * @return null if does not have a query with given name
   */
  public function query(string $name)
  {
    return array_key_exists($name, $this->map)
      ? $this->map[$name]
      : null;
  }

  /**
   * Adds a query to document.
   *
   * @param string $name
   * @param Query $query
   * @return void
   */
  public function addQuery(string $name, Query $query)
  {
    $this->map[$name] = $query;
  }

  /**
   * Removes a query from document.
   *
   * @param string $name
   * @return void
   */
  public function removeQuery(string $name)
  {
    unset($this->map[$name]);
  }

  public function __construct(array $queries)
  {
    /*
     * $queries array must be in this shape: 
     * [ string : Query ]
     * 
     * assert: 
     *  - it has keys as strings
     *  - it has values as Sage\Query instances.
     */
    $this->map = $queries;
  }
}

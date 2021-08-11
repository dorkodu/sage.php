<?php

namespace Sage\Type\Definition;

class Artifact
{
  /** @var string|null */
  public $description;

  /** @var bool */
  public $deprecated = false;

  /** @var string|null */
  public $deprecationReason;

  /**
   * Original type artifact definition configuration
   *
   * @var array
   */
  public $config;

  public function __construct(array $config)
  {
    $this->description       = $config['description'] ?? null;
    $this->deprecationReason = $config['deprecationReason'] ?? null;
    $this->deprecated = $config['deprecated'] ??
      (isset($config['deprecationReason']) ? true : false);

    $this->config = $config;
  }

  /**
   * @throws InvariantViolation
   */
  public function assertValid(Type $parentType)
  {
  }
}

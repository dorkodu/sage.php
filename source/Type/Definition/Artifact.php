<?php

namespace Sage\Type\Definition;

use Sage\Error\Error;
use Sage\Utils\Utils;
use Sage\Error\InvariantViolation;

abstract class Artifact
{
  public const ATTRIBUTE = 'attribute';
  public const ACT = 'act';
  public const LINK = 'link';

  /** @var string */
  public $name;

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
    $this->name = $config['name'];
    $this->description = $config['description'] ?? null;
    $this->deprecationReason = $config['deprecationReason'] ?? null;
    $this->deprecated = $config['deprecated'] ??
      (isset($config['deprecationReason']) ? true : false);

    $this->config = $config;
  }

  /**
   * @throws InvariantViolation
   */
  abstract public function assertValid(Type $parentType);

  public function assertNameIsValid(Type $parentType)
  {
    try {
      Utils::assertValidName($this->name);
    } catch (Error $e) {
      throw new InvariantViolation(
        sprintf('%s.%s: %s', $parentType->name, $this->name, $e->getMessage())
      );
    }
  }
}

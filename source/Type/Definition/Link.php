<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use Sage\Utils\Utils;
use Sage\Type\Definition\Type;
use Sage\Type\Definition\Entity;
use Sage\Type\Definition\Artifact;

class Link extends Artifact
{
  /**
   * Callback for resolving field value given parent value.
   *
   * @var callable
   */
  public $resolve;

  /**
   * The Entity type this link connects to.
   *
   * @var Entity 
   */
  public $type;

  /**
   * @param mixed[] $config
   */
  protected function __construct(array $config)
  {
    parent::__construct($config);

    $this->type = $config['type'] ?? null;
    $this->resolve = $config['resolve'] ?? null;
  }

  /**
   * @throws InvariantViolation
   */
  public function assertValid(Type $parentType)
  {
    $this->assertNameIsValid($parentType);
    $this->assertResolveIsValid($parentType);
  }

  public function assertTypeIsValid(Type $parentType)
  {
    //? Assert: type is an instance of Entity. 
    Utils::invariant(
      $this->type instanceof Entity,
      sprintf(
        '%s.%s - Link type must be Entity but got: %s',
        $parentType->name,
        $this->name,
        Utils::printSafe($this->type)
      )
    );
  }

  public function assertResolveIsValid(Type $parentType)
  {
    //? Assert: resolver is a callable. 
    Utils::invariant(
      is_callable($this->resolve),
      sprintf(
        '%s.%s - Link resolver must be a function, but got: %s',
        $parentType->name,
        $this->name,
        Utils::printSafe($this->resolve)
      )
    );
  }
}

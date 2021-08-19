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
   * Callback for resolving link given reference value and context info.
   * Returns a map to be used as reference value when querying for linked Entity type.
   *
   * @var callable
   */
  public $resolve;

  /**
   * The linked Entity type.
   *
   * @var Entity
   */
  public $linksTo;

  /**
   * @param mixed[] $config
   */
  public function __construct(array $config)
  {
    parent::__construct($config);

    $this->linksTo = $config['linksTo'] ?? null;
    $this->resolve = $config['resolve'] ?? null;
  }

  /**
   * @throws InvariantViolation
   */
  public function assertValid(Type $parentType)
  {
    $this->assertNameIsValid($parentType);
    $this->assertResolveIsValid($parentType);
    $this->assertLinkedTypeIsValid($parentType);
  }

  public function assertLinkedTypeIsValid(Type $parentType)
  {
    //? Assert: linksTo is an instance of Entity. 
    Utils::invariant(
      $this->linksTo instanceof Entity,
      sprintf(
        '%s.%s - Linked type must be Entity but got: %s',
        $parentType->name,
        $this->name,
        Utils::printSafe($this->linksTo)
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

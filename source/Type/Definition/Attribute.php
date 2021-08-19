<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use function sprintf;
use Sage\Utils\Utils;
use function is_callable;
use Sage\Error\InvariantViolation;
use Sage\Type\Definition\Artifact;

class Attribute extends Artifact
{
  /**
   * Callback for resolving field value given parent value.
   *
   * @var callable
   */
  private $resolve;

  /**
   * Type constraint for attribute.
   *
   * @var Type|null
   */
  private $type;

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
    $this->assertTypeConstraintIsValid($parentType);
  }

  public function assertTypeConstraintIsValid(Type $parentType)
  {
    $type = $this->type;

    if ($type !== null) {
      if ($type instanceof WrappingType) {
        $type = $type->wrappedType(true);
      }

      //? Assert: type constraint is either null or an instance of Type & OutputType 
      Utils::invariant(
        $type instanceof OutputType,
        sprintf(
          '%s.%s - Attribute type constraint must be either null or Output Type but got: %s',
          $parentType->name,
          $this->name,
          Utils::printSafe($this->type)
        )
      );
    }
  }

  public function assertResolveIsValid(Type $parentType)
  {
    //? Assert: resolve is a callable
    Utils::invariant(
      is_callable($this->resolve),
      sprintf(
        '%s.%s - Attribute resolver must be a function, but got: %s',
        $parentType->name,
        $this->name,
        Utils::printSafe($this->resolve)
      )
    );
  }
}

<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use Sage\Type\Definition\Artifact;
use Sage\Error\InvariantViolation;
use Sage\Utils\Utils;
use function sprintf;
use function is_callable;

class Attribute extends Artifact
{
  /**
   * Callback for resolving attribute value given reference value.
   * 
   * @var callable
   */
  public $resolve;

  /**
   * Type constraint for the attribute.
   * 
   * @var Type|null
   */
  public $type;

  /**
   * @param mixed[] $config
   */
  public function __construct(array $config)
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
          Utils::printSafe($type)
        )
      );
    }
  }

  public function hasTypeConstraint()
  {
    return $this->type !== null
      && $this->type instanceof Type;
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

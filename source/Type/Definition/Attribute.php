<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use function sprintf;
use Sage\Error\Error;
use Sage\Type\Schema;
use Sage\Utils\Utils;
use function is_array;
use function is_string;
use Sage\Error\Warning;
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
  private $resolveFunction;

  /** @var Type|null */
  private $type;

  /**
   * @param mixed[] $config
   */
  protected function __construct(array $config)
  {
    parent::__construct($config);

    $this->type = $config['type'] ?? null;
    $this->resolveFunction = $config['resolve'] ?? null;
  }

  public function type(): Type
  {
    if (!isset($this->type)) {
      $type = Schema::resolveType($this->config['type']);

      //? Assert: $type must be an instance of Type
      Utils::premise($type instanceof Type);
      $this->type = $type;
    }

    return $this->type;
  }

  public function resolve(): callable
  {
    if (!isset($this->type)) {
      $resolve = $this->config['resolve'] ?? null;

      //? Assert: $resolve must be a callable
      Utils::premise(is_callable($resolve));

      $this->resolveFunction = $resolve;
    }

    return $this->resolveFunction;
  }

  /**
   * @throws InvariantViolation
   */
  public function assertValid(Type $parentType)
  {
    //? Assert: resolver is a callable 
    Utils::invariant(
      is_callable($this->resolveFunction),
      sprintf(
        '%s - attribute resolver must be a function, but got: %s',
        $parentType->name,
        Utils::printSafe($this->resolveFunction)
      )
    );
  }
}

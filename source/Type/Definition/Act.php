<?php

namespace Sage\Type\Definition;

use Sage\Utils\Utils;

class Act extends Artifact
{
  /**
   * Callback for resolving field value given parent value.
   *
   * @var callable
   */
  public $callback;

  public function __construct(array $config)
  {
    parent::__construct($config);
    $this->resolveFunction   = $config['resolve'] ?? null;
  }

  public function assertValid(Type $parentType)
  {
    //? Assert: $this->callback is a callable
    Utils::invariant(
      is_callable($this->callback),
      sprintf(
        '%s - Act function must be a callable, but got: %s',
        $parentType->name,
        Utils::printSafe($this->callback)
      )
    );
  }
}

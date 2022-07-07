<?php

  namespace Sage\Type\Definition;

  use Sage\Utils\Utils;

  class Act extends Artifact
  {
      /** @var callable */
      public $do;

      public function __construct(array $settings)
      {
          parent::__construct($settings);
          $this->do = $settings['do'] ?? null;
      }

      public function assertValid(Type $parentType)
      {
          $this->assertNameIsValid($parentType);
          $this->assertCallbackIsValid($parentType);
      }

      public function assertCallbackIsValid(Type $parentType)
      {
          //? Assert: $this->do is a callable
          Utils::invariant(
              is_callable($this->do),
              sprintf(
                  "%s.%s - Act function 'do' must be a callable, but got: %s",
                  $parentType->name,
                  $this->name,
                  Utils::printSafe($this->do)
              )
          );
      }
  }

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
    public $do;

    public function __construct(array $config)
    {
      parent::__construct($config);
      $this->do = $config['do'] ?? null;
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

<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use Sage\Error\InvariantViolation;
use Sage\Type\Introspection;
use Sage\Utils\Utils;
use JsonSerializable;
use ReflectionClass;
use function array_keys;
use function array_merge;
use function assert;
use function implode;
use function in_array;
use function preg_replace;

/**
 * Registry of standard Sage types
 * and a base class for all other types.
 */
abstract class Type implements JsonSerializable
{
  public const STRING  = 'String';
  public const INT     = 'Int';
  public const BOOLEAN = 'Boolean';
  public const FLOAT   = 'Float';
  public const ID      = 'ID';

  /** @var array<string, ScalarType> */
  protected static $standardTypes;

  /** @var Type[] */
  private static $builtInTypes;

  /** @var string */
  public $name;

  /** @var string|null */
  public $description;

  /** @var mixed[] */
  public $config;

  /**
   * @api
   */
  public static function id(): ScalarType
  {
    if (!isset(static::$standardTypes[self::ID])) {
      static::$standardTypes[self::ID] = new IDType();
    }

    return static::$standardTypes[self::ID];
  }

  /**
   * @api
   */
  public static function string(): ScalarType
  {
    if (!isset(static::$standardTypes[self::STRING])) {
      static::$standardTypes[self::STRING] = new StringType();
    }

    return static::$standardTypes[self::STRING];
  }

  /**
   * @api
   */
  public static function boolean(): ScalarType
  {
    if (!isset(static::$standardTypes[self::BOOLEAN])) {
      static::$standardTypes[self::BOOLEAN] = new BooleanType();
    }

    return static::$standardTypes[self::BOOLEAN];
  }

  /**
   * @api
   */
  public static function int(): ScalarType
  {
    if (!isset(static::$standardTypes[self::INT])) {
      static::$standardTypes[self::INT] = new IntType();
    }

    return static::$standardTypes[self::INT];
  }

  /**
   * @api
   */
  public static function float(): ScalarType
  {
    if (!isset(static::$standardTypes[self::FLOAT])) {
      static::$standardTypes[self::FLOAT] = new FloatType();
    }

    return static::$standardTypes[self::FLOAT];
  }

  /**
   * @api
   */
  public static function listOf(Type $wrappedType): ListOfType
  {
    return new ListOfType($wrappedType);
  }

  /**
   * @api
   */
  public static function nonNull(Type $wrappedType): NonNull
  {
    return new NonNull($wrappedType);
  }

  /**
   * Checks if the type is a builtin type
   */
  public static function isBuiltInType(Type $type): bool
  {
    return in_array($type->name, array_keys(self::allBuiltInTypes()), true);
  }

  /**
   * Returns all builtin in types including base scalar and
   * introspection types
   *
   * @return Type[]
   */
  public static function allBuiltInTypes()
  {
    if (self::$builtInTypes === null) {
      self::$builtInTypes = array_merge(
        Introspection::getTypes(),
        self::standardTypes()
      );
    }

    return self::$builtInTypes;
  }

  /**
   * Returns all builtin scalar types
   *
   * @return ScalarType[]
   */
  public static function standardTypes()
  {
    return [
      self::ID => static::id(),
      self::STRING => static::string(),
      self::FLOAT => static::float(),
      self::INT => static::int(),
      self::BOOLEAN => static::boolean(),
    ];
  }

  /**
   * @param array<string, ScalarType> $types
   */
  public static function overrideStandardTypes(array $types)
  {
    $standardTypes = self::standardTypes();

    foreach ($types as $type) {
      Utils::invariant(
        $type instanceof Type,
        'Expecting instance of %s, got %s',
        self::class,
        Utils::printSafe($type)
      );

      Utils::invariant(
        isset($type->name, $standardTypes[$type->name]),
        'Expecting one of the following names for a standard type: %s, got %s',
        implode(', ', array_keys($standardTypes)),
        Utils::printSafe($type->name ?? null)
      );

      static::$standardTypes[$type->name] = $type;
    }
  }

  /**
   * @param Type $type
   *
   * @api
   */
  public static function namedType($type): ?Type
  {
    if ($type === null) {
      return null;
    }

    while ($type instanceof WrappingType) {
      $type = $type->wrappedType();
    }

    return $type;
  }

  /**
   * @param mixed $type
   */
  public static function assertType($type): Type
  {
    assert($type instanceof Type, new InvariantViolation('Expected ' . Utils::printSafe($type) . ' to be a Sage type.'));
    return $type;
  }

  /**
   * @api
   */
  public static function nullableType(Type $type): Type
  {
    return $type instanceof NonNull
      ? $type->wrappedType()
      : $type;
  }

  /**
   * @throws InvariantViolation
   */
  public function assertValid()
  {
    Utils::assertValidName($this->name);
  }

  /**
   * @return string
   */
  public function jsonSerialize()
  {
    return $this->toString();
  }

  /**
   * @return string
   */
  public function toString()
  {
    return $this->name;
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return $this->toString();
  }

  /**
   * @return string|null
   */
  protected function tryInferName()
  {
    if ($this->name) {
      return $this->name;
    }

    /*
     ? If class is extended - infer name from className
     ? QueryType --> Type
     ? SomeOtherType --> SomeOther
     */
    $tmp  = new ReflectionClass($this);
    $name = $tmp->getShortName();

    if ($tmp->getNamespaceName() !== __NAMESPACE__) {
      return preg_replace('~Type$~', '', $name);
    }

    return null;
  }
}

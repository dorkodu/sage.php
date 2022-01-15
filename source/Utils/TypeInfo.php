<?php

declare(strict_types=1);

namespace Sage\Utils;

use Sage\Error\InvariantViolation;
use Sage\Type\Definition\CompositeType;
use Sage\Type\Definition\Directive;
use Sage\Type\Definition\EnumType;
use Sage\Type\Definition\FieldArgument;
use Sage\Type\Definition\FieldDefinition;
use Sage\Type\Definition\HasFieldsType;
use Sage\Type\Definition\ImplementingType;
use Sage\Type\Definition\InputObjectType;
use Sage\Type\Definition\InputType;
use Sage\Type\Definition\InterfaceType;
use Sage\Type\Definition\ListOfType;
use Sage\Type\Definition\ObjectType;
use Sage\Type\Definition\OutputType;
use Sage\Type\Definition\Type;
use Sage\Type\Definition\UnionType;
use Sage\Type\Definition\WrappingType;
use Sage\Type\Introspection;
use Sage\Type\Schema;

use function array_merge;
use function array_pop;
use function count;
use function is_array;
use function sprintf;

class TypeInfo
{
  /** @var Schema */
  private $schema;

  /** @var array<(OutputType&Type)|null> */
  private $typeStack;

  /** @var array<(CompositeType&Type)|null> */
  private $parentTypeStack;

  /** @var array<(InputType&Type)|null> */
  private $inputTypeStack;

  /** @var array<FieldDefinition> */
  private $fieldDefStack;

  /** @var array<mixed> */
  private $defaultValueStack;

  /** @var Directive|null */
  private $directive;

  /** @var FieldArgument|null */
  private $argument;

  /** @var mixed */
  private $enumValue;

  /**
   * @param Type|null $initialType
   */
  public function __construct(Schema $schema, $initialType = null)
  {
    $this->schema            = $schema;
    $this->typeStack         = [];
    $this->parentTypeStack   = [];
    $this->inputTypeStack    = [];
    $this->fieldDefStack     = [];
    $this->defaultValueStack = [];

    if ($initialType === null) {
      return;
    }

    if (Type::isInputType($initialType)) {
      $this->inputTypeStack[] = $initialType;
    }

    if (Type::isCompositeType($initialType)) {
      $this->parentTypeStack[] = $initialType;
    }

    if (!Type::isOutputType($initialType)) {
      return;
    }

    $this->typeStack[] = $initialType;
  }

  /**
   * Given root type scans through all fields to find nested types. Returns array where keys are for type name
   * and value contains corresponding type instance.
   *
   * Example output:
   * [
   *     'String' => $instanceOfStringType,
   *     'MyType' => $instanceOfMyType,
   *     ...
   * ]
   *
   * @param Type|null   $type
   * @param Type[]|null $typeMap
   *
   * @return Type[]|null
   */
  public static function extractTypes($type, ?array $typeMap = null)
  {
    if (($typeMap ?? []) === []) {
      $typeMap = [];
    }

    if ($type === null) {
      return $typeMap;
    }

    if ($type instanceof WrappingType) {
      return self::extractTypes($type->getWrappedType(true), $typeMap);
    }

    if (!$type instanceof Type) {
      // Preserve these invalid types in map (at numeric index) to make them
      // detectable during $schema->validate()
      $i            = 0;
      $alreadyInMap = false;
      while (isset($typeMap[$i])) {
        $alreadyInMap = $alreadyInMap || $typeMap[$i] === $type;
        $i++;
      }

      if (!$alreadyInMap) {
        $typeMap[$i] = $type;
      }

      return $typeMap;
    }

    if (isset($typeMap[$type->name])) {
      Utils::invariant(
        $typeMap[$type->name] === $type,
        sprintf('Schema must contain unique named types but contains multiple types named "%s" ', $type) .
          '(see https://webonyx.github.io/graphql-php/type-definitions/#type-registry).'
      );

      return $typeMap;
    }

    $typeMap[$type->name] = $type;

    $nestedTypes = [];

    if ($type instanceof UnionType) {
      $nestedTypes = $type->getTypes();
    }

    if ($type instanceof ImplementingType) {
      $nestedTypes = array_merge($nestedTypes, $type->getInterfaces());
    }

    if ($type instanceof HasFieldsType) {
      foreach ($type->getFields() as $field) {
        foreach ($field->args as $arg) {
          $nestedTypes[] = $arg->getType();
        }

        $nestedTypes[] = $field->getType();
      }
    }

    if ($type instanceof InputObjectType) {
      foreach ($type->getFields() as $field) {
        $nestedTypes[] = $field->getType();
      }
    }

    foreach ($nestedTypes as $nestedType) {
      $typeMap = self::extractTypes($nestedType, $typeMap);
    }

    return $typeMap;
  }

  /**
   * @return (Type & OutputType) | null
   */
  public function getType(): ?OutputType
  {
    return $this->typeStack[count($this->typeStack) - 1] ?? null;
  }

  /**
   * @return (CompositeType & Type) | null
   */
  public function getParentType(): ?CompositeType
  {
    return $this->parentTypeStack[count($this->parentTypeStack) - 1] ?? null;
  }
}

<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use function array_keys;
use function array_merge;
use function assert;
use function implode;
use function in_array;
use JsonSerializable;
use function preg_replace;
use ReflectionClass;
use Sage\Error\InvariantViolation;
use Sage\Type\Introspection;
use Sage\Utils\Utils;

/**
 * Registry of standard Sage types
 * and a base class for all other types.
 */
abstract class Type implements JsonSerializable
{
    /** @var string */
    public $name;

    /** @var string|null */
    public $description;

    /** @var mixed[] */
    public $config;

    /**
     * @param mixed $type
     */
    public static function assertType($type): Type
    {
        assert($type instanceof Type, new InvariantViolation('Expected '.Utils::printSafe($type).' to be a Sage type.'));

        return $type;
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
        $tmp = new ReflectionClass($this);
        $name = $tmp->getShortName();

        if (__NAMESPACE__ !== $tmp->getNamespaceName()) {
            return preg_replace('~Type$~', '', $name);
        }

        return null;
    }
}

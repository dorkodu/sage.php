<?php

declare(strict_types=1);

namespace Sage\Type;

use Generator;
use function is_array;
use function is_callable;
use Sage\Error\Error;
use Sage\Error\InvariantViolation;
use Sage\Type\Definition\Entity;
use Sage\Type\Definition\Type;
use Sage\Utils\TypeInfo;
use Sage\Utils\Utils;
use function sprintf;
use Traversable;

/**
 * Schema Definition.
 */
class Schema
{
    /** @var Error[] */
    private $validationErrors;

    /** @var Type[] */
    public $types = [];

    /** @var callable|null */
    public $typeLoader;

    /** @var bool */
    public $assumeValid = false;

    /**
     * @return Entity|null
     *
     * @api
     */
    public function entityType(string $name)
    {
        return array_key_exists($name, $this->types)
          ? $this->types[$name]
          : null;
    }

    /**
     * @param string $name
     *
     * @return void
     *
     * @api
     */
    public function newEntityType(Entity $type)
    {
        $this->types[$type->name] = $type;
    }

    /**
     * @param Type[] $types
     *
     * @api
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    /**
     * @return void
     *
     * @api
     */
    public function setTypeLoader(callable $typeLoader)
    {
        $this->typeLoader = $typeLoader;
    }

    public function assumeValid(?bool $value = null)
    {
        if (null === $value) {
            return $this->assumeValid;
        }

        $this->assumeValid = $value;
    }

    /**
     * @api
     *
     * @param Type[] $types
     */
    public function __construct(array $types)
    {
        // Create schema from array
        if (is_array($config)) {
            $config = static::create($config);
        }

        /*
         * If this schema was built from a source known to be valid, then it may be
         * marked with assumeValid to avoid an additional type system validation.
         */
        if ($this->assumeValid()) {
            $this->validationErrors = [];
        } else {
            /*
             * Otherwise check for common mistakes during construction to produce
             * clear and early error messages.
             */

            Utils::invariant(
                $config instanceof SchemaConfig,
                'Schema constructor expects an array with string keys as Entity type names and values as Entity types, but got: %s',
                Utils::getVariableType($config)
            );

            /*
             ? Example of an invariant violation:
             *
             * Utils::invariant(
             *  !$config->types || is_array($config->types) || is_callable($config->types),
             *  '"types" must be array or callable if provided but got: ' . Utils::getVariableType($config->types)
             * );
             */
        }

        if (null !== $config->query) {
            $this->resolvedTypes[$config->query->name] = $config->query;
        }

        if (null !== $config->mutation) {
            $this->resolvedTypes[$config->mutation->name] = $config->mutation;
        }

        if (null !== $config->subscription) {
            $this->resolvedTypes[$config->subscription->name] = $config->subscription;
        }

        if (is_array($this->config->types)) {
            foreach ($this->resolveAdditionalTypes() as $type) {
                if (isset($this->resolvedTypes[$type->name])) {
                    Utils::invariant(
                        $type === $this->resolvedTypes[$type->name],
                        sprintf(
                            'Schema must contain unique named types but contains multiple types named "%s" (see http://libre.dorkodu.com/sage.php/type-system/#type-registry).',
                            $type
                        )
                    );
                }
                $this->resolvedTypes[$type->name] = $type;
            }
        }

        $this->resolvedTypes += Type::getStandardTypes() + Introspection::types();

        if ($this->config->typeLoader) {
            return;
        }

        // Perform full scan of the schema
        $this->typeMap();
    }

    /**
     * @return Generator
     */
    private function resolveAdditionalTypes()
    {
        $types = $this->config->types ?? [];

        if (is_callable($types)) {
            $types = $types();
        }

        if (!is_array($types) && !$types instanceof Traversable) {
            throw new InvariantViolation(sprintf('Schema types callable must return array or instance of Traversable but got: %s', Utils::getVariableType($types)));
        }

        foreach ($types as $index => $type) {
            $type = self::resolveType($type);
            if (!$type instanceof Type) {
                throw new InvariantViolation(sprintf('Each entry of schema types must be instance of Sage\Type\Definition\Type but entry at %s is %s', $index, Utils::printSafe($type)));
            }
            yield $type;
        }
    }

    /**
     * Returns array of all types in this schema. Keys of this array represent type names, values are instances
     * of corresponding type definitions.
     *
     * This operation requires full schema scan. Do not use in production environment.
     *
     * @return Type[]
     *
     * @api
     */
    public function typeMap()
    {
        if (!$this->fullyLoaded) {
            $this->resolvedTypes = $this->collectAllTypes();
            $this->fullyLoaded = true;
        }

        return $this->resolvedTypes;
    }

    /**
     * @return Type[]
     */
    private function collectAllTypes()
    {
        $typeMap = [];

        foreach ($this->resolvedTypes as $type) {
            $typeMap = TypeInfo::extractTypes($type, $typeMap);
        }

        // When types are set as array they are resolved in constructor
        if (is_callable($this->config->types)) {
            foreach ($this->resolveAdditionalTypes() as $type) {
                $typeMap = TypeInfo::extractTypes($type, $typeMap);
            }
        }

        return $typeMap;
    }

    /**
     * Returns type by its name.
     *
     * @api
     */
    public function type(string $name): ?Type
    {
        if (!isset($this->resolvedTypes[$name])) {
            $type = $this->loadType($name);

            if (!$type) {
                return null;
            }
            $this->resolvedTypes[$name] = self::resolveType($type);
        }

        return $this->resolvedTypes[$name];
    }

    public function hasType(string $name): bool
    {
        return null !== $this->type($name);
    }

    private function loadType(string $typeName): ?Type
    {
        $typeLoader = $this->config->typeLoader;

        if (!isset($typeLoader)) {
            return $this->defaultTypeLoader($typeName);
        }

        $type = $typeLoader($typeName);

        if (!$type instanceof Type) {
            /*
             ! Unless you know what you're doing, kindly resist the temptation to refactor or simplify this block. The
             ! twisty logic here is tuned for performance, and meant to prioritize the "happy path" (the result returned
             ! from the type loader is already a Type), and only checks for callable if that fails. If the result is
             ! neither a Type nor a callable, then we throw an exception.
             */

            if (is_callable($type)) {
                $type = $type();

                if (!$type instanceof Type) {
                    $this->throwNotAType($type, $typeName);
                }
            } else {
                $this->throwNotAType($type, $typeName);
            }
        }

        if ($type->name !== $typeName) {
            throw new InvariantViolation(sprintf('Type loader is expected to return type "%s", but it returned "%s"', $typeName, $type->name));
        }

        return $type;
    }

    protected function throwNotAType($type, string $typeName)
    {
        throw new InvariantViolation(sprintf('Type loader is expected to return a callable or valid type "%s", but it returned %s', $typeName, Utils::printSafe($type)));
    }

    private function defaultTypeLoader(string $typeName): ?Type
    {
        // Default type loader simply falls back to collecting all types
        $typeMap = $this->typeMap();

        return $typeMap[$typeName] ?? null;
    }

    /**
     * @param Type|callable():Type $type
     */
    public static function resolveType($type): Type
    {
        if ($type instanceof Type) {
            return $type;
        }

        return $type();
    }
}

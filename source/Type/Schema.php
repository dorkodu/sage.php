<?php

declare(strict_types=1);

namespace Sage\Type;

use Generator;
use Sage\Error\Error;
use Sage\Error\InvariantViolation;
use Sage\Sage;
use Sage\Type\Definition\Type;
use Sage\Utils\Utils;
use InvalidArgumentException;
use Traversable;

use function get_class;
use function implode;
use function is_array;
use function is_callable;
use function sprintf;

class Schema
{
    private $config;

    /**
     * Contains currently resolved schema types
     *
     * @var array<string, Type>
     */
    private array $resolvedTypes = [];

    /**
     * True when $resolvedTypes contains all possible schema types.
     */
    private bool $fullyLoaded = false;

    /** @var array<int, Error> */
    private array $validationErrors;

    /**
     * @param array<string, mixed> $config
     *
     * @api
     */
    public function __construct($config)
    {
        if (is_array($config)) {
            //? TODO: Configure
        }

        /**
         * If this schema was built from a source known to be valid, then it may be
         * marked with assumeValid to avoid an additional type system validation.
         */
        if ($this->assumeValid) {
            $this->validationErrors = [];
        }

        $this->config = $config;

        if (is_array($this->config->types)) {
            foreach ($this->resolveAdditionalTypes() as $type) {
                $typeName = $type->name;
                if (isset($this->resolvedTypes[$typeName])) {
                    Utils::invariant(
                        $type === $this->resolvedTypes[$typeName],
                        sprintf(
                            'Schema must contain unique named types but contains multiple types named "%s" (see https://webonyx.github.io/graphql-php/type-definitions/#type-registry).',
                            $type
                        )
                    );
                }

                $this->resolvedTypes[$typeName] = $type;
            }
        }

        $this->resolvedTypes += Type::getStandardTypes() + Introspection::getTypes();

        if ($this->config->typeLoader !== null) {
            return;
        }

        // Perform full scan of the schema
        $this->getTypeMap();
    }

    private function resolveAdditionalTypes(): Generator
    {
        $types = $this->config->types;

        if (is_callable($types)) {
            $types = $types();
        }

        if (! is_array($types) && ! $types instanceof Traversable) {
            throw new InvariantViolation(sprintf(
                'Schema types callable must return array or instance of Traversable but got: %s',
                Utils::getVariableType($types)
            ));
        }

        foreach ($types as $index => $type) {
            $type = self::resolveType($type);
            if (! $type instanceof Type) {
                throw new InvariantViolation(sprintf(
                    'Each entry of schema types must be instance of Sage\Type\Definition\Type but entry at %s is %s',
                    $index,
                    Utils::printSafe($type)
                ));
            }

            yield $type;
        }
    }

    /**
     * Returns all types in this schema.
     *
     * This operation requires a full schema scan. Do not use in production environment.
     *
     * @return array<string, Type> Keys represent type names, values are instances of corresponding type definitions
     *
     * @api
     */
    public function getTypeMap(): array
    {
        if (! $this->fullyLoaded) {
            $this->resolvedTypes = $this->collectAllTypes();
            $this->fullyLoaded   = true;
        }

        return $this->resolvedTypes;
    }

    /**
     * @return array<Type>
     */
    private function collectAllTypes(): array
    {
        $typeMap = [];
        foreach ($this->resolvedTypes as $type) {
            $typeMap = TypeInfo::extractTypes($type, $typeMap);
        }

        foreach ($this->getDirectives() as $directive) {
            if (! ($directive instanceof Directive)) {
                continue;
            }

            $typeMap = TypeInfo::extractTypesFromDirectives($directive, $typeMap);
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
     * Returns a list of directives supported by this schema
     *
     * @return array<Directive>
     *
     * @api
     */
    public function getDirectives(): array
    {
        return $this->config->directives ?? Sage::getStandardDirectives();
    }

    public function getOperationType(string $operation): ?ObjectType
    {
        switch ($operation) {
            case 'query':
                return $this->getQueryType();

            case 'mutation':
                return $this->getMutationType();

            case 'subscription':
                return $this->getSubscriptionType();

            default:
                return null;
        }
    }

    /**
     * Returns root query type.
     *
     * @api
     */
    public function getQueryType(): ?ObjectType
    {
        return $this->config->query;
    }

    /**
     * Returns root mutation type.
     *
     * @api
     */
    public function getMutationType(): ?ObjectType
    {
        return $this->config->mutation;
    }

    /**
     * Returns schema subscription
     *
     * @api
     */
    public function getSubscriptionType(): ?ObjectType
    {
        return $this->config->subscription;
    }

    /**
     * @api
     */
    public function getConfig(): SchemaConfig
    {
        return $this->config;
    }

    /**
     * Returns a type by name.
     *
     * @api
     */
    public function getType(string $name): ?Type
    {
        if (! isset($this->resolvedTypes[$name])) {
            $type = $this->loadType($name);

            if ($type === null) {
                return null;
            }

            $this->resolvedTypes[$name] = self::resolveType($type);
        }

        return $this->resolvedTypes[$name];
    }

    public function hasType(string $name): bool
    {
        return $this->getType($name) !== null;
    }

    private function loadType(string $typeName): ?Type
    {
        $typeLoader = $this->config->typeLoader;

        if (! isset($typeLoader)) {
            return $this->defaultTypeLoader($typeName);
        }

        $type = $typeLoader($typeName);

        if (! $type instanceof Type) {
            // Unless you know what you're doing, kindly resist the temptation to refactor or simplify this block. The
            // twisty logic here is tuned for performance, and meant to prioritize the "happy path" (the result returned
            // from the type loader is already a Type), and only checks for callable if that fails. If the result is
            // neither a Type nor a callable, then we throw an exception.

            if (is_callable($type)) {
                $type = $type();

                if (! $type instanceof Type) {
                    $this->throwNotAType($type, $typeName);
                }
            } else {
                $this->throwNotAType($type, $typeName);
            }
        }

        if ($type->name !== $typeName) {
            throw new InvariantViolation(
                sprintf('Type loader is expected to return type "%s", but it returned "%s"', $typeName, $type->name)
            );
        }

        return $type;
    }

    protected function throwNotAType($type, string $typeName): void
    {
        throw new InvariantViolation(
            sprintf(
                'Type loader is expected to return a callable or valid type "%s", but it returned %s',
                $typeName,
                Utils::printSafe($type)
            )
        );
    }

    private function defaultTypeLoader(string $typeName): ?Type
    {
        // Default type loader simply falls back to collecting all types
        $typeMap = $this->getTypeMap();

        return $typeMap[$typeName] ?? null;
    }

    /**
     * @param Type|callable $type
     * @phpstan-param T|callable():T $type
     *
     * @phpstan-return T
     *
     * @template T of Type
     */
    public static function resolveType($type): Type
    {
        if ($type instanceof Type) {
            /** @phpstan-var T $type */
            return $type;
        }

        return $type();
    }

    /**
     * Returns all possible concrete types for given abstract type
     * (implementations for interfaces and members of union type for unions)
     *
     * This operation requires full schema scan. Do not use in production environment.
     *
     * @param InterfaceType|UnionType $abstractType
     *
     * @return array<Type&ObjectType>
     *
     * @api
     */
    public function getPossibleTypes(Type $abstractType): array
    {
        return $abstractType instanceof UnionType
            ? $abstractType->getTypes()
            : $this->getImplementations($abstractType)->objects();
    }

    /**
     * Returns all types that implement a given interface type.
     *
     * This operations requires full schema scan. Do not use in production environment.
     *
     * @api
     */
    public function getImplementations(InterfaceType $abstractType): InterfaceImplementations
    {
        return $this->collectImplementations()[$abstractType->name];
    }

    /**
     * @return array<string, InterfaceImplementations>
     */
    private function collectImplementations(): array
    {
        if (! isset($this->implementationsMap)) {
            /** @var array<string, array<string, Type>> $foundImplementations */
            $foundImplementations = [];
            foreach ($this->getTypeMap() as $type) {
                if ($type instanceof InterfaceType) {
                    if (! isset($foundImplementations[$type->name])) {
                        $foundImplementations[$type->name] = ['objects' => [], 'interfaces' => []];
                    }

                    foreach ($type->getInterfaces() as $iface) {
                        if (! isset($foundImplementations[$iface->name])) {
                            $foundImplementations[$iface->name] = ['objects' => [], 'interfaces' => []];
                        }

                        $foundImplementations[$iface->name]['interfaces'][] = $type;
                    }
                } elseif ($type instanceof ObjectType) {
                    foreach ($type->getInterfaces() as $iface) {
                        if (! isset($foundImplementations[$iface->name])) {
                            $foundImplementations[$iface->name] = ['objects' => [], 'interfaces' => []];
                        }

                        $foundImplementations[$iface->name]['objects'][] = $type;
                    }
                }
            }

            foreach ($foundImplementations as $name => $implementations) {
                $this->implementationsMap[$name] = new InterfaceImplementations($implementations['objects'], $implementations['interfaces']);
            }
        }

        return $this->implementationsMap;
    }

    /**
     * Returns true if the given type is a sub type of the given abstract type.
     *
     * @param UnionType|InterfaceType  $abstractType
     * @param ObjectType|InterfaceType $maybeSubType
     *
     * @api
     */
    public function isSubType(AbstractType $abstractType, ImplementingType $maybeSubType): bool
    {
        if ($abstractType instanceof InterfaceType) {
            return $maybeSubType->implementsInterface($abstractType);
        }

        if ($abstractType instanceof UnionType) {
            return $abstractType->isPossibleType($maybeSubType);
        }

        throw new InvalidArgumentException(sprintf('$abstractType must be of type UnionType|InterfaceType got: %s.', get_class($abstractType)));
    }

    /**
     * Returns instance of directive by name
     *
     * @api
     */
    public function getDirective(string $name): ?Directive
    {
        foreach ($this->getDirectives() as $directive) {
            if ($directive->name === $name) {
                return $directive;
            }
        }

        return null;
    }

    public function getAstNode(): ?SchemaDefinitionNode
    {
        return $this->config->getAstNode();
    }

    /**
     * Throws if the schema is not valid.
     *
     * This operation requires a full schema scan. Do not use in production environment.
     *
     * @throws InvariantViolation
     *
     * @api
     */
    public function assertValid(): void
    {
        $errors = $this->validate();

        if ($errors !== []) {
            throw new InvariantViolation(implode("\n\n", $this->validationErrors));
        }

        $internalTypes = Type::getStandardTypes() + Introspection::getTypes();
        foreach ($this->getTypeMap() as $name => $type) {
            if (isset($internalTypes[$name])) {
                continue;
            }

            $type->assertValid();

            // Make sure type loader returns the same instance as registered in other places of schema
            if ($this->config->typeLoader === null) {
                continue;
            }

            Utils::invariant(
                $this->loadType($name) === $type,
                sprintf(
                    'Type loader returns different instance for %s than field/argument definitions. Make sure you always return the same instance for the same type name.',
                    $name
                )
            );
        }
    }

    /**
     * Validate the schema and return any errors.
     *
     * This operation requires a full schema scan. Do not use in production environment.
     *
     * @return array<int, Error>
     *
     * @api
     */
    public function validate(): array
    {
        // If this Schema has already been validated, return the previous results.
        if (isset($this->validationErrors)) {
            return $this->validationErrors;
        }

        // TODO: Validate types

        /*
         ? Persist the results of validation before returning to ensure validation
         ? does not run multiple times for this schema.
         */
        $this->validationErrors = $context->getErrors();

        return $this->validationErrors;
    }
}

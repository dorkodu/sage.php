<?php

declare(strict_types=1);

namespace Sage\Validator;

use function array_filter;
use function array_merge;
use function count;
use Exception;
use function is_array;
use Sage\Document;
use Sage\Error\Error;
use Sage\Type\Schema;
use Sage\Utils\TypeInfo;
use Sage\Validator\Rules\DisableIntrospection;
use Sage\Validator\Rules\ExecutableDefinitions;
use Sage\Validator\Rules\FieldsOnCorrectType;
use Sage\Validator\Rules\FragmentsOnCompositeTypes;
use Sage\Validator\Rules\KnownArgumentNames;
use Sage\Validator\Rules\KnownArgumentNamesOnDirectives;
use Sage\Validator\Rules\KnownDirectives;
use Sage\Validator\Rules\KnownFragmentNames;
use Sage\Validator\Rules\KnownTypeNames;
use Sage\Validator\Rules\LoneAnonymousOperation;
use Sage\Validator\Rules\LoneSchemaDefinition;
use Sage\Validator\Rules\NoFragmentCycles;
use Sage\Validator\Rules\NoUndefinedVariables;
use Sage\Validator\Rules\NoUnusedFragments;
use Sage\Validator\Rules\NoUnusedVariables;
use Sage\Validator\Rules\OverlappingFieldsCanBeMerged;
use Sage\Validator\Rules\PossibleFragmentSpreads;
use Sage\Validator\Rules\ProvidedRequiredArguments;
use Sage\Validator\Rules\ProvidedRequiredArgumentsOnDirectives;
use Sage\Validator\Rules\QueryComplexity;
use Sage\Validator\Rules\QueryDepth;
use Sage\Validator\Rules\QuerySecurityRule;
use Sage\Validator\Rules\ScalarLeafs;
use Sage\Validator\Rules\SingleFieldSubscription;
use Sage\Validator\Rules\UniqueArgumentNames;
use Sage\Validator\Rules\UniqueDirectivesPerLocation;
use Sage\Validator\Rules\UniqueFragmentNames;
use Sage\Validator\Rules\UniqueInputFieldNames;
use Sage\Validator\Rules\UniqueOperationNames;
use Sage\Validator\Rules\UniqueVariableNames;
use Sage\Validator\Rules\ValidationRule;
use Sage\Validator\Rules\ValuesOfCorrectType;
use Sage\Validator\Rules\VariablesAreInputTypes;
use Sage\Validator\Rules\VariablesInAllowedPosition;
use function sprintf;
use Throwable;

/**
 * Implements the "Validation" section of the spec.
 *
 * Validation runs synchronously, returning an array of encountered errors, or
 * an empty array if no errors were encountered and the document is valid.
 *
 * Each validation rule is defined in Sage\Validator\Rules.
 *
 * Each are expected to return an instance of [Sage\Error\Error](class-reference.md#graphqlerrorerror),
 * or array of such instances when invalid.
 */
class DocumentValidator
{
    /** @var ValidationRule[] */
    private static $rules = [];

    /** @var bool */
    private static $initRules = false;

    /**
     * Primary method for query validation. See class description for details.
     *
     * @param ValidationRule[]|null $rules
     *
     * @return Error[]
     *
     * @api
     */
    public static function validate(
        Schema $schema,
        Document $source
    ): array {
        $rules = static::allRules();

        if (true === is_array($rules) && 0 === count($rules)) {
            // Skip validation if there are no rules
            return [];
        }

        return static::visitUsingRules($schema, $source, $rules);
    }

    /**
     * Returns all global validation rules.
     *
     * @return ValidationRule[]
     *
     * @api
     */
    public static function allRules(): array
    {
        if (!self::$initRules) {
            static::$rules = array_merge(static::defaultRules(), self::$rules);
            static::$initRules = true;
        }

        return self::$rules;
    }

    public static function defaultRules()
    {
        if (null === self::$defaultRules) {
            self::$rules = [];
        }

        return self::$defaultRules;
    }

    /**
     * @return QuerySecurityRule[]
     */
    public static function securityRules(): array
    {
        // This way of defining rules is deprecated
        // When custom security rule is required - it should be just added via DocumentValidator::addRule();
        // TODO: deprecate this

        if (null === self::$securityRules) {
            self::$securityRules = [
        DisableIntrospection::class => new DisableIntrospection(DisableIntrospection::DISABLED), // DEFAULT DISABLED
        QueryDepth::class => new QueryDepth(QueryDepth::DISABLED), // default disabled
        QueryComplexity::class => new QueryComplexity(QueryComplexity::DISABLED), // default disabled
      ];
        }

        return self::$securityRules;
    }

    public static function sdlRules()
    {
        if (null === self::$sdlRules) {
            self::$sdlRules = [
        LoneSchemaDefinition::class => new LoneSchemaDefinition(),
        KnownDirectives::class => new KnownDirectives(),
        KnownArgumentNamesOnDirectives::class => new KnownArgumentNamesOnDirectives(),
        UniqueDirectivesPerLocation::class => new UniqueDirectivesPerLocation(),
        UniqueArgumentNames::class => new UniqueArgumentNames(),
        UniqueInputFieldNames::class => new UniqueInputFieldNames(),
        ProvidedRequiredArgumentsOnDirectives::class => new ProvidedRequiredArgumentsOnDirectives(),
      ];
        }

        return self::$sdlRules;
    }

    /**
     * This uses a specialized visitor which runs multiple visitors in parallel,
     * while maintaining the visitor skip and break API.
     *
     * @param ValidationRule[] $rules
     *
     * @return Error[]
     */
    public static function visitUsingRules(Schema $schema, TypeInfo $typeInfo, DocumentNode $documentNode, array $rules): array
    {
        $context = new ValidationContext($schema, $documentNode, $typeInfo);
        $visitors = [];
        foreach ($rules as $rule) {
            $visitors[] = $rule->getVisitor($context);
        }

        Visitor::visit($documentNode, Visitor::visitWithTypeInfo($typeInfo, Visitor::visitInParallel($visitors)));

        return $context->getErrors();
    }

    /**
     * Returns global validation rule by name. Standard rules are named by class name, so
     * example usage for such rules:.
     *
     * $rule = DocumentValidator::getRule(Sage\Validator\Rules\QueryComplexity::class);
     *
     * @param string $name
     *
     * @api
     */
    public static function getRule($name): ?ValidationRule
    {
        $rules = static::allRules();

        if (isset($rules[$name])) {
            return $rules[$name];
        }

        $name = sprintf('Sage\\Validator\\Rules\\%s', $name);

        return $rules[$name] ?? null;
    }

    /**
     * Add rule to list of global validation rules.
     *
     * @api
     */
    public static function addRule(ValidationRule $rule): void
    {
        self::$rules[$rule->getName()] = $rule;
    }

    public static function isError($value)
    {
        return is_array($value)
      ? count(array_filter(
          $value,
          static function ($item): bool {
              return $item instanceof Throwable;
          }
      )) === count($value)
      : $value instanceof Throwable;
    }

    public static function append(&$arr, $items)
    {
        if (is_array($items)) {
            $arr = array_merge($arr, $items);
        } else {
            $arr[] = $items;
        }

        return $arr;
    }

    /**
     * @param ValidationRule[]|null $rules
     *
     * @return Error[]
     *
     * @throws Exception
     */
    public static function validateSDL(
        DocumentNode $documentAST,
        ?Schema $schemaToExtend = null,
        ?array $rules = null
    ): array {
        $usedRules = $rules ?? self::sdlRules();
        $context = new SDLValidationContext($documentAST, $schemaToExtend);
        $visitors = [];
        foreach ($usedRules as $rule) {
            $visitors[] = $rule->getSDLVisitor($context);
        }

        Visitor::visit($documentAST, Visitor::visitInParallel($visitors));

        return $context->getErrors();
    }

    public static function assertValidSDL(DocumentNode $documentAST): void
    {
        $errors = self::validateSDL($documentAST);
        if (count($errors) > 0) {
            throw new Error(self::combineErrorMessages($errors));
        }
    }

    public static function assertValidSDLExtension(DocumentNode $documentAST, Schema $schema): void
    {
        $errors = self::validateSDL($documentAST, $schema);
        if (count($errors) > 0) {
            throw new Error(self::combineErrorMessages($errors));
        }
    }

    /**
     * @param Error[] $errors
     */
    private static function combineErrorMessages(array $errors): string
    {
        $str = '';
        foreach ($errors as $error) {
            $str .= $error->getMessage() . "\n\n";
        }

        return $str;
    }
}

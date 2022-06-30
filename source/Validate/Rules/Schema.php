<?php

namespace Sage\Validate;

use Sage\Type\Schema;

class SchemaValidator
{
    /**
     * Validates schema.
     *
     * This operation requires full schema scan. Do not use in production environment.
     *
     * @throws InvariantViolation
     *
     * @api
     */
    public function assertValid()
    {
        $errors = $this->validate();

        if ($errors) {
            throw new InvariantViolation(implode("\n\n", $this->validationErrors));
        }

        $internalTypes = Type::standardTypes();
        foreach ($this->getTypeMap() as $name => $type) {
            if (isset($internalTypes[$name])) {
                continue;
            }

            $type->assertValid();

            // * Make sure type loader returns the same instance as registered in other places of schema
            if (!$this->config->typeLoader) {
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
     * Validates schema.
     *
     * ! This operation requires full schema scan. Do not use in production environment.
     *
     * @return InvariantViolation[]|Error[]
     *
     * @api
     */
    public function validate()
    {
        // If this Schema has already been validated, return the previous results.
        if (null !== $this->validationErrors) {
            return $this->validationErrors;
        }

        // Validate the schema, producing a list of errors.
        $context = new SchemaValidationContext($this);
        $context->validateTypes();

        /*
         ? Persist the results of validation before returning to ensure validation
         ? does not run multiple times for this schema.
         */
        $this->validationErrors = $context->getErrors();

        return $this->validationErrors;
    }
}

<?php

declare(strict_types=1);

namespace Sage\Executor;

use Sage\Type\Schema;
use Sage\Document;

/**
 * Represents a reusable execution order. 
 * (a value object that contains the schema, request document, context and options.)
 */
class ExecutionDirective
{
  public Schema $schema;
  public Document $document;
  public $context;
  public array $options;

  public function __construct(
    Schema $schema,
    Document $document,
    $context = null,
    ?array $options = null
  ) {
    $this->schema   = $schema;
    $this->document = $document;
    $this->context  = $context;
    $this->options  = $options;
  }
}

<?php

namespace Sage\Test;

use Exception;

use Blog\DataSource;
use Sage\ContextInfo;
use Dorkodu\Seekr\Test\TestCase;
use Sage\Type\Definition\Entity;
use Sage\Type\Definition\Attribute;
use Sage\Type\Schema;

class Usage extends TestCase
{
    public Schema $schema;

    public function setUp()
    {
        $schema = new Schema([
      'Person' => $Person
    ]);

        //? Attributes
        $name = new Attribute([
      'name' => 'name',
      'description' => 'Name of a User.',
      'resolve' => function ($referenceValue, ContextInfo $info) {
          $id = $referenceValue['userId'];
          $user = DataSource::getUserById($id);
          return $user->email;
      }
    ]);

        $email = new Attribute([
      'name' => 'email',
      'description' => 'Email of a User.',
      'resolve' => function ($referenceValue, ContextInfo $info) {
          $id = $referenceValue['userId'];
          $user = DataSource::getUserById($id);
          return $user->email;
      }
    ]);

        //? Entities
        $User = new Entity([
      'name' => "User",
      'description' => "Represents a user of the app.",
      'attributes' => [
        'name' => $name
      ],
      'acts' => [],
      'links' => [],
    ]);

        $Note = new Entity([
      'name' => 'Note',
      'attributes' => [
        'id' => $id,
        'contents' => $contents,
        'authorId' => $authodId,
        'timestamp' => $timestamp
      ]
    ]);
    }

    public function registerSchema()
    {
    }

    public function testExecuteQuery()
    {
        // Execute a valid Sage query.
    }

    public function testEntityDefinition()
    {
    }
}

<?php

namespace Sage\Test;

use Exception;

use Blog\DataSource;
use Sage\ContextInfo;
use Dorkodu\Seekr\Seekr;
use Dorkodu\Seekr\Test\TestCase;
use Sage\Type\Definition\Entity;
use Sage\Type\Definition\Attribute;

class Usage extends TestCase
{
  public function setUp()
  {
  }

  public function registerSchema()
  {
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
  }

  public function testExecuteQuery()
  {
    // Execute a valid Sage query.
  }

  public function testEntityDefinition()
  {

    // Define an Entity correctly.
    $definition = new Entity([
      'name' => "User",
      'description' => "Represents a user of the app.",
      'attributes' => [
        'name' => $name
      ],
      'acts' => [],
      'links' => [],
    ]);

    $User = new Entity([
      'name' => 'User',
      'attributes' => [
        'name' => new Attribute([
          'name' => "name",
          'type' => Type::string(),
        ]),
        'email' => new Attribute([
          'name' => "email",
          'type' => Type::string()
        ])
      ]
    ]);
  }
}

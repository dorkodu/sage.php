<?php

use PinkFloyd\DataSource;
use Sage\ContextInfo;
use Sage\Type\Schema;
use Sage\Type\Definition\Type;
use Sage\Type\Definition\Entity;
use Sage\Type\Definition\Attribute;
use Sage\Type\Definition\Link;

//? Define Types Below
$schema = new Schema([
  'types' => [
    $Album, $Song, $Member, $Band, $Review
  ]
]);

//? Entities
$Album = new Entity([
  'name' => 'Album',
  'description' => 'Represents a user of the app.',
  'attributes' => [
    'name' => new Attribute([
      'name'        => 'name',
      'description' => 'Name of a User.',
      'rule'        => function(){}, // Type::nonNull(Type::string())
      'resolve'     => function ($referenceValue) {
          $id = $referenceValue['user.id'];
          $user = DataSource::getUserById($id);
          return $user->email;
      },
    ]),
    'email' => new Attribute([
      'name'        => 'email',
      'description' => 'Email of a User.',
      'rule'      => function(){},
      'resolve'     => function ($referenceValue) {
          $id = $referenceValue['user.id'];
          $user = DataSource::getUserById($id);
          return $user->email;
      },
    ]),
  ],
  'acts' => [
    'create' => new Act([
      'name' => 'addNote',
      'description' => 'Saves a note to the data source.',
      'do' => function ($referenceValue) {
        $id = $referenceValue['user.id'];
        DataSource::savePost($id);
      }
    ]),
    'delete' => function() {},
    'update' => function() {},
  ],
  'links' => [
    'notes' => new Link([
      
    ]) 
  ],
  'resolve' => function ($query, $context) {
      $id = $query->argument('id');

      return [
        'id' => $id,
        'dataSource' => $context['dataSource'],
      ];
  },
]);

$Note = new Entity([
  'name'       => 'Note',
  'attributes' => [
    'id' => $id,
    'contents' => $contents,
    'authorId' => $authodId,
    'timestamp' => $timestamp,
  ],
]);

//? Attributes

$name = ;

$email = ;

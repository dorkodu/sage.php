<?php

use Blog\DataSource;
use Sage\ContextInfo;
use Sage\Type\Schema;
use Sage\Type\Definition\Type;
use Sage\Type\Definition\Entity;
use Sage\Type\Definition\Attribute;

//? Define Types Below
$schema = new Schema([
  'User' => $User,
]);

//? Entities

//TODO: implement new acts for User: create, delete, update

$User = new Entity([
  'name' => 'User',
  'description' => 'Represents a user of the app.',
  'attributes' => [
    'name' => $name,
    'email' => $email,
  ],
  'acts' => [
    'create' => $act_create,
    'delete' => $act_delete,
    'update' => $act_update,
  ],
  'links' => [],
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

$name = new Attribute([
  'name'        => 'name',
  'description' => 'Name of a User.',
  'type'        => Type::string(),
  'resolve'     => function ($referenceValue, ContextInfo $info) {
      $id = $referenceValue['user.id'];
      $user = DataSource::getUserById($id);

      return $user->email;
  },
]);

$email = new Attribute([
  'name'        => 'email',
  'description' => 'Email of a User.',
  'resolve'     => function ($referenceValue, ContextInfo $info) {
      $id = $referenceValue['userId'];
      $user = DataSource::getUserById($id);

      return $user->email;
  },
]);

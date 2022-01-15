<?php

// TODO: define schema

/*
   * 
   */

use Sage\ContextInfo;
use Sage\Type\Schema;
use Sage\Type\Definition\Entity;
use Sage\Type\Definition\Attribute;
use Blog\DataSource;

$schema = new Schema([
  'entities' => []
]);

//? Define Types Below

//? Entities

// TODO: implement new acts: addUser, deleteUser, updateUser
$User = new Entity([
  'name' => "User",
  'description' => "Represents a user of the app.",
  'attributes' => [
    'name' => $name,
    'email' => $email
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

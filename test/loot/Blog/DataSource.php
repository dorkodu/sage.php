<?php

namespace Blog;

use Blog\Post;
use Blog\User;

class DataSource
{
  private static $data = [
    'users' => [
      1 => new User(1, "Doruk Eray", "doruk@dorkodu.com"),
      2 => new User(2, "Berk Cambaz", "berk@dorkodu.com")
    ],
    'posts' => [
      1 => new Post(1, "First Post of Our Blog", "Hello World!", 1, 1600000000),
      2 => new Post(2, "Introducing Lucid", "Lucid is the awesome and lightweight component-based library for JavaScript.", 1, 1615000000),
      3 => new Post(1, "Introducing Sage", "Sage is a query-based data exchange protocol.", 1, 1629500000),
      4 => new Post(1, "Sage.php Is Released!", "Sage.php the reference server implementation of the Sage protocol.", 1, 1629792730),
    ]
  ];

  public static function getUserById(int $id)
  {
    return self::$data['users'][$id];
  }

  public static function getPostById(int $id)
  {
    return self::$data['posts'][$id];
  }

  private static function generateId($collection)
  {
    return count($collection) + 1;
  }

  public static function addPost(string $title, string $contents, int $authorId, int $timestamp)
  {
    $id = self::generateId(self::$data['posts']);

    $post = new Post($id, $title, $contents, $authorId, $timestamp);

    self::$data['posts'][$id] = $post;
  }

  public static function addUser(string $name, string $email)
  {
    $id = self::generateId(self::$data['users']);

    $user = new User($id, $name, $email);

    self::$data['users'][$id] = $user;
  }
}

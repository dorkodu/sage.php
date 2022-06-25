<?php

namespace Blog;

class DataSource
{
    private static $data = [
    'users' => [
      1 => new User(1, 'Doruk Eray', 'doruk@dorkodu.com'),
      2 => new User(2, 'Berk Cambaz', 'berk@dorkodu.com'),
    ],
    'notes' => [
      1 => new Note(1, 'Hello World!', 1, 1600000000),
      2 => new Note(2, 'Lucid is an awesome library collection for JavaScript.', 1, 1615000000),
      3 => new Note(1, 'Sage is a query-based data exchange protocol.', 1, 1629500000),
      4 => new Note(1, 'Sage.php Is Released!', 1, 1629792730),
    ],
  ];

    public static function getUserById(int $id)
    {
        return self::$data['users'][$id];
    }

    public static function getNoteById(int $id)
    {
        return self::$data['notes'][$id];
    }

    private static function generateId($collection)
    {
        return count($collection) + 1;
    }

    public static function addNote(string $title, string $contents, int $authorId, int $timestamp)
    {
        $id = self::generateId(self::$data['notes']);
        $note = new Note($id, $title, $contents, $authorId, $timestamp);

        self::$data['notes'][$id] = $note;
    }

    public static function addUser(string $name, string $email)
    {
        $id = self::generateId(self::$data['users']);
        $user = new User($id, $name, $email);

        self::$data['users'][$id] = $user;
    }
}

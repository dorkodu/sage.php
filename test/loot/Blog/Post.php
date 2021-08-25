<?php

namespace Blog;

class Post
{
  /** @var int */
  public $id;

  /** @var string */
  public $title;

  /** @var string */
  public $contents;

  /** @var int */
  public $authorId;

  /** @var int */
  public $timestamp;

  public function __construct(string $id, string $title, string $contents, int $authorId, int $timestamp)
  {
    $this->id = $id;
    $this->title = $title;
    $this->contents = $contents;
    $this->authorId = $authorId;
    $this->title = $title;
  }
}

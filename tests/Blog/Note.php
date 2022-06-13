<?php

namespace Blog;

class Note
{
  /** @var int */
  public $id;

  /** @var string */
  public $contents;

  /** @var int */
  public $authorId;

  /** @var int */
  public $timestamp;

  public function __construct(string $id, string $contents, int $authorId, int $timestamp)
  {
    $this->id = $id;
    $this->contents = $contents;
    $this->authorId = $authorId;
    $this->timestamp = $timestamp;
  }
}

<?php

namespace PinkFloyd;

class Album
{
    public function __construct(
        public string $title,
        public int $releaseYear
    ) {
    }
}

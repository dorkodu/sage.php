<?php

namespace PinkFloyd;

class Song
{
    public function __construct(
        public string $title,
        public Album $album,
        public array $songwriters,
        public int $duration
    ) {
    }
}

<?php

namespace PinkFloyd;

class Review
{
    public function __construct(
        public int $rating,
        public string $comment,
        public int $timestamp
    ) {
    }
}

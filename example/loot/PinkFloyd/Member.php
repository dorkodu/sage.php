<?php

namespace PinkFloyd;

class Member
{
    public function __construct(
        public string $name,
        public string $about
    ) {
    }
}

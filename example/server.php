<?php

// for a simpler autoloading using Loom.
require '__loom.php';

// ? define schema in another file, just import to use
require 'schema.php';

use Exception;
use Sage\Sage;
use Sage\Type\Schema;

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

try {
    $result = Sage::execute(
        $schema,
        $document,
        [
          'author' => 'doruk eray',
        ],
        [
          'onError' => function () {
              # code
          }
        ]
    );

    $output = $result->toArray();
} catch (Exception $e) {
    $output = [
      'errors' => [
        [
          'message' => $e->getMessage(),
        ],
      ],
    ];
}

header('Content-Type: application/json');
echo json_encode($output);

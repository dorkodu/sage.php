<?php

// for a simpler autoloading using Loom.
require '__loom.php';

require 'schema.php';
require 'data.php';

use Exception;
use Sage\Sage;

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

try {
    $result = Sage::execute(
      $schema,
      $document,
      [
        'magic' => '13',
      ],
      []
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

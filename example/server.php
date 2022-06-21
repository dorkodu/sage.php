<?php

require "schema.php";
require "data.php";

use Sage\Sage;
use Sage\Type\Schema;
use Blog\DataSource;
use \Exception;

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

try {
  $options = [];
  $context = [
    'magic' => "13"
  ];

  $result = Sage::execute($schema, $document, $context, $options);

  $output = $result->toArray();
} catch (Exception $e) {
  $output = [
    'errors' => [
      [
        'message' => $e->getMessage()
      ]
    ]
  ];
}

header('Content-Type: application/json');
echo json_encode($output);

<?php

require_once 'loom/Psr4Autoloader.php';

$universalNamespaces = [
  'Sage'      => './../../source',
  'Sage\Test' => './',
  'Dorkodu'   => 'loot/Dorkodu',
  'PinkFloyd' => 'loot/PinkFloyd',
];

$psr4Autoloader = new Psr4Autoloader();
$psr4Autoloader->register();

foreach ($universalNamespaces as $namespace => $path) {
    $psr4Autoloader->addNamespace($namespace, $path);
}

<?php

$vendorDir = dirname(__DIR__);
$rootDir = dirname(dirname(__DIR__));

return array (
  'needletail/needletail-craft3' => 
  array (
    'class' => 'needletail\\needletail\\Needletail',
    'basePath' => '/Users/esdert/Development/Valet/needletail-craft3/src',
    'handle' => 'needletail',
    'aliases' => 
    array (
      '@needletail/needletail' => $rootDir . '/src',
    ),
    'name' => 'Needletail',
    'version' => '0.6.1',
    'description' => 'Needletail Search and Index package for Craft 3.x',
    'developer' => 'Needletail',
    'developerUrl' => 'https://needletail.io',
    'changelogUrl' => 'https://raw.githubusercontent.com/needletail-io/needletail-craft3/master/CHANGELOG.md',
    'hasCpSettings' => true,
    'hasCpSection' => true,
  ),
);

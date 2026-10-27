<?php
use Drupal\Core\Runtime\DrupalRuntime;
$_ENV['APP_RUNTIME'] ??= $_SERVER['APP_RUNTIME'] ?? DrupalRuntime::class;
return require __DIR__ . '/vendor/autoload_runtime.php';

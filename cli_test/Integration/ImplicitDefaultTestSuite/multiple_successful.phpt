--TEST--
Ensure multiple tests has appropriate format
--FILE--
<?php

$root = dirname(__DIR__, 3);
$command = "cd $root && php $root/bin/asyncunit run acme_src/ImplicitDefaultTestSuite/MultipleTest";

passthru($command, $statusCode);
echo PHP_EOL, 'Status: ', $statusCode, PHP_EOL;
?>
--EXPECTF--
AsyncUnit v%s - %s

Runtime: PHP 8.0.%d

%s.%s.%s.%s

Time: %d:%f, Memory: %d.%d MB

OK!
Tests: 3, Assertions: 3, Async Assertions: 0

Status: 0
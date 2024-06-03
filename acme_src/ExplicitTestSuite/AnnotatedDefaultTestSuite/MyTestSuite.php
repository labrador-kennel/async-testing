<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\AnnotatedDefaultTestSuite;

use Labrador\AsyncUnit\Framework\Attribute\DefaultTestSuite;
use Labrador\AsyncUnit\Framework\TestSuite;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {}
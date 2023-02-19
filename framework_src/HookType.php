<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Cspray\Yape\Enum;
use Cspray\Yape\EnumTrait;

enum HookType : string {
    case BeforeAll = 'BeforeAll';
    case BeforeEach = 'BeforeEach';
    case AfterEach = 'AfterEach';
    case AfterAll = 'AfterAll';
    case BeforeEachTest = 'BeforeEachTest';
    case AfterEachTest = 'AfterEachTest';
}
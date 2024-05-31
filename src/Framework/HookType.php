<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework;

enum HookType : string {
    case BeforeAll = 'BeforeAll';
    case BeforeEach = 'BeforeEach';
    case AfterEach = 'AfterEach';
    case AfterAll = 'AfterAll';
    case BeforeEachTest = 'BeforeEachTest';
    case AfterEachTest = 'AfterEachTest';
}
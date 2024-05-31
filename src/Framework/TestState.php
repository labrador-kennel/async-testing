<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework;

enum TestState : string {
    case Passed = 'Passed';
    case Failed = 'Failed';
    case Disabled = 'Disabled';
    case Errored = 'Errored';
}
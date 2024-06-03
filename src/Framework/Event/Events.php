<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Event;

interface Events {

    const PROCESSING_STARTED = 'labrador.asyncUnit.processingStarted';

    const TEST_SUITE_STARTED = 'labrador.asyncUnit.testSuiteStarted';

    const TEST_CASE_STARTED = 'labrador.asyncUnit.testCaseStarted';

    const TEST_PROCESSED = 'labrador.asyncUnit.testProcessed';

    const TEST_PASSED = 'labrador.asyncUnit.testPassed';

    const TEST_FAILED = 'labrador.asyncUnit.testFailed';

    const TEST_DISABLED = 'labrador.asyncUnit.testDisabled';

    const TEST_ERRORED = 'labrador.asyncUnit.testErrored';

    const TEST_CASE_FINISHED = 'labrador.asyncUnit.testCaseFinished';

    const TEST_SUITE_FINISHED = 'labrador.asyncUnit.testSuiteFinished';

    const PROCESSING_FINISHED = 'labrador.asyncUnit.processingFinished';

}
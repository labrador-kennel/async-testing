<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli;

use Amp\ByteStream\WritableStream;
use Amp\Future;
use Closure;
use Cspray\Labrador\AsyncUnit\AsyncUnitApplication;
use Cspray\Labrador\AsyncUnit\Event\TestDisabledEvent;
use Cspray\Labrador\AsyncUnit\Event\TestErroredEvent;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Event\ProcessingFinishedEvent;
use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;
use Cspray\Labrador\AsyncUnit\ResultPrinterPlugin;
use Cspray\Labrador\StyledByteStream\TerminalOutputStream;
use Generator;
use Labrador\AsyncEvent\AbstractListener;
use Labrador\AsyncEvent\Event;
use Labrador\AsyncEvent\EventEmitter;
use Labrador\AsyncEvent\Listener;
use Labrador\AsyncEvent\OneTimeListener;
use Labrador\CompositeFuture\CompositeFuture;
use SebastianBergmann\Timer\ResourceUsageFormatter;

final class TerminalResultPrinter implements ResultPrinterPlugin {

    /**
     * @var TestFailedEvent[]
     */
    private array $failedTests = [];

    /**
     * @var TestDisabledEvent[]
     */
    private array $disabledTests = [];

    /**
     * @var TestErroredEvent[]
     */
    private array $erroredTests = [];

    private function createClosureInvokingListener(string $event, WritableStream $output, Closure $closure) : Listener {
        return new class($event, $output, $closure) extends AbstractListener {
            public function __construct(
                private readonly string $event,
                private readonly WritableStream $output,
                private readonly Closure $closure
            ) {}

            public function canHandle(string $eventName) : bool {
                return $this->event === $eventName;
            }

            public function handle(Event $event) : Future|CompositeFuture|null {
                ($this->closure)($this->output);
                return null;
            }
        };
    }

    public function registerEvents(EventEmitter $emitter, WritableStream $output) : void {
        $output = new TerminalOutputStream($output);
        $emitter->register(new OneTimeListener($this->createClosureInvokingListener(
            Events::PROCESSING_STARTED,
            $output,
            fn() => $this->testProcessingStarted($output)
        )));
        $emitter->register($this->createClosureInvokingListener(
            Events::TEST_PASSED,
            $output,
            fn() => $this->testPassed($output)
        ));
        $emitter->register($this->createClosureInvokingListener(
            Events::TEST_FAILED,
            $output,
            fn(TestFailedEvent $event) => $this->testFailed($event, $output)
        ));
        $emitter->register($this->createClosureInvokingListener(
            Events::TEST_DISABLED,
            $output,
            fn(TestDisabledEvent $event) => $this->testDisabled($event, $output)
        ));
        $emitter->register($this->createClosureInvokingListener(
            Events::TEST_ERRORED,
            $output,
            fn(TestErroredEvent $event) => $this->testErrored($event, $output)
        ));
        $emitter->register(new OneTimeListener($this->createClosureInvokingListener(
            Events::PROCESSING_FINISHED,
            $output,
            fn(ProcessingFinishedEvent $event) => $this->testProcessingFinished($event, $output)
        )));
    }

    private function testProcessingStarted(WritableStream $output) : void {
        $inspirationalMessages = [
            'Let\'s run some asynchronous tests!',
            'Zoom, zoom... here we go!',
            'One Loop to rule them all.',
            'Alright, waking the hamsters up!',
        ];
        $inspirationalMessage = $inspirationalMessages[array_rand($inspirationalMessages)];
        $output->write(sprintf("AsyncUnit v%s - %s\n", AsyncUnitApplication::VERSION, $inspirationalMessage));
        $output->write(sprintf("Runtime: PHP %s\n", phpversion()));
    }

    private function testPassed(WritableStream $output) : void {
        $output->write('.');
    }

    private function testDisabled(TestDisabledEvent $disabledEvent, WritableStream $output) : void {
        $this->disabledTests[] = $disabledEvent;
        $output->write('D');
    }

    private function testFailed(TestFailedEvent $failedEvent, WritableStream $output) : void {
        $this->failedTests[] = $failedEvent;
        $output->write('X');
    }

    private function testErrored(TestErroredEvent $erroredEvent, WritableStream $output) : void {
        $this->erroredTests[] = $erroredEvent;
        $output->write('E');
    }

    private function testProcessingFinished(ProcessingFinishedEvent $event, TerminalOutputStream $output) : void {
        $output->br(2);
        $output->writeln((new ResourceUsageFormatter())->resourceUsage($event->getTarget()->getDuration()));
        $output->br();
        if ($event->getTarget()->getErroredTestCount() > 0) {
            $output->writeln(sprintf('There was %d error:', $event->getTarget()->getErroredTestCount()));
            $output->br();
            foreach ($this->erroredTests as $index => $erroredTestEvent) {
                $output->writeln(sprintf(
                    '%d) %s::%s',
                    $index + 1,
                    $erroredTestEvent->getTarget()->getTestCase()::class,
                    $erroredTestEvent->getTarget()->getTestMethod()
                ));
                $output->writeln($erroredTestEvent->getTarget()->getException()->getMessage());
                $output->br();
                $output->writeln($erroredTestEvent->getTarget()->getException()->getTraceAsString());
            }
            $output->br();
            $output->writeln('ERRORS');
            $output->writeln(sprintf(
                'Tests: %d, Errors: %d, Assertions: %d, Async Assertions: %d',
                $event->getTarget()->getTotalTestCount(),
                $event->getTarget()->getErroredTestCount(),
                $event->getTarget()->getAssertionCount(),
                $event->getTarget()->getAsyncAssertionCount()
            ));
        }

        if ($event->getTarget()->getFailedTestCount() > 0) {
            $output->writeln(sprintf("There was %d failure:\n", $event->getTarget()->getFailedTestCount()));
            foreach ($this->failedTests as $index => $failedTestEvent) {
                $output->writeln(sprintf(
                    "%d) %s::%s",
                    $index + 1,
                    $failedTestEvent->getTarget()->getTestCase()::class,
                    $failedTestEvent->getTarget()->getTestMethod()
                ));
                $exception = $failedTestEvent->getTarget()->getException();
                if ($exception instanceof AssertionFailedException) {
                    $output->writeln($exception->getDetailedMessage());
                    $output->br();
                    $output->writeln(sprintf(
                        "%s:%d",
                        $exception->getAssertionFailureFile(),
                        $exception->getAssertionFailureLine()
                    ));
                    $output->br();
                } else if ($exception instanceof TestFailedException) {
                    $output->br();
                    $output->writeln("Test failure message:");
                    $output->br();
                    $output->writeln($exception->getMessage());
                    $output->br();
                    $output->writeln($exception->getTraceAsString());
                    $output->br();
                } else {
                    $output->writeln(sprintf(
                        "An unexpected %s was thrown in %s on line %d.",
                        $exception::class,
                        $exception->getFile(),
                        $exception->getLine()
                    ));
                    $output->br();
                    $output->writeln(sprintf("\"%s\"", $exception->getMessage()));
                    $output->br();
                    $output->writeln($exception->getTraceAsString());
                    $output->br();
                }
            }

            $output->write("FAILURES\n");
            $output->write(sprintf(
                "Tests: %d, Failures: %d, Assertions: %d, Async Assertions: %d\n",
                $event->getTarget()->getTotalTestCount(),
                $event->getTarget()->getFailedTestCount(),
                $event->getTarget()->getAssertionCount(),
                $event->getTarget()->getAsyncAssertionCount()
            ));
        }

        if ($event->getTarget()->getDisabledTestCount() > 0) {
            $output->write(sprintf("There was %d disabled test:\n", $event->getTarget()->getDisabledTestCount()));
            $output->write("\n");
            foreach ($this->disabledTests as $index => $disabledEvent) {
                $output->write(sprintf(
                    "%d) %s::%s\n",
                    $index + 1,
                    $disabledEvent->getTarget()->getTestCase()::class,
                    $disabledEvent->getTarget()->getTestMethod()
                ));
            }
            $output->write("\n");
            $output->write(sprintf(
                "Tests: %d, Disabled Tests: %d, Assertions: %d, Async Assertions: %d\n",
                $event->getTarget()->getTotalTestCount(),
                $event->getTarget()->getDisabledTestCount(),
                $event->getTarget()->getAssertionCount(),
                $event->getTarget()->getAsyncAssertionCount()
            ));
        }

        if ($event->getTarget()->getTotalTestCount() === $event->getTarget()->getPassedTestCount()) {
            $output->write("OK!\n");
            $output->write(sprintf(
                "Tests: %d, Assertions: %d, Async Assertions: %d\n",
                $event->getTarget()->getTotalTestCount(),
                $event->getTarget()->getAssertionCount(),
                $event->getTarget()->getAsyncAssertionCount()
            ));
        }
    }
}
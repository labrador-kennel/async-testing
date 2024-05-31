<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Cli;

use Amp\ByteStream\WritableStream;
use Amp\Future;
use Closure;
use Cspray\Labrador\StyledByteStream\TerminalOutputStream;
use Labrador\AsyncEvent\Emitter;
use Labrador\AsyncEvent\Event;
use Labrador\AsyncEvent\Listener;
use Labrador\AsyncUnit\Framework\AsyncUnitApplication;
use Labrador\AsyncUnit\Framework\Event\Events;
use Labrador\AsyncUnit\Framework\Event\ProcessingFinishedEvent;
use Labrador\AsyncUnit\Framework\Event\TestDisabledEvent;
use Labrador\AsyncUnit\Framework\Event\TestErroredEvent;
use Labrador\AsyncUnit\Framework\Event\TestFailedEvent;
use Labrador\AsyncUnit\Framework\Exception\AssertionFailedException;
use Labrador\AsyncUnit\Framework\Exception\TestFailedException;
use Labrador\AsyncUnit\Framework\Plugin\ResultPrinterPlugin;
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

    private function createClosureInvokingListener(WritableStream $output, Closure $closure) : Listener {
        return new class($output, $closure) implements Listener{
            public function __construct(
                private readonly WritableStream $output,
                private readonly Closure $closure
            ) {}

            public function handle(Event $event) : Future|CompositeFuture|null {
                ($this->closure)($event, $this->output);
                return null;
            }
        };
    }

    public function registerEvents(Emitter $emitter, WritableStream $output) : void {
        $output = new TerminalOutputStream($output);
        $emitter->register(
            Events::PROCESSING_STARTED,
            $this->createClosureInvokingListener($output, $this->testProcessingStarted(...))
        );
        $emitter->register(
            Events::TEST_PASSED,
            $this->createClosureInvokingListener($output, $this->testPassed(...))
        );
        $emitter->register(
            Events::TEST_FAILED,
            $this->createClosureInvokingListener($output, $this->testFailed(...))
        );
        $emitter->register(
            Events::TEST_DISABLED,
            $this->createClosureInvokingListener($output, $this->testDisabled(...))
        );
        $emitter->register(
            Events::TEST_ERRORED,
            $this->createClosureInvokingListener($output, $this->testErrored(...))
        );
        $emitter->register(
            Events::PROCESSING_FINISHED,
            $this->createClosureInvokingListener($output, $this->testProcessingFinished(...))
        );
    }

    private function testProcessingStarted(Event $_, WritableStream $output) : void {
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

    private function testPassed(Event $event, WritableStream $output) : void {
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
        $output->writeln((new ResourceUsageFormatter())->resourceUsage($event->payload()->getDuration()));
        $output->br();
        if ($event->payload()->getErroredTestCount() > 0) {
            $output->writeln(sprintf('There was %d error:', $event->payload()->getErroredTestCount()));
            $output->br();
            foreach ($this->erroredTests as $index => $erroredTestEvent) {
                $output->writeln(sprintf(
                    '%d) %s::%s',
                    $index + 1,
                    $erroredTestEvent->payload()->getTestCase()::class,
                    $erroredTestEvent->payload()->getTestMethod()
                ));
                $output->writeln($erroredTestEvent->payload()->getException()->getMessage());
                $output->br();
                $output->writeln($erroredTestEvent->payload()->getException()->getTraceAsString());
            }
            $output->br();
            $output->writeln('ERRORS');
            $output->writeln(sprintf(
                'Tests: %d, Errors: %d, Assertions: %d, Async Assertions: %d',
                $event->payload()->getTotalTestCount(),
                $event->payload()->getErroredTestCount(),
                $event->payload()->getAssertionCount(),
                $event->payload()->getAsyncAssertionCount()
            ));
        }

        if ($event->payload()->getFailedTestCount() > 0) {
            $output->writeln(sprintf("There was %d failure:\n", $event->payload()->getFailedTestCount()));
            foreach ($this->failedTests as $index => $failedTestEvent) {
                $output->writeln(sprintf(
                    "%d) %s::%s",
                    $index + 1,
                    $failedTestEvent->payload()->getTestCase()::class,
                    $failedTestEvent->payload()->getTestMethod()
                ));
                $exception = $failedTestEvent->payload()->getException();
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
                $event->payload()->getTotalTestCount(),
                $event->payload()->getFailedTestCount(),
                $event->payload()->getAssertionCount(),
                $event->payload()->getAsyncAssertionCount()
            ));
        }

        if ($event->payload()->getDisabledTestCount() > 0) {
            $output->write(sprintf("There was %d disabled test:\n", $event->payload()->getDisabledTestCount()));
            $output->write("\n");
            foreach ($this->disabledTests as $index => $disabledEvent) {
                $output->write(sprintf(
                    "%d) %s::%s\n",
                    $index + 1,
                    $disabledEvent->payload()->getTestCase()::class,
                    $disabledEvent->payload()->getTestMethod()
                ));
            }
            $output->write("\n");
            $output->write(sprintf(
                "Tests: %d, Disabled Tests: %d, Assertions: %d, Async Assertions: %d\n",
                $event->payload()->getTotalTestCount(),
                $event->payload()->getDisabledTestCount(),
                $event->payload()->getAssertionCount(),
                $event->payload()->getAsyncAssertionCount()
            ));
        }

        if ($event->payload()->getTotalTestCount() === $event->payload()->getPassedTestCount()) {
            $output->write("OK!\n");
            $output->write(sprintf(
                "Tests: %d, Assertions: %d, Async Assertions: %d\n",
                $event->payload()->getTotalTestCount(),
                $event->payload()->getAssertionCount(),
                $event->payload()->getAsyncAssertionCount()
            ));
        }
    }
}
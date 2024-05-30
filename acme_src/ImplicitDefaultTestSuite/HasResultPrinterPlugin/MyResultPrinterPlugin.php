<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasResultPrinterPlugin;

use Amp\ByteStream\WritableStream;
use Amp\Future;
use Cspray\Labrador\AsyncUnit\ResultPrinterPlugin;
use Cspray\Labrador\AsyncUnit\Events;
use Labrador\AsyncEvent\AbstractListener;
use Labrador\AsyncEvent\Event;
use Labrador\AsyncEvent\EventEmitter;
use Labrador\AsyncEvent\OneTimeListener;
use Labrador\CompositeFuture\CompositeFuture;

class MyResultPrinterPlugin implements ResultPrinterPlugin {

    public function registerEvents(EventEmitter $emitter, WritableStream $output) : void {
        $listener = new class($output) extends AbstractListener {
            public function __construct(
                private readonly WritableStream $output
            ) {}

            public function canHandle(string $eventName) : bool {
                return $eventName === Events::TEST_PROCESSED;
            }

            public function handle(Event $event) : Future|CompositeFuture|null {
                $this->output->write($event->getTarget()->getTestCase()::class . "\n");
                $this->output->write($event->getTarget()->getTestMethod() . "\n");
            }
        };
        $emitter->register(new OneTimeListener($listener));
    }

}
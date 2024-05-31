<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasResultPrinterPlugin;

use Amp\ByteStream\WritableStream;
use Amp\Future;
use Labrador\AsyncEvent\Emitter;
use Labrador\AsyncEvent\Event;
use Labrador\AsyncEvent\Listener;
use Labrador\AsyncUnit\Framework\Event\Events;
use Labrador\AsyncUnit\Framework\Plugin\ResultPrinterPlugin;
use Labrador\CompositeFuture\CompositeFuture;

class MyResultPrinterPlugin implements ResultPrinterPlugin {

    public function registerEvents(Emitter $emitter, WritableStream $output) : void {
        $listener = new class($output) implements Listener {
            public function __construct(
                private readonly WritableStream $output
            ) {}

            public function handle(Event $event) : Future|CompositeFuture|null {
                $this->output->write($event->payload()->getTestCase()::class . "\n");
                $this->output->write($event->payload()->getTestMethod() . "\n");
            }
        };
        $emitter->register(Events::TEST_PROCESSED, $listener);
    }

}
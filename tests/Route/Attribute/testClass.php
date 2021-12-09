<?php

declare(strict_types=1);

namespace Route\Attribute;


use Astaroth\Attribute\ClassAttribute\Conversation;
use Astaroth\Attribute\ClassAttribute\Event\MessageEvent;
use Astaroth\Attribute\ClassAttribute\Event\MessageNew as TestEvent;
use Astaroth\Attribute\General\Description;
use Astaroth\Attribute\Method\Debug;
use Astaroth\Attribute\Method\Message;
use Astaroth\Commands\BaseCommands;
use Astaroth\DataFetcher\Events\MessageNew;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertIsArray;
use function PHPUnit\Framework\assertIsObject;

#[Conversation]
#[TestEvent]
class testClass extends BaseCommands
{
    public function __construct(MessageNew|MessageEvent $dataConstruct = null)
    {
        parent::__construct($dataConstruct);
        assertIsObject($this->data);
    }

    #[Message("test")]
    #[Description("method to be implicitly executed")]
    public function emptyMethod(MessageNew|MessageEvent $dataEmptyMethod, Description $description): bool
    {
        assertEquals("method to be implicitly executed", $description->getResult());
        assertEquals("test", $dataEmptyMethod->getText());

        return true;
    }

    #[Debug]
    #[Description("desc2")]
    public function emptyMethod2(Debug $debug, Description $description): bool
    {
        assertIsArray($debug->getResult());
        assertEquals("desc2", $description->getResult());

        $stack = [];
        foreach (\range(0, 10000) as $ignored) {
            $stack[] = \uniqid('', true);
        }

        assertEquals(10001, \count($stack));

        return true;
    }
}
<?php

declare(strict_types=1);

namespace Astaroth\Route\Attribute;

use Astaroth\Attribute\Action;
use Astaroth\Attribute\Attachment;
use Astaroth\Attribute\ClientInfo;
use Astaroth\Attribute\Conversation;
use Astaroth\Attribute\Message;
use Astaroth\Attribute\MessageRegex;
use Astaroth\Attribute\Payload;
use Astaroth\Attribute\State;
use Astaroth\Contracts\AttributeValidatorInterface;
use Astaroth\DataFetcher\DataFetcher;
use Astaroth\DataFetcher\Events\MessageEvent;
use Astaroth\DataFetcher\Events\MessageNew;
use Astaroth\Parser\DataTransferObject\ClassInfo;
use Astaroth\Parser\DataTransferObject\MethodsInfo;

class EventAttributeHandler
{
    /**
     * @param ClassInfo[] $instances
     * @param DataFetcher $data
     */
    public function __construct(
        array       $instances,
        DataFetcher $data,
    )
    {
        $this->handle($instances, $data);
    }

    /**
     * AttributeOLD check and routing
     * @param ClassInfo[] $classes
     */
    private function handle(array $classes, DataFetcher $data): void
    {
        foreach ($classes as $class) {
            foreach ($class->getAttribute() as $attribute) {

                /**
                 * If the attribute is a Conversation or State object and the validation data is negative
                 * @see Conversation
                 * @see State
                 */
                if (
                    ($attribute instanceof Conversation || $attribute instanceof State)
                    && !$attribute->setHaystack($data)->validate()
                ) {
                    break;
                }

                /**
                 * If the attribute is a MessageNew object
                 * @see \Astaroth\Attribute\Event\MessageNew
                 */
                if (
                    $attribute instanceof \Astaroth\Attribute\Event\MessageNew &&
                    $attribute->setHaystack($data->getType())->validate()
                ) {
                    $this->messageNew($class->getClassName(), $class->getMethods(), $data->messageNew());
                }

                /**
                 * If the attribute is a MessageEvent object
                 * @see \Astaroth\Attribute\Event\MessageEvent
                 */
                if (
                    $attribute instanceof \Astaroth\Attribute\Event\MessageEvent &&
                    $attribute->setHaystack($data->getType())->validate()
                ) {
                    $this->messageEvent($class->getClassName(), $class->getMethods(), $data->messageEvent());
                }

            }
        }
    }


    /**
     * Checks attributes for an event message_new
     * @param string $instanceName
     * @param MethodsInfo $methods
     * @param MessageNew $data
     * @see \Astaroth\Attribute\Event\MessageNew
     */
    private function messageNew(string $instanceName, MethodsInfo $methods, MessageNew $data): void
    {
        $execute = new MethodExecutor($instanceName, $methods);
        $execute
            ->setValidateData($data)
            ->setAvailableAttribute
            (
                Message::class,
                MessageRegex::class,
                Payload::class,
                Attachment::class,
                ClientInfo::class,
                State::class,
                Action::class
            )
            ->addExtraParameters($data)
            ->launch()
        ;
    }

    /**
     * Checks attributes for an event message_event
     * @param string $instanceName
     * @param MethodsInfo $methods
     * @param MessageEvent $data
     * @see \Astaroth\Attribute\Event\MessageEvent
     */
    private function messageEvent(string $instanceName, MethodsInfo $methods, MessageEvent $data): void
    {
        $execute = new MethodExecutor($instanceName, $methods);
        $execute
            ->setAvailableAttribute(Payload::class, State::class)
            ->setValidateData($data)
            ->addExtraParameters($data)
            ->launch();
    }
}
<?php

declare(strict_types=1);

namespace Astaroth\Route\Attribute;

use Astaroth\Contracts\AttributeClassInterface;
use Astaroth\Contracts\AttributeMethodInterface;
use Astaroth\Contracts\AttributeReturnInterface;
use Astaroth\Contracts\AttributeValidatorInterface;
use Astaroth\DataFetcher\DataFetcher;
use Astaroth\DataFetcher\Events\MessageEvent;
use Astaroth\DataFetcher\Events\MessageNew;
use Astaroth\Enums\Events;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use function count;
use function is_object;
use function trigger_error;
use const E_USER_WARNING;

final class EventAttributeHandler
{
    private readonly null|MessageEvent|MessageNew $data;

    /**
     * @param string[] $classMap
     * @param DataFetcher $data
     */
    public function __construct(
        private readonly array $classMap,
        DataFetcher   $data,
    )
    {
        $this->data = self::fetchData($data);
    }

    /**
     * @return ValidatedObject[]
     * @throws ReflectionException
     */
    public function validate(): array
    {
        $validatedObjects = [];
        foreach ($this->classMap as $class) {
            /** @psalm-suppress ArgumentTypeCoercion */
            $reflectionClass = new ReflectionClass($class);

            //if the validation of the top-level class attributes is false, then we validate another class
            // if the number of validated attributes is not equal to the number of class attributes, then the validation failed

            // cast to array to allow count
            $topLevelValidation = (array)$this->validateAttr($reflectionClass);
            $classAttr = $reflectionClass->getAttributes(AttributeClassInterface::class);
            if (count($topLevelValidation) !== count($classAttr)) {
                continue;
            }

            $methods = $this->validateAttrMethods($reflectionClass);
            if ($methods === []) {
                continue;
            }

            $validatedObjects[] = new ValidatedObject($reflectionClass, $methods);
        }

        return $validatedObjects;

    }


    /**
     * Validating top-level attributes
     * @see https://i.imgur.com/zcylScY.png
     *
     * @param ReflectionClass|ReflectionMethod $reflection
     * @return bool|AttributeReturnInterface
     */
    private function validateAttr
    (
        ReflectionClass|ReflectionMethod $reflection,
    ): bool|AttributeReturnInterface
    {
        $validatedAttr = false;

        foreach ($reflection->getAttributes() as $reflectionAttribute) {
            $attribute = $reflectionAttribute->newInstance();

            if (
                $attribute instanceof AttributeValidatorInterface
                && $attribute->setHaystack($this->data)
            ) {

                $validate = $attribute->validate();
                if ($attribute instanceof AttributeClassInterface) {
                    $validatedAttr = $validate;
                }

                if ($validate === true) {
                    // will return an attribute that will be replaced in reflection
                    if ($attribute instanceof AttributeReturnInterface) {
                        return $attribute;
                    }
                    // only the first attribute of the method is checked
                    if ($attribute instanceof AttributeMethodInterface) {
                        return true;
                    }
                }

            }
        }

        return $validatedAttr;
    }

    /**
     * @throws ReflectionException
     */
    private function validateAttrMethods(
        ReflectionClass $classInfo,
    ): array
    {
        $validatedMethods = [];
        foreach ($classInfo->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($replaceAttr = $this->validateAttr($method)) {
                $md = new ReflectionMethodDecorator($classInfo->getName(), $method->getName());

                if (is_object($replaceAttr)) {
                    $md->addReplaceAttribute($replaceAttr);
                }
                $validatedMethods[] = $md;
            }
        }

        return $validatedMethods;
    }

    public static function fetchData(DataFetcher $data): MessageNew|MessageEvent|null
    {
        if ($data->getType() === Events::MESSAGE_NEW->value) {
            return $data->messageNew();
        }

        if ($data->getType() === Events::MESSAGE_EVENT->value) {
            return $data->messageEvent();
        }

        trigger_error($data->getType() . " not yet implemented, please create issue", E_USER_WARNING);

        return null;
    }
}
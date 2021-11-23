<?php

declare(strict_types=1);

namespace Astaroth\Route\Attribute;

use Astaroth\Attribute\Debug;
use Astaroth\Contracts\AttributeReturnInterface;
use Astaroth\Route\ReturnResultHandler;
use Closure;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use function is_object;
use function is_string;

class Executor
{
    /** @var AdditionalParameter[] */
    private array $replaceableObjects = [];

    /** @var AdditionalParameter[] */
    private array $parameters = [];
    private Closure $callableValidateAttribute;


    /**
     * General event coordinator
     * @param ReflectionClass $reflectionClass
     *
     */
    public function __construct(
        private ReflectionClass $reflectionClass,
    )
    {
    }

    /**
     * @throws ReflectionException
     */
    public function launch(): void
    {
        $invokedClass = $this->instantiateClass($this->reflectionClass, ...$this->getReplaceableObjects());
        foreach ($this->reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($method->getAttributes() as $attribute) {
                //if the validation is successful, proceed to the execution of the method
                if ($this->validateAttribute($attribute)) {
                    //passing attributes to parameters (if their type is explicitly specified in user-method)
                    $this->addReplaceableAttributes($method->getAttributes());

                    $this->initializeParameters($method->getParameters());

                    $parameters = array_merge($this->getParameters(), $this->getReplaceableObjects());

                    $method_return = $this->invoke
                    (
                        $invokedClass,
                        $method,
                        //normalize the parameter list for the method
                        $this->parameterNormalizer($method->getParameters(), $parameters)
                    );

                    /** We process the result returned by the method */
                    new ReturnResultHandler($method_return);

                    $this->clearStack();
                    break;
                }
            }
        }
    }

    /**
     * @param ReflectionParameter[] $parameters
     * @throws ReflectionException
     */
    private function initializeParameters(array $parameters): void
    {
        foreach ($parameters as $parameter) {
            if ($parameter->getType() === ReflectionNamedType::class) {
                isset($this->getReplaceableObjects()[$parameter->getName()]) ?:
                    $this->addParameters($this->instantiateClass($parameter->getName()));
            }
        }
    }

    /**
     * @param ReflectionClass|string $reflectionClassOrClassName
     * @param AdditionalParameter[] $parameters
     * @return object
     * @throws ReflectionException
     */
    private function instantiateClass(ReflectionClass|string $reflectionClassOrClassName, ...$parameters): object
    {
        if (is_string($reflectionClassOrClassName)) {
            $reflectionClassOrClassName = new ReflectionClass($reflectionClassOrClassName);
        }

        $constructor = $reflectionClassOrClassName->getConstructor();
        $var = $this->parameterNormalizer($constructor ? $constructor->getParameters() : [], $parameters);
        return $reflectionClassOrClassName->newInstance(
            ...$var
        );
    }

    /**
     * Adds the necessary parameters to the method that requires it
     * @param ReflectionParameter[] $reflectionParameters
     * @param AdditionalParameter[] $parameters
     * @return array
     * @throws ReflectionException
     */
    private function parameterNormalizer(array $reflectionParameters, array $parameters): array
    {
        $methodParameters = [];
        foreach ($reflectionParameters as $schema) {
            foreach ($parameters as $extraParameter) {
                if ($schema->getType() !== null) {
                    $normalized = false;

                    if ($schema->getType() instanceof ReflectionUnionType) {
                        $normalized = $this->normalizeUnionType($schema->getType()->getTypes(), $extraParameter);
                    }

                    if ($schema->getType() instanceof ReflectionNamedType) {
                        $normalized = $this->normalizeNamedType($schema->getType(), $extraParameter);
                    }

                    if (is_object($normalized)) {
                        $methodParameters[] = $normalized;
                    }

                }
            }
        }

        return $methodParameters;
    }

    /**
     * @param ReflectionNamedType[] $reflectionTypes
     * @param AdditionalParameter $additionalParameter
     * @return ?object
     * @throws ReflectionException
     */
    private function normalizeUnionType(array $reflectionTypes, AdditionalParameter $additionalParameter): ?object
    {
        $parameters = [];
        foreach ($reflectionTypes as $reflectionType) {
            $parameters[] = $this->normalizeNamedType($reflectionType, $additionalParameter);
        }

        return current(array_filter($parameters)) ?: null;
    }

    /**
     * @param ReflectionNamedType $reflectionType
     * @param AdditionalParameter $additionalParameter
     * @return ?object
     * @throws ReflectionException
     */
    private function normalizeNamedType(ReflectionNamedType $reflectionType, AdditionalParameter $additionalParameter): ?object
    {
        $type = $reflectionType->getName();
        if ($reflectionType->getName() === $additionalParameter->getType()) {
            if ($additionalParameter->isNeedCreateInstance() === true) {
                return $this->newInstance($additionalParameter->getType());
            }
            return $additionalParameter->getInstance();
        }

        return null;
    }

    /**
     * We give the opportunity to get data from the attribute if it passed validation
     * @param ReflectionAttribute[] $attributes
     */
    private function addReplaceableAttributes(array $attributes): void
    {
        foreach ($attributes as $attribute) {
            $attribute = $attribute->newInstance();
            if ($attribute instanceof AttributeReturnInterface) {
                $this->replaceObjects($attribute);
            }

            //for debug
            if ($attribute instanceof Debug) {
                $this->replaceObjects($attribute->setHaystack(debug_backtrace()));
            }
        }
    }


    /**
     * @return AdditionalParameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    private function addParameters(object ...$instances): void
    {
        foreach ($instances as $instance) {
            isset($this->getParameters()[$instance::class]) ?:
                $this->parameters[$instance::class] = new AdditionalParameter
                (
                    $instance::class,
                    $instance::class,
                    true,
                    $instance
                );
        }

    }

    /**
     * Add intercepted object from outside
     * @param object ...$instances
     * @return $this
     */
    public function replaceObjects(object ...$instances): static
    {
        foreach ($instances as $instance) {
            isset($this->getReplaceableObjects()[$instance::class]) ?:
                $this->replaceableObjects[$instance::class] = new AdditionalParameter
                (
                    $instance::class,
                    $instance::class,
                    false,
                    $instance
                );
        }

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    private function newInstance(string $class, ...$parameters): object
    {
        return (new ReflectionClass($class))->newInstance(...$parameters);
    }

    /**
     * We call methods from the class on which the correct route is set
     * And add arguments
     * method_exist is not needed since method 100% exists
     * @param object $object
     * @param ReflectionMethod $method
     * @param array $parameters
     * @return mixed
     * @throws ReflectionException
     */
    private function invoke(object $object, ReflectionMethod $method, array $parameters): mixed
    {
        return $method->invoke($object, ...$parameters);
    }

    public function setCallableValidateAttribute(Closure $closure): static
    {
        $this->callableValidateAttribute = $closure;
        return $this;
    }

    public function validateAttribute(ReflectionAttribute $attribute): bool
    {
        return (bool)($this->callableValidateAttribute)($attribute->newInstance());
    }

    private function clearStack(): void
    {
        unset($this->parameters, $this->replaceableObjects);
        $this->parameters = [];
        $this->replaceableObjects = [];
    }

    /**
     * @return AdditionalParameter[]
     */
    public function getReplaceableObjects(): array
    {
        return $this->replaceableObjects;
    }

}
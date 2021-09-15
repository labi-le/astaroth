<?php

declare(strict_types=1);

namespace Astaroth\Route\DataTransferObject;

final class ClassInfo
{
    /**
     * @param string $name
     * @param object[] $attribute
     * @param MethodInfo[] $methods
     * @param object|null $classInstance
     */
    public function __construct
    (
        private string  $name,
        private array   $attribute,
        private array   $methods,
        private ?object $classInstance = null,
    )
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getAttribute(): array
    {
        return $this->attribute;
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return ?object
     */
    public function getClassInstance(): ?object
    {
        return $this->classInstance;
    }


}
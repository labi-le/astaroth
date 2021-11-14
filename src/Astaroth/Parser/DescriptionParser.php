<?php
declare(strict_types=1);

namespace Astaroth\Parser;

use Astaroth\Attribute\Description;
use Astaroth\Parser\DataTransferObject\ClassInfo;

final class DescriptionParser
{
    /**
     * @var ClassInfo[]
     */
    private array $classInfo;

    /**
     * @throws ClassNotFoundException
     */
    public function __construct(private string $className)
    {
        if (class_exists($this->className) === false) {
            throw new ClassNotFoundException("class $this->className not found");
        }
        $this->classInfo = ReflectionParser::setClassMap([$this->className])->parse();
    }

    public function getClassDescription(): ?string
    {
        return current($this->classInfo)->getDescription();
    }

    /**
     * @throws MethodNotFoundException
     */
    public function getMethodDescription(string $methodName): ?string
    {
        if (method_exists($this->className, $methodName) === false) {
            throw new MethodNotFoundException("$methodName not found in class $this->className");
        }

        foreach (current($this->classInfo)->getMethods()->getMethods() as $method) {
            if ($method->getName() === $methodName) {
                return $method->getDescription();
            }
        }
        return null;
    }

    /**
     * @param object[] $attrObject
     * @return string|null
     */
    private function getDescription(array $attrObject): ?string
    {
        foreach ($attrObject as $attribute) {
            if ($attribute instanceof Description) {
                return $attribute->getDescription();
            }
        }
        return null;
    }

    public function getAllMethodsDescription(): string
    {
        return current($this->classInfo)->getMethods()->__toString();
    }
}
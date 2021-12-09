<?php
declare(strict_types=1);

namespace Astaroth\Attribute\General;

use Astaroth\Contracts\AttributeMethodInterface;
use Astaroth\Contracts\AttributeReturnInterface;
use Attribute;
use JetBrains\PhpStorm\Pure;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Description implements AttributeReturnInterface, AttributeMethodInterface
{
    public function __construct(private string $description)
    {

    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Pure] public function getResult(): string
    {
        return $this->getDescription();
    }
}
<?php

declare(strict_types=1);

namespace Attribute\Method;

use Astaroth\Attribute\Method\ClientInfo;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

class ClientInfoTest extends TestCase
{
    private const DATA_DIR = __DIR__ . "/../../data.php";

    public function testSetHaystack(): void
    {
        assertEquals((new ClientInfo)->setHaystack((require self::DATA_DIR))::class, ClientInfo::class);
    }

    public function testValidate(): void
    {
        assertTrue((new ClientInfo([ClientInfo::CALLBACK], keyboard: true, inline_keyboard: false))
            ->setHaystack((require self::DATA_DIR))->validate());
    }
}

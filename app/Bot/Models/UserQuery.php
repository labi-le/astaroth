<?php

declare(strict_types=1);


namespace Manager\Models;


class UserQuery extends QueryBuilder
{
    protected string $store_name = 'users';

    protected function _generateTable(int $id): array
    {
        // TODO: Implement _generateTable() method.
    }
}
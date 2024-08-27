<?php

namespace Modules\Accounting\Models;

class AccountingType
{
    const ASSET = 'asset';

    const LIABILITY = 'liability';

    const Income = 'income';

    const REVENUE = 'revenue';

    const EXPENSE = 'expense';

    public static function getTypes(): array
    {
        return [
            self::ASSET => [
                'name' => 'Assets',
                'nature' => 'debit',
            ],
            self::LIABILITY => [
                'name' => 'Liabilities',
                'nature' => 'credit',
            ],
            self::Income => [
                'name' => 'Income',
                'nature' => 'credit',
            ],
            self::EXPENSE => [
                'name' => 'Expense',
                'nature' => 'debit',
            ],
            self::REVENUE => [
                'name' => 'Revenue',
                'nature' => 'credit',
            ],
        ];
    }

    public static function getAccountingTypes(): array
    {
        return array_map(function ($type) {
            return $type['name'].' - '.
                $type['nature'];
        }, self::getTypes());
    }

    public static function getAccountingNatureByType(string $type): ?string
    {
        return self::getTypes()[$type]['nature'];
    }
}

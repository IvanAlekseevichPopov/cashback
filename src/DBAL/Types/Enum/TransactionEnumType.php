<?php

declare(strict_types = 1);

namespace App\DBAL\Types\Enum;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * TransactionEnumType
 */
class TransactionEnumType extends AbstractEnumType
{
    const BALANCE_OPERATION_CREATE         = 'create';
    const BALANCE_OPERATION_DECREASE       = 'decrease';
    const BALANCE_OPERATION_INCREASE       = 'increase';

    /** @var array */
    protected static $choices = [
        self::BALANCE_OPERATION_CREATE => 'Создание',
        self::BALANCE_OPERATION_DECREASE => 'Списание',
        self::BALANCE_OPERATION_INCREASE => 'Пополнение',
    ];
}

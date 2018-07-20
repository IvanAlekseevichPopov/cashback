<?php

declare(strict_types=1);

namespace App\DBAL\Types\Enum;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * TransactionEnumType.
 */
class TransactionEnumType extends AbstractEnumType
{
    public const CREATE = 'create';
    public const DECREASE = 'decrease';
    public const INCREASE = 'increase';

    /** @var array */
    protected static $choices = [
        self::CREATE => 'Создание',
        self::DECREASE => 'Списание',
        self::INCREASE => 'Пополнение',
    ];
}

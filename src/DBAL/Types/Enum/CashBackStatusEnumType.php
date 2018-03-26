<?php

namespace App\DBAL\Types\Enum;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * CashBackStatusEnumType
 */
class CashBackStatusEnumType extends AbstractEnumType
{
    public const STATUS_NOT_PARTNER = 'NOT_PARTNER';
    public const STATUS_AWAITING_PARTNERSHIP = 'AWAITING_PARTNERSHIP';
    public const STATUS_APPROVED_PARTNERSHIP = 'APPROVED_PARTNERSHIP';
    public const STATUS_REJECTED_PARTNERSHIP = 'REJECTED_PARTNERSHIP';
    public const STATUS_CLOSES_COMPANY = 'CLOSES_COMPANY';

    /** @var array */
    protected static $choices = [
        self::STATUS_NOT_PARTNER => 'Не является партнером',
        self::STATUS_AWAITING_PARTNERSHIP => 'Ожидание ответа на запрос партнерки',
        self::STATUS_APPROVED_PARTNERSHIP => 'Подтвержденная партнерка',
        self::STATUS_REJECTED_PARTNERSHIP => 'Отклонено',
        self::STATUS_CLOSES_COMPANY => 'Закрытая кампания',
    ];
}

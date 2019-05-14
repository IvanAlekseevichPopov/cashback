<?php

declare(strict_types=1);

namespace App\DBAL\Types\Enum;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class CashBackStatusEnumType extends AbstractEnumType
{
    public const NOT_PARTNER = 'not_partner';
    public const AWAITING_PARTNERSHIP = 'awaiting_partnership';
    public const APPROVED_PARTNERSHIP = 'approved_partnership';
    public const REJECTED_PARTNERSHIP = 'rejected_partnership';
    public const CLOSED_COMPANY = 'closed_company';

    /** @var array */
    protected static $choices = [
        self::NOT_PARTNER => 'Не является партнером',
        self::AWAITING_PARTNERSHIP => 'Ожидание ответа на запрос партнерки',
        self::APPROVED_PARTNERSHIP => 'Подтвержденная партнерка',
        self::REJECTED_PARTNERSHIP => 'Отклонено',
        self::CLOSED_COMPANY => 'Закрытая кампания',
    ];
}

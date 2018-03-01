<?php

declare(strict_types = 1);

namespace App\DBAL\Types\Enum;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * TransactionStatusEnumType
 */
class TransactionStatusEnumType extends AbstractEnumType
{
    const STATUS_WAIT            = 'wait';
    const STATUS_APPROVED        = 'approved';
    const STATUS_REJECT          = 'reject';
    const STATUS_WAIT_MODERATION = 'wait_moderation';

    /** @var array */
    protected static $choices = [
        self::STATUS_WAIT => 'В ожидании',
        self::STATUS_APPROVED => 'Выполнена',
        self::STATUS_REJECT => 'Отклонена',
        self::STATUS_WAIT_MODERATION => 'Ожидает модерации',
    ];
}

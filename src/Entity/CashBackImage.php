<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\AbstractImage;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="cash_back_image",
 *     options={
 *         "collate": "utf8mb4_unicode_ci",
 *         "charset": "utf8mb4",
 *         "comment": "Cashback images"
 *     }
 * )
 *
 * @ORM\Entity
 * @ORM\EntityListeners({"App\Listener\EntityListener\CashBackImageListener"})
 */
class CashBackImage extends AbstractImage
{
    /**
     * @return string
     */
    protected function getSubDir(): string
    {
        return 'cashback';
    }
}

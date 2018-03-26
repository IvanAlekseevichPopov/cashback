<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Model\AbstractImage;
use App\Traits\Column\UuidColumn;
use Doctrine\ORM\Mapping as ORM;

/**
 * CashBackImage.
 *
 * @ORM\Table(
 *     name="cash_back_image",
 *     options={
 *         "comment": "Cashback images"
 *     }
 * )
 *
 * @ORM\EntityListeners({"App\Listener\EntityListener\CashbackImageListener"})
 * @ORM\Entity
 */
class CashBackImage extends AbstractImage
{
    use UuidColumn;

    /**
     * @return string
     */
    protected function getSubDir(): string
    {
        return 'cashback';
    }
}

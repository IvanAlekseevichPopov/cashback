<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\AbstractImage;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\EntityListeners({"App\Listener\EntityListener\CashbackImageListener"})
 * @ORM\Entity
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

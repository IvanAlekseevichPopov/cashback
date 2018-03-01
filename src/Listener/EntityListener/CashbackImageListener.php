<?php

declare(strict_types=1);

namespace App\Listener\EntityListener;

use App\Entity\CashBackImage;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\ORM\Mapping\PreRemove;

/**
 * Class CashbackImageListener.
 */
class CashbackImageListener
{
    /**
     * @PostPersist
     *
     * @param CashBackImage $image
     */
    public function postPersistHandler(CashBackImage $image)
    {
        $image->saveFile();
    }

    /**
     * @PreRemove
     *
     * @param CashBackImage $image
     */
    public function preRemoveHandler(CashBackImage $image)
    {
        $image->removeFile();
    }
}

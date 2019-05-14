<?php

declare(strict_types=1);

namespace App\Listener\EntityListener;

use App\Model\AbstractImage;
use App\Service\FileManager;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\ORM\Mapping\PreRemove;

class CashbackImageListener
{
    /**
     * @var FileManager
     */
    private $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * @PostPersist
     *
     * @param AbstractImage $baseImage
     */
    public function postPersistHandler(AbstractImage $baseImage): void
    {
        $this->fileManager->saveFile($baseImage);
    }

    /**
     * @PreRemove
     *
     * @param AbstractImage $baseImage
     */
    public function preRemoveHandler(AbstractImage $baseImage): void
    {
        $this->fileManager->removeFile($baseImage);
    }
}

<?php

declare(strict_types=1);

namespace App\Listener\EntityListener;

use App\Model\AbstractImage;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\ORM\Mapping\PreRemove;

/**
 * Class CashbackImageListener.
 */
class CashbackImageListener
{
    public const PUBLIC_FOLDER = '/public/';

    /** @var string */
    private $projectDir;

    /**
     * ImageListener constructor.
     *
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir.self::PUBLIC_FOLDER;
    }

    /**
     * @PostPersist
     *
     * @param AbstractImage $image
     */
    public function postPersistHandler(AbstractImage $image)
    {
        $image->saveFile($this->projectDir);
    }

    /**
     * @PreRemove
     *
     * @param AbstractImage $image
     */
    public function preRemoveHandler(AbstractImage $image)
    {
        $image->removeFile($this->projectDir);
    }
}

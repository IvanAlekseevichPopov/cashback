<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\AbstractImage;
use function sprintf;
use Symfony\Component\Filesystem\Filesystem;

class FileManager
{
    public const PUBLIC_FOLDER = 'public';

    /** @var string */
    protected $publicDir;

    /**
     * @var Filesystem
     */
    private $fs;

    public function __construct(string $projectDir)
    {
        $this->publicDir = sprintf('%s/%s/', $projectDir, self::PUBLIC_FOLDER);
        $this->fs = new Filesystem();
    }

    public function saveFile(AbstractImage $baseImage): void
    {
        $dir = $this->publicDir.$baseImage->getBasePath();

        if (false === $this->fs->exists($dir)) {
            $this->fs->mkdir($dir);
        }
        if ($baseImage->getFile()) {
            $baseImage->getFile()->move($this->publicDir.$baseImage->getBasePath(), $baseImage->getFileName());
        }
    }

    public function removeFile(AbstractImage $baseImage): void
    {
        $this->fs->remove($this->publicDir.$baseImage->getFilePath());
    }

    public function copy(string $source, string $destination): void
    {
        $this->fs->copy($this->publicDir.$source, $this->publicDir.$destination);
    }
}

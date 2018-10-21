<?php

declare(strict_types=1);

namespace App\Model;

use App\Traits\Column\UuidColumn;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * AbstractImage.
 */
abstract class AbstractImage
{
    use UuidColumn;

    public const BASE_PATH = 'static/images';

    /**
     * @var string
     *
     * @ORM\Column(name="extension", type="string", length=5, nullable=false)
     */
    protected $extension;

    /**
     * @var File
     */
    protected $file;

    public function __toString()
    {
        return $this->getFilePath();
    }

    /**
     * @return null|File
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File $file
     *
     * @throws \Exception
     */
    public function setFile(File $file)
    {
        if (!$file->isReadable()) {
            throw new \Exception('file read error');
        }

        $this->file = $file;
        $extension = $file->getExtension();
        if (empty($extension) || strlen($extension) > 5) {
            $extension = $file->guessExtension();
        }
        if (empty($extension)) {
            $extension = $this->genExtension($file);
        }

        $this->setExtension($extension);
    }

    /**
     * @return string
     */
    final public function getFilePath()
    {
        return sprintf('%s/%s', $this->getBasePath(), $this->getFileName());
    }

    /**
     * @return string|null
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     *
     * @return $this
     */
    public function setExtension(string $extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Puts file to filesystem.
     *
     * @param null|string $projectPath
     */
    final public function saveFile(?string $projectPath = null)
    {
        $fs = new FileSystem();
        $fs->mkdir($this->getBasePath());

        $this->file->move($projectPath.$this->getBasePath(), $this->getFileName());
    }

    /**
     * Removes reference file.
     *
     * @param null|string $projectPath
     */
    final public function removeFile(?string $projectPath = null)
    {
        $fs = new FileSystem();
        $fs->remove($projectPath.$this->getFilePath());
    }

    final protected function getBasePath(): string
    {
        return sprintf('%s/%s', self::BASE_PATH, $this->getSubDir());
    }

    final protected function getFileName(): string
    {
        return sprintf('%s.%s', $this->getId(), $this->getExtension());
    }

    abstract protected function getSubDir(): string;

    protected function genExtension(File $file)
    {
        list(, $type) = explode('/', $file->getMimeType(), 2);
        switch ($type) {
            case 'text/x-Algol68':
                return 'sql';
            default:
                return 'none';
        }
    }
}

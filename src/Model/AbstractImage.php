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
     *
     * @return $this
     */
    public function setFile(File $file)
    {
        if (!$file->isReadable()) {
            throw new \Exception('file read error');
        }

        $this->file = $file;
        $this->setExtension($this->genExtension($file));

        return $this;
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
            case 'pjpeg':
            case 'jpeg':
            case 'jpg':
                return 'jpg';
            case 'gif':
                return 'gif';
            case 'png':
                return 'png';
            case 'webp':
                return 'webp';
            default:
                return '';
        }
    }
}

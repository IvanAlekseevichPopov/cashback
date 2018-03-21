<?php

declare(strict_types=1);

namespace App\Model;

use App\Traits\Column\UuidColumn;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
     * @var UploadedFile
     */
    protected $uploadedFile;

    /**
     * @param UploadedFile $file
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setFile(UploadedFile $file)
    {
        if (!$file->isValid()) {
            throw new \Exception('file upload error');
        }

        $this->uploadedFile = $file;
        $this->setExtension($this->genExtension($file));

        return $this;
    }

    /**
     * @return null|UploadedFile
     */
    public function getFile(): ?UploadedFile
    {
        return $this->uploadedFile;
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
     * Moves tmp file to it place.
     */
    final public function saveFile()
    {
        $fs = new FileSystem();
        $fs->mkdir($this->getBasePath());

        $this->uploadedFile->move($this->getBasePath(), $this->getFileName());
    }

    /**
     * Removes file from filesystem.
     */
    final public function removeFile()
    {
        $fs = new FileSystem();
        $fs->remove($this->getFilePath());
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

    protected function genExtension(UploadedFile $uploadedFile)
    {
        list(, $type) = explode('/', $uploadedFile->getMimeType(), 2);
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

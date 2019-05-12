<?php

declare(strict_types=1);

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;

abstract class AbstractImage
{
    public const BASE_PATH = 'content';
    public const MAX_EXT_LENGTH = 5;

    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $id;

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

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public function __toString()
    {
        return $this->getFilePath();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File $file
     *
     * @throws RuntimeException
     */
    public function setFile(File $file): void
    {
        if (!$file->isReadable()) {
            throw new RuntimeException('File read error');
        }

        $this->file = $file;
        $extension = $file->getExtension();
        if (empty($extension) || strlen($extension) > self::MAX_EXT_LENGTH) {
            $extension = $file->guessExtension();
        }
        if (empty($extension)) {
            $extension = $this->genExtension($file);
        }

        $this->setExtension($extension);
    }

    final public function getFilePath(): string
    {
        return sprintf('%s/%s', $this->getBasePath(), $this->getFileName());
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    final public function getBasePath(): string
    {
        return sprintf('%s/%s', self::BASE_PATH, $this->getSubDir());
    }

    final public function getFileName(): string
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

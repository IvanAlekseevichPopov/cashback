<?php

declare(strict_types=1);

namespace App\Traits\Column;

use Ramsey\Uuid\Uuid;

/**
 * UuidColumn trait.
 */
trait UuidColumn
{
    /**
     * @var Uuid
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @return Uuid
     */
    public function getId(): Uuid
    {
        return $this->id;
    }

    /**
     * @param Uuid $id
     *
     * @return $this
     */
    public function setId(Uuid $id)
    {
        $this->id = $id;

        return $this;
    }
}

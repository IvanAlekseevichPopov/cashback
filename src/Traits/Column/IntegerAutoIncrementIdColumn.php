<?php

declare(strict_types=1);

namespace App\Traits\Column;

/**
 * IntegerAutoIncrementIdColumn trait.
 */
trait IntegerAutoIncrementIdColumn
{
    /**
     * @Doctrine\ORM\Mapping\Column(
     *     name="id",
     *     type="integer",
     *     nullable=false,
     * )
     * @Doctrine\ORM\Mapping\Id
     * @Doctrine\ORM\Mapping\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    protected $id;

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = (int) $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->id;
    }
}

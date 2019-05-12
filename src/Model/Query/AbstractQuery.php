<?php

declare(strict_types=1);

namespace App\Model\Query;

use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractQuery
{
    protected const PER_PAGE = 20;
    protected const FIRST_PAGE = 1;

    /**
     * @var int|null
     *
     * @Assert\GreaterThanOrEqual(1)
     */
    protected $page;

    /**
     * @var int|null
     *
     * @Assert\Range(min="1", max="100")
     */
    protected $perPage;

    public function getPage(): int
    {
        return $this->page ?? static::FIRST_PAGE;
    }

    public function setPage(?int $page): void
    {
        $this->page = $page;
    }

    public function getPerPage(): int
    {
        return $this->perPage ?? static::PER_PAGE;
    }

    public function setPerPage(?int $perPage): void
    {
        $this->perPage = $perPage;
    }

    public function getFirstResult(): int
    {
        return $this->getPerPage() * ($this->getPage() - 1);
    }
}

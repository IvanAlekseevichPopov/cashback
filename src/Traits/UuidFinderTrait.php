<?php

declare(strict_types=1);

namespace App\Traits;

use App\Entity\CashBack;
use Ramsey\Uuid\Uuid;

trait UuidFinderTrait
{
    /**
     * @param string $id
     *
     * @return object|CashBack|null
     */
    public function findByUuid(string $id)
    {
        $entity = null;

        if (Uuid::isValid($id)) {
            $entity = $this->find($id);
        }

        return $entity;
    }
}

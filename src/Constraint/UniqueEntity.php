<?php

declare(strict_types=1);

namespace App\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 */
class UniqueEntity extends Constraint
{
    public $message = 'Entity of class {{class}} with {{field}}={{value}} is already defined';

    public $entityClass;

    public $field = 'id';
}

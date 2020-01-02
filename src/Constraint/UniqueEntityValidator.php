<?php

declare(strict_types=1);

namespace App\Constraint;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEntityValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueEntity) {
            throw new UnexpectedTypeException($constraint, UniqueEntity::class);
        }

        $entity = $this->entityManager->getRepository($constraint->entityClass)->findOneBy([$constraint->field => $value]);
        if (null !== $entity) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{class}}', $constraint->entityClass)
                ->setParameter('{{field}}', $constraint->field)
                ->setParameter('{{value}}', $this->formatValue($value))
                ->addViolation();
        }
    }
}

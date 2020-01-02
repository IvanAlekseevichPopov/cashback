<?php

declare(strict_types=1);

namespace App\Form\Api\Admitad;

use App\Constraint\UniqueEntity;
use App\DBAL\Types\Enum\CashBackStatusEnumType;
use App\Entity\CashBack;
use App\Entity\CashBackCategory;
use Cocur\Slugify\Slugify;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class CashBackFormType extends AbstractType
{
    private const VALID_REGION = 'RU';

    /**
     * @var array
     */
    private $regions = [];

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', NumberType::class, [
                'property_path' => 'externalId',
                'constraints' => [
                    new UniqueEntity(['entityClass' => CashBack::class, 'field' => 'externalId']),
                    new NotBlank(),
                    new Range(['min' => 1]),
                ],
            ])
            ->add('name', TextType::class, [
                'property_path' => 'title',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('rating', NumberType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => 1]),
                ],
            ])
            ->add('avg_money_transfer_time', NumberType::class, [
                'property_path' => 'awaitingTime',
            ])
            ->add('description', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('more_rules', TextType::class, [
            ])
            ->add('connected', TextType::class, [
                'property_path' => 'status',
            ])
            ->add('categories', EntityType::class, [
                'class' => CashBackCategory::class,
                'allow_extra_fields' => true,
                'by_reference' => false,
                'multiple' => true,
                'choice_value' => 'externalId',
            ])
            //Not mapped, only validation
            ->add('regions', TextType::class, [
                'mapped' => false,
            ]);

        $builder->get('connected')->addModelTransformer(new CallbackTransformer(
            static function (?string $status) {
                return CashBackStatusEnumType::APPROVED_PARTNERSHIP === $status;
            },
            static function (?bool $connected) {
                return $connected ? CashBackStatusEnumType::APPROVED_PARTNERSHIP : CashBackStatusEnumType::NOT_PARTNER;
            }
        ));

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'transformRawData']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'setSlug']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'validate']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'data_class' => CashBack::class,
            'csrf_protection' => false,
        ]);
    }

    /**
     * @param FormEvent $event
     */
    public function transformRawData(FormEvent $event): void
    {
        $rawData = $event->getData();
        if (isset($rawData['categories'])) {
            $rawData['categories'] = array_map(
                static function (array $category) {
                    return $category['id'] ?? null;
                },
                $rawData['categories']
            );
        }

        if (isset($rawData['regions'])) {
            $this->regions = $rawData['regions'];
            unset($rawData['regions']);
        }

        $event->setData($rawData);
    }

    /**
     * @param FormEvent $event
     */
    public function setSlug(FormEvent $event): void
    {
        /** @var CashBack $cashback */
        $cashback = $event->getData();

        if (null !== $cashback->getTitle()) {
            $slugify = new Slugify();
            $cashback->setSlug($slugify->slugify($cashback->getTitle()));
        }
    }

    /**
     * @param FormEvent $event
     */
    public function validate(FormEvent $event): void
    {
        if (!in_array(self::VALID_REGION, array_column($this->regions, 'region'), true)) {
            $form = $event->getForm();
            $form->get('regions')->addError(new FormError('Required region is missed: '.self::VALID_REGION));
        }

    }
}

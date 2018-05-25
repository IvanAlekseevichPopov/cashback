<?php

declare(strict_types=1);

namespace App\Admin;

use App\DBAL\Types\Enum\CashBackStatusEnumType;
use App\Entity\CashBackImage;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Form\Type\DatePickerType;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * CashBackAdmin
 */
class CashBackAdmin extends AbstractAdmin
{

    const MESSAGE_NO_IMAGE_UPLOADED = 'Вы должны прикрепить изображение!';

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }

    /**
     * Проверка на непустой файл
     *
     * @param ErrorElement $errorElement
     * @param mixed        $cashBack
     *
     */
    public function validate(ErrorElement $errorElement, $cashBack)
    {
        parent::validate($errorElement, $cashBack);

        $cashBackImage = $cashBack->getCashBackImage();
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $this->getForm()->get('file')->getData();
        if (empty($uploadedFile) && empty($cashBackImage)) {
            $errorElement->with('file')->addViolation(self::MESSAGE_NO_IMAGE_UPLOADED);
        } elseif (!empty($uploadedFile)) {
            $this->replaceImage($uploadedFile, $cashBack);
        }
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $cashBackImage = $this->getSubject()->getCashBackImage();

        $fileFieldOptions = [
            'mapped'   => false,
            'required' => false,
        ];

        if (!empty($cashBackImage)) {
            $fileFieldOptions['help'] = $this->getImagePath($cashBackImage);
        }

        $formMapper
            ->add('id', null, [
                'read_only' => true,
                'disabled'  => true,
            ])
            ->add('status', 'choice', [
                'choices'   => CashBackStatusEnumType::getChoices(),
                'read_only' => true,
                'disabled'  => true,
                'help'      => 'Это поле меняется только системой',
            ])
            ->add('externalId')
            ->add('rating')
            ->add('active', null, [
                'label' => 'Активен',
            ])
            ->add('title', null, [
                'label' => 'Название кешбека/магазина',
            ])
            ->add('file', 'file', $fileFieldOptions)
            ->add('description', 'textarea', [
                'label' => 'Описание(для пользователя)',
                'required' => false,
                'attr'  => ['style' => 'height:100px'],
            ])
            ->add('condition', 'textarea', [
                'label'    => 'Условия(Только для внутреннего использования)',
                'required' => false,
                'attr'     => ['style' => 'height:200px'],
            ])
            ->add('url', null, [
                'label' => 'Заготовка для генерации кешбек-url',
            ])
            ->add('siteUrl', null, [
                'label' => 'Оригинальный адрес сайта',
            ])
            ->add('cash', null, [
                'required' => false,
                'label'    => 'Выплаты',
                'attr' => ['class' => 'payment_size']
            ])
            ->add('cashBackPlatform', null, [
                'property' => 'name',
                'label'    => 'Платформа',
            ])
            ->add('categories', 'sonata_type_collection',
                [
                    'required'     => false,
                    'by_reference' => false,
                ],
                [
                    'edit'   => 'inline',
                    'inline' => 'table',
                ]
            );
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('rating')
            ->add('title')
            ->add('condition')
            ->add('active')
            ->add('status', null, [], 'choice', [
                'choices' => CashBackStatusEnumType::getChoices(),
            ])
            ->add('cashBackPlatform', null, [
                'label' => 'Платформа',
            ])
            ->add('updatedAt', 'doctrine_orm_datetime_range', [], 'sonata_type_datetime_range_picker',
                [
                    'field_options_start' => ['format' => 'yyyy-MM-dd HH:mm:ss'],
                    'field_options_end'   => ['format' => 'yyyy-MM-dd HH:mm:ss'],
                ]
            )
            ->add('createdAt', 'doctrine_orm_datetime_range', [], 'sonata_type_datetime_range_picker',
                [
                    'field_options_start' => ['format' => 'yyyy-MM-dd HH:mm:ss'],
                    'field_options_end'   => ['format' => 'yyyy-MM-dd HH:mm:ss'],
                ]
            );
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('id')
            ->add('_action', null, [
                'label'   => 'Действия',
                'actions' => [
                    'actions' => ['template' => 'AppBundle:Admin\Sonata\CashBack:cash_back_list_actions.html.twig'],
                ],
            ])
            ->add('rating', null, [
                'template' => 'AppBundle:Admin\Sonata\CashBack:cashback_list_rating.html.twig',
            ])
            ->add('title')
//                   ->add('description')
            ->add('url', null, [
                'template' => 'AppBundle:Admin\Sonata\CashBack:cashback_list_url.html.twig',
            ])
//                   ->add('condition')
            ->add('cash')
            ->add('categories', null, [
                'template' => 'AppBundle:Admin\Sonata\CashBack:cashback_list_category.html.twig',
            ])
            ->add('active', null, [
                'editable' => true,
            ])
            ->add('status', 'choice', [
                'choices' => CashBackStatusEnumType::getChoices(),
                'template' => 'AppBundle:Admin\Sonata\CashBack:cashback_list_status.html.twig',
            ])
            ->add('cashBackPlatform', null, [
                'label' => 'Платформа',
            ])
            ->add('createdAt', null, ['label' => 'Создан'])
            ->add('updatedAt', null, ['label' => 'Обновлён']);

    }

    protected function getFlashBag()
    {
        return $this->getConfigurationPool()->getContainer()->get('session')->getFlashBag();
    }

//    /**
//     * Геттер сервиса для работы с изображениями
//     *
//     * @return RebateReceiptImageHandler
//     */
//    protected function getImageHandler(): RebateReceiptImageHandler
//    {
//        return $this->getConfigurationPool()->getContainer()->get('app.file_manager.rebate_receipt');
//    }
//
//    /**
//     * Геттер менеджера
//     *
//     * @return EntityManager
//     */
//    protected function getManager(): EntityManager
//    {
//        return $this->getConfigurationPool()->getContainer()->get('doctrine.orm.entity_manager');
//    }
//
//    /**
//     * Замена/присоединение изображения для кешбека
//     *
//     * @param UploadedFile $uploadedFile
//     * @param CashBack     $cashBack
//     *
//     * @return void
//     * @throws \Exception
//     */
//    protected function replaceImage(UploadedFile $uploadedFile, CashBack $cashBack)
//    {
//        $cashBackImage = $cashBack->getCashBackImage();
//
//        $newCashBackImage = new CashBackImage();
//        $result           = $this->getImageHandler()->saveEntityAndFile($newCashBackImage, $uploadedFile);
//
//        if (!$result) {
//            throw new \Exception('Сохранение не удалось');
//        }
//
//        $cashBack->setCashBackImage($newCashBackImage);
//        $this->getManager()->persist($cashBack);
//        $this->getManager()->flush();
//
//        if (!empty($cashBackImage)) {
//            $this->getImageHandler()->deleteEntityFile($cashBackImage);
//        }
//    }

    /**
     * Возвращает изображение
     *
     * @param CashBackImage $cashBackImage
     *
     * @return string
     */
    protected function getImagePath(CashBackImage $cashBackImage)
    {
        $fullPath = $cashBackImage->getFilePath();

        return '<a target="_blank" href="' . $fullPath . '"><img style="max-width: 100px" src="' . $fullPath . '" class="admin-preview" /></a>';
    }

    public function configure()
    {
        $this->setTemplate('list', 'AppBundle:Admin\Sonata\CashBack:cashback_list.html.twig');
        $this->setTemplate('edit', 'AppBundle:Admin\Sonata\CashBack:cashback_edit.html.twig');
    }
}
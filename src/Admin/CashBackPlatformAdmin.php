<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

/**
 * CashBackPlatformAdmin
 */
class CashBackPlatformAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('id', null, [
                'disabled'  => true,
            ])
            ->add('externalPlatformId', null, [
                'label' => 'Внешний Id учетной записи у кешбек сервиса',
            ])
            ->add('name', null, [
                'label' => 'Название',
            ])
            ->add('baseUrl', null, [
                'label' => 'Базовый URL',
            ])
            ->add('authHeader', null, [
                'label' => 'Авторизационный заголовок',
            ])
            ->add('clientId', null, [
                'label' => 'Id клиента',
            ])
            ->add('token', null, [
                'label' => 'Временный токен авторизации',
            ])
//            ->add('expiredAt', DatePickerType::class, [
//                'format' => DateType::HTML5_FORMAT,
//            ])
        ;

    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('name')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('name', null, [
                'label' => 'Название',
            ])
            ->add('baseUrl', null, [
                'label' => 'Базовый Url',
            ])
            ->add('authHeader', null, [
                'label' => 'Авторизационный заголовок',
            ])
            ->add('clientId', null, [
                'label' => 'Id клиента',
            ])
            ->add('createdAt', null, ['label' => 'Создан'])
            ->add('updatedAt', null, ['label' => 'Обновлён']);
    }
}
<?php

declare(strict_types=1);

namespace App\Admin;

use AppBundle\Entity\Stock\CashBack;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * CashBackPlatformAdmin
 */
class CashBackPlatformAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('id', null, [
                'read_only' => true,
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
            ->add('expiredAt', 'sonata_type_datetime_picker', ['format' => 'yyyy-MM-dd HH:mm:ss']);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('name')
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
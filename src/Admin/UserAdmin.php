<?php

declare(strict_types = 1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class UserAdmin.
 */
class UserAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('username')
            ->add('email')
            ->add('enabled')
            ->add('roles');
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add(
                'id',
                null,
                [
                    'attr' => [
                        'readonly' => true,
                    ],
                ]
            )
            ->add('email')
            ->add('enabled')
            ->add(
                'roles',
                ChoiceType::class,
                [
                    'choices' => $this->getRoles(),
                    'multiple' => true,
                ]
            );
    }

    /**
     * @return array
     */
    protected function getRoles()
    {
        $rolesHierarchy = $this->getConfigurationPool()->getContainer()->getParameter('security.role_hierarchy.roles');

        $flatRoles = [];
        foreach ($rolesHierarchy as $roles) {
            if (empty($roles)) {
                continue;
            }

            foreach ($roles as $role) {
                if (!isset($flatRoles[$role])) {
                    $flatRoles[$role] = $role;
                }
            }
        }

        return $flatRoles;
    }
}

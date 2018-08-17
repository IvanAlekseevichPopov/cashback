<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class UserAdmin.
 */
class UserAdmin extends AbstractAdmin
{
    /**
     * @param object $object
     */
    public function prePersist($object)
    {
        /* @var User $object */
        parent::prePersist($object);
        $this->updatePassword($object);
    }

    /**
     * @param object $object
     */
    public function preUpdate($object)
    {
        /* @var User $object */
        parent::preUpdate($object);
        $this->updatePassword($object);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('username')
            ->add('email')
            ->add('enabled')
            ->add('roles');
    }

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('id', null, [
                'attr' => ['readonly' => true],
            ])
            ->add('username')
            ->add('email')
            ->add('plainPassword', TextType::class, [
                'required' => false,
            ])
            ->add('enabled')
            ->add('roles', ChoiceType::class, [
                'choices' => $this->getRoles(),
                'multiple' => true,
            ])
        ;
    }

    /**
     * @param DatagridMapper $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('username')
            ->add('email')
            ->add('enabled');
    }

    /**
     * @return array
     */
    private function getRoles()
    {
        $rolesHierarchy = $this->getConfigurationPool()->getContainer()->getParameter('security.role_hierarchy.roles');

        $flatRoles = [];
        foreach ($rolesHierarchy as $parent => $roles) {
            if (empty($roles)) {
                continue;
            }

            foreach ($roles as $role) {
                $flatRoles[$role] = $role;
            }
        }

        return $flatRoles;
    }

    /**
     * @param User $user
     */
    private function updatePassword(User $user)
    {
        if ($user->getPlainPassword()) {
            $this->getConfigurationPool()->getContainer()->get('fos_user.user_manager')->updateUser($user, false);
        }
    }
}

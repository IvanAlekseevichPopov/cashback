<?php

declare(strict_types=1);

namespace App\Admin;

use App\DBAL\Types\Enum\CashBackStatusEnumType;
use App\Entity\CashBack;
use App\Entity\CashBackImage;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * CashBackAdmin.
 */
class CashBackAdmin extends AbstractAdmin
{
    public const MESSAGE_NO_IMAGE_UPLOADED = 'Вы должны прикрепить изображение!';

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }

    /**
     * Проверка на непустой файл.
     *
     * @param ErrorElement $errorElement
     * @param mixed        $cashBack
     */
    public function validate(ErrorElement $errorElement, $cashBack)
    {
        parent::validate($errorElement, $cashBack);

        $cashBackImage = $cashBack->getCashBackImage();
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $this->getForm()->get('cashBackImage')->getData();
        if (empty($uploadedFile) && empty($cashBackImage)) {
            $errorElement->with('file')->addViolation(self::MESSAGE_NO_IMAGE_UPLOADED);
        } elseif (!empty($uploadedFile)) {
            $this->replaceImage($uploadedFile, $cashBack);
        }
    }

    public function configure()
    {
//        $this->setTemplate('list', 'AppBundle:Admin\Sonata\CashBack:cashback_list.html.twig');
//        $this->setTemplate('edit', 'AppBundle:Admin\Sonata\CashBack:cashback_edit.html.twig');
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('id', null, [
                'disabled' => true,
            ])
            ->add('status', ChoiceType::class, [
                'choices' => CashBackStatusEnumType::getChoices(),
                'disabled' => true,
                'help' => 'Это поле меняется только системой',
            ])
            ->add('externalId')
            ->add('rating')
            ->add('active', null, [
                'label' => 'Активен',
            ])
            ->add('title', null, [
                'label' => 'Название кешбека/магазина',
            ])
            ->add('cashBackImage', FileType::class, $this->getImageOptions())
            ->add('description', TextareaType::class, [
                'label' => 'Описание(для пользователя)',
                'required' => true,
                'attr' => ['style' => 'height:100px'],
            ])
            ->add('condition', TextareaType::class, [
                'label' => 'Условия(Только для внутреннего использования)',
                'required' => false,
                'attr' => ['style' => 'height:200px'],
            ])
            ->add('url', null, [
                'label' => 'Заготовка для генерации кешбек-url',
            ])
            ->add('siteUrl', null, [
                'label' => 'Оригинальный адрес сайта',
            ])
            ->add('cash', null, [
                'required' => false,
                'label' => 'Выплаты',
                'attr' => ['class' => 'payment_size'],
            ])
            ->add('cashBackPlatform', null, [
                'label' => 'Платформа',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('rating')
            ->add('title');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('id')
            ->add('_action', null, [
                'label' => 'Действия',
                'actions' => [
                    'actions' => ['template' => 'admin/cashback/list_actions.html.twig'],
                ],
            ])
            ->add('rating')
            ->add('title')
            ->add('description')
            ->add('url')
            ->add('condition', null, [
                'collapse' => true,
                'header_style' => 'width:10%;',
            ])
            ->add('cash')
            ->add('categories', null, [
                'associated_property' => 'title',
//                'template' => 'admin/cashback/list_category.html.twig', //TODO починить это дерьмо(не работает замещение блока twig)
//                'collapse' => true
            ])
            ->add('active', null, [
                'editable' => true,
            ])
            ->add('status', 'choice', [
                'choices' => CashBackStatusEnumType::getChoices(),
                'template' => 'admin/cashback/list_status.html.twig',
            ])
        ;
    }

    /**
     * Замена/присоединение изображения для кешбека.
     *
     * @param UploadedFile $uploadedFile
     * @param CashBack     $cashBack
     *
     * @throws \Exception
     */
    protected function replaceImage(UploadedFile $uploadedFile, CashBack $cashBack)
    {
        $newCashBackImage = new CashBackImage();
        $newCashBackImage->setFile($uploadedFile);

        $cashBack->setCashBackImage($newCashBackImage);
    }

    protected function getFlashBag()
    {
        return $this->getConfigurationPool()->getContainer()->get('session')->getFlashBag();
    }

    private function getImageOptions()
    {
        /** @var CashBack $cashback */
        $cashback = $this->getSubject();
        /** @var CashBackImage $image */
        $image = $cashback->getCashBackImage();

        $fileFieldOptions = ['mapped' => false, 'required' => false];
        if (null !== $image && $webPath = $image->getFilePath()) {
            $container = $this->getConfigurationPool()->getContainer();
            $fullPath = $container->get('request_stack')->getCurrentRequest()->getBasePath().'/'.$webPath;
            $fileFieldOptions['help'] = '<img src="'.$fullPath.'" class="admin-preview"/>'; //TODO маленькая превьюшка изображения
        } else {
            $fileFieldOptions['required'] = true;
        }

        return $fileFieldOptions;
    }
}

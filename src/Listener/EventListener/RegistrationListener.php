<?php

declare(strict_types=1);

namespace App\Listener\EventListener;

use App\Model\Flash\FlashTypes;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class RegistrationListener.
 */
class RegistrationListener implements EventSubscriberInterface
{
    /** @var FlashBagInterface */
    protected $flashBag;
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param FlashBagInterface   $flashBag
     * @param TranslatorInterface $translator
     */
    public function __construct(FlashBagInterface $flashBag, TranslatorInterface $translator)
    {
        $this->flashBag = $flashBag;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationCompleted',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function onRegistrationCompleted(FilterUserResponseEvent $event)
    {
        $this->flashBag->add(
            FlashTypes::SUCCESS,
            $this->translator->trans('register.start.success')
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Event\AppEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\FacebookResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\MailRuResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\VkontakteResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\YandexResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseUserProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class FOSUBUserProvider.
 */
class FOSUBUserProvider extends BaseUserProvider
{
    /** @var EventDispatcher */
    private $eventDispatcher;

    /**
     * FOSUBUserProvider constructor.
     *
     * @param UserManagerInterface     $userManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param array                    $properties
     */
    public function __construct(UserManagerInterface $userManager, EventDispatcherInterface $eventDispatcher, array $properties)
    {
        parent::__construct($userManager, $properties);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $userName = $this->getUserName($response);

        try {
            $user = parent::loadUserByOAuthUserResponse($response);
        } catch (AccountNotLinkedException $e) {
            $user = $this->userManager->findUserByEmail($response->getEmail());
            //TODO add profile picture

            if (!$user) {
                /** @var User $user */
                $user = $this->userManager->createUser();
                $user->setEmail($response->getEmail());
                $user->setPlainPassword(md5(uniqid('', true)));
                $user->setUsername($userName);
                $user->setEnabled(true);
                $user->addRole(User::ROLE_DEFAULT);

                $this->userManager->updateCanonicalFields($user);
                $this->userManager->updatePassword($user);

                $this->eventDispatcher->dispatch(AppEvents::REGISTER_BY_GOOGLE, new GenericEvent($user));
            }
        }

        $serviceName = $response->getResourceOwner()->getName();

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($user, ucfirst($serviceName).'AccessToken', $response->getAccessToken());
        $accessor->setValue($user, ucfirst($serviceName).'Id', $response->getUsername());

        return $user;
    }

    /**
     * @param UserResponseInterface $response
     *
     * @return string
     */
    private function getUserName(UserResponseInterface $response): string
    {
        if ($response->getResourceOwner() instanceof VkontakteResourceOwner) {
            return $response->getFirstName().' '.$response->getLastName();
        } elseif ($response->getResourceOwner() instanceof GoogleResourceOwner) {
            return $response->getNickname();
        } elseif ($response->getResourceOwner() instanceof MailRuResourceOwner || $response->getResourceOwner() instanceof YandexResourceOwner || $response->getResourceOwner() instanceof  FacebookResourceOwner) {
            return $response->getRealName();
        }

        throw new LogicException('cannot be here');
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as FOSUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * User.
 *
 * @ORM\Table(name="user")
 * @UniqueEntity(fields={"email"}, message="email.already_used")
 * @ORM\Entity
 */
class User extends FOSUser
{
    public const ROLE_MODERATOR = 'ROLE_MODERATOR';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @ORM\Column(
     *     name="phone",
     *     type="bigint",
     *     nullable=true,
     *     length=11,
     * )
     *
     * @var string
     */
    protected $phone; //TODO https://github.com/misd-service-development/phone-number-bundle

    /**
     * @ORM\OneToOne(
     *     targetEntity="Balance",
     *     cascade={
     *         "persist",
     *         "remove"
     *     }
     * )
     *
     * @var Balance
     */
    protected $balance;

    /**
     * @ORM\OneToMany(
     *     mappedBy="user",
     *     targetEntity="Transaction"
     * )
     *
     * @var ArrayCollection|Transaction[]
     */
    protected $transactions;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    protected $googleId;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    protected $googleAccessToken;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    protected $vkontakteId;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    protected $vkontakteAccessToken;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    protected $mailruId;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    protected $mailruAccessToken;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    protected $yandexId;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    protected $yandexAccessToken;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    protected $facebookId;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    protected $facebookAccessToken;

    /**
     * @return Balance
     */
    public function getBalance(): ?Balance
    {
        return $this->balance;
    }

    /**
     * @param Balance $balance
     *
     * @return User
     */
    public function setBalance(Balance $balance): User
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return User
     */
    public function setPhone(string $phone): User
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @param string|null $googleId
     */
    public function setGoogleId(?string $googleId): void
    {
        $this->googleId = $googleId;
    }

    /**
     * @return string|null
     */
    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    /**
     * @return string|null
     */
    public function getGoogleAccessToken(): ?string
    {
        return $this->googleAccessToken;
    }

    /**
     * @param string|null $googleAccessToken
     */
    public function setGoogleAccessToken(?string $googleAccessToken): void
    {
        $this->googleAccessToken = $googleAccessToken;
    }

    /**
     * @return string|null
     */
    public function getVkontakteId(): ?string
    {
        return $this->vkontakteId;
    }

    /**
     * @param string|null $vkontakteId
     */
    public function setVkontakteId(?string $vkontakteId): void
    {
        $this->vkontakteId = $vkontakteId;
    }

    /**
     * @return string|null
     */
    public function getVkontakteAccessToken(): ?string
    {
        return $this->vkontakteAccessToken;
    }

    /**
     * @param string|null $vkontakteAccessToken
     */
    public function setVkontakteAccessToken(?string $vkontakteAccessToken): void
    {
        $this->vkontakteAccessToken = $vkontakteAccessToken;
    }

    public function isModerator(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN) || $this->hasRole(self::ROLE_MODERATOR);
    }

    /**
     * @return string|null
     */
    public function getMailruId(): ?string
    {
        return $this->mailruId;
    }

    /**
     * @param string|null $mailruId
     */
    public function setMailruId(?string $mailruId): void
    {
        $this->mailruId = $mailruId;
    }

    /**
     * @return string|null
     */
    public function getMailruAccessToken(): ?string
    {
        return $this->mailruAccessToken;
    }

    /**
     * @param string|null $mailruAccessToken
     */
    public function setMailruAccessToken(?string $mailruAccessToken): void
    {
        $this->mailruAccessToken = $mailruAccessToken;
    }

    /**
     * @return string|null
     */
    public function getYandexId(): ?string
    {
        return $this->yandexId;
    }

    /**
     * @param string|null $yandexId
     */
    public function setYandexId(?string $yandexId): void
    {
        $this->yandexId = $yandexId;
    }

    /**
     * @return string|null
     */
    public function getYandexAccessToken(): ?string
    {
        return $this->yandexAccessToken;
    }

    /**
     * @param string|null $yandexAccessToken
     */
    public function setYandexAccessToken(?string $yandexAccessToken): void
    {
        $this->yandexAccessToken = $yandexAccessToken;
    }

    /**
     * @return string|null
     */
    public function getFacebookId(): ?string
    {
        return $this->facebookId;
    }

    /**
     * @param string|null $facebookId
     */
    public function setFacebookId(?string $facebookId): void
    {
        $this->facebookId = $facebookId;
    }

    /**
     * @return string|null
     */
    public function getFacebookAccessToken(): ?string
    {
        return $this->facebookAccessToken;
    }

    /**
     * @param string|null $facebookAccessToken
     */
    public function setFacebookAccessToken(?string $facebookAccessToken): void
    {
        $this->facebookAccessToken = $facebookAccessToken;
    }
}

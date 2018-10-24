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
     * @return null|string
     */
    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    /**
     * @return null|string
     */
    public function getGoogleAccessToken(): ?string
    {
        return $this->googleAccessToken;
    }

    /**
     * @param null|string $googleAccessToken
     */
    public function setGoogleAccessToken(?string $googleAccessToken): void
    {
        $this->googleAccessToken = $googleAccessToken;
    }

    /**
     * @return null|string
     */
    public function getVkontakteId(): ?string
    {
        return $this->vkontakteId;
    }

    /**
     * @param null|string $vkontakteId
     */
    public function setVkontakteId(?string $vkontakteId): void
    {
        $this->vkontakteId = $vkontakteId;
    }

    /**
     * @return null|string
     */
    public function getVkontakteAccessToken(): ?string
    {
        return $this->vkontakteAccessToken;
    }

    /**
     * @param null|string $vkontakteAccessToken
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
     * @return null|string
     */
    public function getMailruId(): ?string
    {
        return $this->mailruId;
    }

    /**
     * @param null|string $mailruId
     */
    public function setMailruId(?string $mailruId): void
    {
        $this->mailruId = $mailruId;
    }

    /**
     * @return null|string
     */
    public function getMailruAccessToken(): ?string
    {
        return $this->mailruAccessToken;
    }

    /**
     * @param null|string $mailruAccessToken
     */
    public function setMailruAccessToken(?string $mailruAccessToken): void
    {
        $this->mailruAccessToken = $mailruAccessToken;
    }

    /**
     * @return null|string
     */
    public function getYandexId(): ?string
    {
        return $this->yandexId;
    }

    /**
     * @param null|string $yandexId
     */
    public function setYandexId(?string $yandexId): void
    {
        $this->yandexId = $yandexId;
    }

    /**
     * @return null|string
     */
    public function getYandexAccessToken(): ?string
    {
        return $this->yandexAccessToken;
    }

    /**
     * @param null|string $yandexAccessToken
     */
    public function setYandexAccessToken(?string $yandexAccessToken): void
    {
        $this->yandexAccessToken = $yandexAccessToken;
    }
}

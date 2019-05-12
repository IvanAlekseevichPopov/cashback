<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as FOSUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @UniqueEntity(fields={"email"}, message="email.already_used")
 * @ORM\Entity
 */
class User extends FOSUser
{
    public const ROLE_MODERATOR = 'ROLE_MODERATOR';

    //TODO migrate on uuid

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
    protected $phone;

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
    protected $balance; //TODO https://github.com/misd-service-development/phone-number-bundle

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

    public function getBalance(): ?Balance
    {
        return $this->balance;
    }

    public function setBalance(Balance $balance): void
    {
        $this->balance = $balance;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): void
    {
        $this->googleId = $googleId;
    }

    public function getGoogleAccessToken(): ?string
    {
        return $this->googleAccessToken;
    }

    public function setGoogleAccessToken(?string $googleAccessToken): void
    {
        $this->googleAccessToken = $googleAccessToken;
    }

    public function getVkontakteId(): ?string
    {
        return $this->vkontakteId;
    }

    public function setVkontakteId(?string $vkontakteId): void
    {
        $this->vkontakteId = $vkontakteId;
    }

    public function getVkontakteAccessToken(): ?string
    {
        return $this->vkontakteAccessToken;
    }

    public function setVkontakteAccessToken(?string $vkontakteAccessToken): void
    {
        $this->vkontakteAccessToken = $vkontakteAccessToken;
    }

    public function isModerator(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN) || $this->hasRole(self::ROLE_MODERATOR);
    }

    public function getMailruId(): ?string
    {
        return $this->mailruId;
    }

    public function setMailruId(?string $mailruId): void
    {
        $this->mailruId = $mailruId;
    }

    public function getMailruAccessToken(): ?string
    {
        return $this->mailruAccessToken;
    }

    public function setMailruAccessToken(?string $mailruAccessToken): void
    {
        $this->mailruAccessToken = $mailruAccessToken;
    }

    public function getYandexId(): ?string
    {
        return $this->yandexId;
    }

    public function setYandexId(?string $yandexId): void
    {
        $this->yandexId = $yandexId;
    }

    public function getYandexAccessToken(): ?string
    {
        return $this->yandexAccessToken;
    }

    public function setYandexAccessToken(?string $yandexAccessToken): void
    {
        $this->yandexAccessToken = $yandexAccessToken;
    }

    public function getFacebookId(): ?string
    {
        return $this->facebookId;
    }

    public function setFacebookId(?string $facebookId): void
    {
        $this->facebookId = $facebookId;
    }

    public function getFacebookAccessToken(): ?string
    {
        return $this->facebookAccessToken;
    }

    public function setFacebookAccessToken(?string $facebookAccessToken): void
    {
        $this->facebookAccessToken = $facebookAccessToken;
    }
}

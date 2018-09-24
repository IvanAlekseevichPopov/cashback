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
}

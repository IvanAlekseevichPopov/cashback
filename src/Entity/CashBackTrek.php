<?php

declare(strict_types=1);

namespace AppBundle\Entity\Stock;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="cash_back_trek",
 *     options={
 *         "collate": "utf8mb4_unicode_ci",
 *         "charset": "utf8mb4",
 *         "comment": "Cashback treks"
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CashBack\CashBackTrekRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CashBackTrek
{
    use UuidIdColumn;
    use CreatedAtColumn;
    use UpdatedAtColumn;

    use CreatedAtLifecycleTrait;
    use UpdatedAtLifecycleTrait;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="AppBundle\Entity\Users\User",
     *     fetch="EXTRA_LAZY"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id"
     * )
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="AppBundle\Entity\Stock\CashBack",
     *     fetch="EXTRA_LAZY"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(
     *     name="cash_back_id",
     *     referencedColumnName="id"
     * )
     *
     * @var CashBack
     */
    protected $cashBack;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="TransactionRepository",
     *     fetch="LAZY",
     * )
     * @ORM\JoinColumn(
     *     name="balance_history_id",
     *     nullable=true,
     *     referencedColumnName="id"
     * )
     *
     * @var UserBalanceHistory|null
     */
    protected $balanceHistory;

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return CashBack
     */
    public function getCashBack(): CashBack
    {
        return $this->cashBack;
    }

    /**
     * @param CashBack $cashBack
     *
     * @return $this
     */
    public function setCashBack(CashBack $cashBack)
    {
        $this->cashBack = $cashBack;

        return $this;
    }

    /**
     * @return UserBalanceHistory|null
     */
    public function getBalanceHistory(): ?UserBalanceHistory
    {
        return $this->balanceHistory;
    }

    /**
     * @param UserBalanceHistory|null $balanceHistory
     *
     * @return $this
     */
    public function setBalanceHistory(?UserBalanceHistory $balanceHistory)
    {
        $this->balanceHistory = $balanceHistory;

        return $this;
    }
}

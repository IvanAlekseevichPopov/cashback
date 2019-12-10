<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20190512181649 extends AbstractMigration
{
    private const CASHBACK_STATUS_MAPPINGS = [
        'NOT_PARTNER ' => 'not_partner',
        'AWAITING_PARTNERSHIP ' => 'awaiting_partnership',
        'APPROVED_PARTNERSHIP ' => 'approved_partnership',
        'REJECTED_PARTNERSHIP ' => 'rejected_partnership',
        'CLOSES_COMPANY ' => 'closed_company',
    ];

    public function up(Schema $schema): void
    {
        foreach (self::CASHBACK_STATUS_MAPPINGS as $oldStatus => $newStatus) {
            $this->addSql('UPDATE cash_back SET status = ? WHERE status = ?;', [
                $newStatus, $oldStatus,
            ]);
        }

        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cash_back CHANGE site_url site_url VARCHAR(255) NOT NULL, CHANGE status status ENUM(\'not_partner\', \'awaiting_partnership\', \'approved_partnership\', \'rejected_partnership\', \'closed_company\') NOT NULL COMMENT \'(DC2Type:CashBackStatusEnumType)\', CHANGE rating rating NUMERIC(4, 1) NOT NULL');
        $this->addSql('ALTER TABLE cash_back_platform CHANGE expired_at expired_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE transaction CHANGE operation_type operation_type ENUM(\'create\', \'decrease\', \'increase\') NOT NULL COMMENT \'(DC2Type:TransactionEnumType)\', CHANGE status status ENUM(\'wait\', \'approved\', \'reject\', \'wait_moderation\') NOT NULL COMMENT \'(DC2Type:TransactionStatusEnumType)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cash_back CHANGE site_url site_url VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'адрес сайта-заказчика\', CHANGE status status ENUM(\'not_partner\', \'awaiting_partnership\', \'approved_partnership\', \'rejected_partnership\', \'closed_company\') NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'Статус кешбека(DC2Type:CashBackStatusEnumType)\', CHANGE rating rating NUMERIC(4, 1) NOT NULL COMMENT \'Рейтинг площадки\'');
        $this->addSql('ALTER TABLE cash_back_platform CHANGE expired_at expired_at DATETIME DEFAULT NULL COMMENT \'Дата протухания токена\'');
        $this->addSql('ALTER TABLE transaction CHANGE operation_type operation_type ENUM(\'create\', \'decrease\', \'increase\') NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'operation type(DC2Type:TransactionEnumType)\', CHANGE status status ENUM(\'wait\', \'approved\', \'reject\', \'wait_moderation\') NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'Статус операции(DC2Type:TransactionStatusEnumType)\'');
    }
}

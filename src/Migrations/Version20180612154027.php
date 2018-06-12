<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Version20180612154027.
 */
final class Version20180612154027 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM cash_back_platform');
        $this->addSql('ALTER TABLE cash_back_platform AUTO_INCREMENT = 1');
        $this->addSql(
            'INSERT INTO cash_back_platform (id, name, base_url, client_id, auth_header, external_platform_id, token, expired_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [1, 'admitad', 'https://api.admitad.com/', '947edd1e1feb1a82b19884ddb7271b', 'OTQ3ZWRkMWUxZmViMWE4MmIxOTg4NGRkYjcyNzFiOjRmNzQxMDZjZWViZGNkZGZhYjE2NzdlYTZmMDJjNA==', '607803', '910d7036064e74029dcd', '2018-06-19 11:17:16']
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM cash_back_platform');
        $this->addSql('ALTER TABLE cash_back_platform AUTO_INCREMENT = 1');
    }
}

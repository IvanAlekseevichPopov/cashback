<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20181024200643 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD mailru_id VARCHAR(255) DEFAULT NULL, ADD mailru_access_token VARCHAR(255) DEFAULT NULL, ADD yandex_id VARCHAR(255) DEFAULT NULL, ADD yandex_access_token VARCHAR(255) DEFAULT NULL, ADD facebook_id VARCHAR(255) DEFAULT NULL, ADD facebook_access_token VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP mailru_id, DROP mailru_access_token, DROP yandex_id, DROP yandex_access_token, DROP facebook_id, DROP facebook_access_token');
    }
}

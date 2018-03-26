<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Version20180326172911
 */
class Version20180326172911 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, balance_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', phone BIGINT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_8D93D649C05FB297 (confirmation_token), UNIQUE INDEX UNIQ_8D93D649AE91A3DD (balance_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cash_back_platform (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(64) NOT NULL, base_url VARCHAR(128) NOT NULL, client_id VARCHAR(64) NOT NULL, auth_header VARCHAR(128) NOT NULL, external_platform_id VARCHAR(32) DEFAULT NULL, token VARCHAR(64) DEFAULT NULL, expired_at DATETIME DEFAULT NULL COMMENT \'Дата протухания токена\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'Cashback platforms\' ');
        $this->addSql('CREATE TABLE cash_back_trek (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, cash_back_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', transaction_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_7E867D69A76ED395 (user_id), INDEX IDX_7E867D69B7B42DAC (cash_back_id), INDEX IDX_7E867D692FC0CB0F (transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'Cashback treks\' ');
        $this->addSql('CREATE TABLE cash_back_category (id INT AUTO_INCREMENT NOT NULL, cash_back_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(64) NOT NULL, cash VARCHAR(255) NOT NULL, external_id INT DEFAULT NULL, INDEX IDX_677FBF15B7B42DAC (cash_back_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'Cashback categories\' ');
        $this->addSql('CREATE TABLE transaction (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id INT DEFAULT NULL, balance_id INT DEFAULT NULL, operation_type ENUM(\'create\', \'decrease\', \'increase\') NOT NULL COMMENT \'operation type(DC2Type:TransactionEnumType)\', status ENUM(\'wait\', \'approved\', \'reject\', \'wait_moderation\') NOT NULL COMMENT \'Статус операции(DC2Type:TransactionStatusEnumType)\', comment VARCHAR(255) DEFAULT NULL, INDEX IDX_723705D1A76ED395 (user_id), INDEX IDX_723705D1AE91A3DD (balance_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'History of balance changes\' ');
        $this->addSql('CREATE TABLE cash_back (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', cash_back_platform_id INT DEFAULT NULL, cash_back_image_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(64) NOT NULL, description TEXT DEFAULT NULL, cashback_condition TEXT NOT NULL COMMENT \'Условия кешбека\', url VARCHAR(255) NOT NULL COMMENT \'адрес кешбек сервиса\', site_url VARCHAR(255) NOT NULL COMMENT \'адрес сайта-заказчика\', cash VARCHAR(255) NOT NULL, external_id INT DEFAULT NULL, active TINYINT(1) NOT NULL, status ENUM(\'NOT_PARTNER\', \'AWAITING_PARTNERSHIP\', \'APPROVED_PARTNERSHIP\', \'REJECTED_PARTNERSHIP\', \'CLOSES_COMPANY\') NOT NULL COMMENT \'Статус кешбека(DC2Type:CashBackStatusEnumType)\', rating NUMERIC(4, 1) NOT NULL COMMENT \'Рейтинг площадки\', INDEX IDX_5048085A32EBAE9B (cash_back_platform_id), UNIQUE INDEX UNIQ_5048085A5C9683B9 (cash_back_image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'Cashbacks\' ');
        $this->addSql('CREATE TABLE cash_back_image (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', extension VARCHAR(5) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'Cashback images\' ');
        $this->addSql('CREATE TABLE balance (id INT AUTO_INCREMENT NOT NULL, amount NUMERIC(16, 4) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'User balance\' ');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649AE91A3DD FOREIGN KEY (balance_id) REFERENCES balance (id)');
        $this->addSql('ALTER TABLE cash_back_trek ADD CONSTRAINT FK_7E867D69A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cash_back_trek ADD CONSTRAINT FK_7E867D69B7B42DAC FOREIGN KEY (cash_back_id) REFERENCES cash_back (id)');
        $this->addSql('ALTER TABLE cash_back_trek ADD CONSTRAINT FK_7E867D692FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE cash_back_category ADD CONSTRAINT FK_677FBF15B7B42DAC FOREIGN KEY (cash_back_id) REFERENCES cash_back (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1AE91A3DD FOREIGN KEY (balance_id) REFERENCES balance (id)');
        $this->addSql('ALTER TABLE cash_back ADD CONSTRAINT FK_5048085A32EBAE9B FOREIGN KEY (cash_back_platform_id) REFERENCES cash_back_platform (id)');
        $this->addSql('ALTER TABLE cash_back ADD CONSTRAINT FK_5048085A5C9683B9 FOREIGN KEY (cash_back_image_id) REFERENCES cash_back_image (id)');
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cash_back_trek DROP FOREIGN KEY FK_7E867D69A76ED395');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1A76ED395');
        $this->addSql('ALTER TABLE cash_back DROP FOREIGN KEY FK_5048085A32EBAE9B');
        $this->addSql('ALTER TABLE cash_back_trek DROP FOREIGN KEY FK_7E867D692FC0CB0F');
        $this->addSql('ALTER TABLE cash_back_trek DROP FOREIGN KEY FK_7E867D69B7B42DAC');
        $this->addSql('ALTER TABLE cash_back_category DROP FOREIGN KEY FK_677FBF15B7B42DAC');
        $this->addSql('ALTER TABLE cash_back DROP FOREIGN KEY FK_5048085A5C9683B9');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649AE91A3DD');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1AE91A3DD');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE cash_back_platform');
        $this->addSql('DROP TABLE cash_back_trek');
        $this->addSql('DROP TABLE cash_back_category');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE cash_back');
        $this->addSql('DROP TABLE cash_back_image');
        $this->addSql('DROP TABLE balance');
    }
}

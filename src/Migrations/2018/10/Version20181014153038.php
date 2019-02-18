<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181014153038 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE schedule (id INT AUTO_INCREMENT NOT NULL, screen_id VARCHAR(36) NOT NULL, presentation_id INT NOT NULL, scheduled_start DATETIME NOT NULL, scheduled_end DATETIME NOT NULL, INDEX IDX_5A3811FB41A67722 (screen_id), INDEX IDX_5A3811FBAB627E8B (presentation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE api_access_keys (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, code VARCHAR(32) NOT NULL, name VARCHAR(255) NOT NULL, last_use DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', UNIQUE INDEX UNIQ_2820A86477153098 (code), INDEX IDX_2820A864A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE screen_associations (id INT AUTO_INCREMENT NOT NULL, screen_id VARCHAR(36) NOT NULL, user_id INT DEFAULT NULL, role VARCHAR(32) NOT NULL, INDEX IDX_15BC34BC41A67722 (screen_id), INDEX IDX_15BC34BCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE presentations (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, title VARCHAR(200) NOT NULL, notes LONGTEXT NOT NULL, settings LONGTEXT NOT NULL, last_modified DATETIME DEFAULT NULL, type VARCHAR(200) NOT NULL, INDEX IDX_72936B1DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', user_type VARCHAR(32) NOT NULL, name VARCHAR(200) NOT NULL, UNIQUE INDEX UNIQ_1483A5E992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_1483A5E9A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_1483A5E9C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users_orgas (user_id INT NOT NULL, organization_id INT NOT NULL, INDEX IDX_3737C1AA76ED395 (user_id), INDEX IDX_3737C1A32C8A3DE (organization_id), PRIMARY KEY(user_id, organization_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE screens (guid VARCHAR(36) NOT NULL, presentation_id INT DEFAULT NULL, name VARCHAR(200) NOT NULL, location LONGTEXT NOT NULL, notes LONGTEXT NOT NULL, admin_c LONGTEXT NOT NULL, first_connect DATETIME NOT NULL, last_connect DATETIME NOT NULL, connect_code VARCHAR(8) NOT NULL, INDEX IDX_3D08B3C6AB627E8B (presentation_id), PRIMARY KEY(guid)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB41A67722 FOREIGN KEY (screen_id) REFERENCES screens (guid)');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FBAB627E8B FOREIGN KEY (presentation_id) REFERENCES presentations (id)');
        $this->addSql('ALTER TABLE api_access_keys ADD CONSTRAINT FK_2820A864A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE screen_associations ADD CONSTRAINT FK_15BC34BC41A67722 FOREIGN KEY (screen_id) REFERENCES screens (guid)');
        $this->addSql('ALTER TABLE screen_associations ADD CONSTRAINT FK_15BC34BCA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE presentations ADD CONSTRAINT FK_72936B1DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE users_orgas ADD CONSTRAINT FK_3737C1AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE users_orgas ADD CONSTRAINT FK_3737C1A32C8A3DE FOREIGN KEY (organization_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE screens ADD CONSTRAINT FK_3D08B3C6AB627E8B FOREIGN KEY (presentation_id) REFERENCES presentations (id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FBAB627E8B');
        $this->addSql('ALTER TABLE screens DROP FOREIGN KEY FK_3D08B3C6AB627E8B');
        $this->addSql('ALTER TABLE api_access_keys DROP FOREIGN KEY FK_2820A864A76ED395');
        $this->addSql('ALTER TABLE screen_associations DROP FOREIGN KEY FK_15BC34BCA76ED395');
        $this->addSql('ALTER TABLE presentations DROP FOREIGN KEY FK_72936B1DA76ED395');
        $this->addSql('ALTER TABLE users_orgas DROP FOREIGN KEY FK_3737C1AA76ED395');
        $this->addSql('ALTER TABLE users_orgas DROP FOREIGN KEY FK_3737C1A32C8A3DE');
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FB41A67722');
        $this->addSql('ALTER TABLE screen_associations DROP FOREIGN KEY FK_15BC34BC41A67722');
        $this->addSql('DROP TABLE schedule');
        $this->addSql('DROP TABLE api_access_keys');
        $this->addSql('DROP TABLE screen_associations');
        $this->addSql('DROP TABLE presentations');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE users_orgas');
        $this->addSql('DROP TABLE screens');
    }
}

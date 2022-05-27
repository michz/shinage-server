<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190223113607 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users ADD email_auth_enabled TINYINT(1) NOT NULL, ADD email_auth_code VARCHAR(6) DEFAULT NULL, ADD totp_secret VARCHAR(200) DEFAULT NULL, ADD backup_codes JSON DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users DROP email_auth_enabled, DROP email_auth_code, DROP totp_secret, DROP backup_codes');
    }
}

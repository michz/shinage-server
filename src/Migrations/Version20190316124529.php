<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190316124529 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE registration_codes (code VARCHAR(250) NOT NULL, created_by INT NULL, assign_organization INT NULL, created_date DATETIME NOT NULL, valid_until DATETIME NOT NULL, INDEX IDX_6A0761A5DE12AB56 (created_by), INDEX IDX_6A0761A59C245B88 (assign_organization), PRIMARY KEY(code)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE registration_codes ADD CONSTRAINT FK_6A0761A5DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id)');
        $this->addSql('ALTER TABLE registration_codes ADD CONSTRAINT FK_6A0761A59C245B88 FOREIGN KEY (assign_organization) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE registration_codes');
    }
}

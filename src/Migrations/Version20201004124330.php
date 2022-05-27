<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201004124330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add screen_commands for screen remote control';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE screen_commands (id INT AUTO_INCREMENT NOT NULL, screen_id VARCHAR(36) NOT NULL, user_id INT DEFAULT NULL, created DATETIME NOT NULL, fetched DATETIME DEFAULT NULL, command VARCHAR(255) NOT NULL, arguments LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_E64D874C41A67722 (screen_id), INDEX IDX_E64D874CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE screen_commands ADD CONSTRAINT FK_E64D874C41A67722 FOREIGN KEY (screen_id) REFERENCES screens (guid)');
        $this->addSql('ALTER TABLE screen_commands ADD CONSTRAINT FK_E64D874CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX screen_fetched_index ON screen_commands (screen_id, fetched)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX screen_fetched_index ON screen_commands');
        $this->addSql('DROP TABLE screen_commands');
    }
}

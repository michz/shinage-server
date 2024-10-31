<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220108100623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change column type from json_array to json to be compatible with newer Doctrine versions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users CHANGE backup_codes backup_codes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users CHANGE backup_codes backup_codes LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_bin` COMMENT \'(DC2Type:json_array)\'');
    }
}

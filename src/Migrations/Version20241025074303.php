<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241025074303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add timezone column to Screen entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE screens ADD timezone VARCHAR(32) NOT NULL DEFAULT \'UTC\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE screens DROP timezone');
    }
}

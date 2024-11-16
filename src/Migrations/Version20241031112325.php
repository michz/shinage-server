<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241031112325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Converts the roles of the users to json array';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP salt, CHANGE password password VARCHAR(255) DEFAULT NULL, CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');

        $connection = $this->connection;

        // Fetch the existing rows from the table
        $rows = $connection->fetchAllAssociative('SELECT `id` , `roles` FROM `users`');

        // Iterate over each row
        foreach ($rows as $row) {
            $id = $row['id'];
            $oldRolesSerialized = $row['roles'];

            // Unserialize data first since type array is serialize data
            $oldRoles = \unserialize($oldRolesSerialized);

            // Encode the roles as json
            $newRoles = \json_encode($oldRoles);

            // Update the row with the new encoded data
            $connection->executeQuery(
                'UPDATE `users` SET `roles` = :roles WHERE `id` = :id',
                [
                    'id' => $id,
                    'roles' => $newRoles,
                ],
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD salt VARCHAR(255) DEFAULT NULL, CHANGE password password VARCHAR(255) NOT NULL, CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');

        $connection = $this->connection;

        // Fetch the existing rows from the table
        $rows = $connection->fetchAllAssociative('SELECT `id` , `roles` FROM `users`');

        // Iterate over each row
        foreach ($rows as $row) {
            $id = $row['id'];
            $oldRolesJson = $row['roles'];

            // Unserialize data first since type array is serialize data
            $oldRoles = \json_decode($oldRolesJson);

            // Encode the roles as json
            $newRoles = \serialize($oldRoles);

            // Update the row with the new encoded data
            $connection->executeQuery(
                'UPDATE `users` SET `roles` = :roles WHERE `id` = :id',
                [
                    'id' => $id,
                    'roles' => $newRoles,
                ],
            );
        }
    }
}

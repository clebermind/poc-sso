<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240923012634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this method is called during the "up" migration
        $this->addSql("INSERT INTO user (username, password, roles) VALUES ('admin@simpro.com', md5('simpro123'), '[\"ROLE_ADMIN\"]')");
        $this->addSql("INSERT INTO user (username, password, roles) VALUES ('user@simpro.com', md5('simpro123'), '[]')");
        $this->addSql("INSERT INTO user (username, password, roles) VALUES ('simpro_sso@hotmail.com', md5('simpro123'), '[\"ROLE_ADMIN\"]')");
    }

    public function down(Schema $schema): void
    {
        // this method is called during the "down" migration
        $this->addSql("DELETE FROM user WHERE username IN ('admin@simpro.com', 'user@simpro.com', 'simpro_sso@hotmail.com')");
    }
}

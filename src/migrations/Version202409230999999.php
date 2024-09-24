<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202409230999999 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this method is called during the "up" migration
        $this->addSql("INSERT INTO user (username, password, roles) VALUES ('clebermind@hotmail.com', md5('simpro123'), '[\"ROLE_ADMIN\"]')");
        $this->addSql("INSERT INTO user (username, password, roles) VALUES ('paulinharoso@hotmail.com', md5('simpro123'), '[]')");
    }

    public function down(Schema $schema): void
    {
        // this method is called during the "down" migration
        $this->addSql("DELETE FROM user WHERE username IN ('clebermind@hotmail.com', 'paulinharoso@hotmail.com')");
    }
}

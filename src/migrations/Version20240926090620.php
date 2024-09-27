<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240926090620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE identity_provider (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, class_name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, redirect_url VARCHAR(255) NULL, scope JSON NOT NULL, tenant VARCHAR(255) NOT NULL, client_id VARCHAR(255) NOT NULL, client_secret VARCHAR(255) NOT NULL, extra_fields JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0679 ON user (username)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE identity_provider');
        $this->addSql('DROP INDEX UNIQ_8D93D649F85E0679 ON `user`');
    }
}

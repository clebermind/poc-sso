<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240927095720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert Auth0 as IDP';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $domain = '--domain--';
        $clientId = '--client-id--';
        $clientSecret = '--client-secret--';

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO identity_provider (name, class_name, client_id, client_secret, extra_fields) VALUES ('Auth0', 'Auth0', '[\"openid\", \"email\", \"profile\", \"offline_access\"]', '{$clientId}', '{$clientSecret}', '{\"domain\": \"{domain}\"}')");

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM identity_provider WHERE class_name = 'Auth0'");
    }
}

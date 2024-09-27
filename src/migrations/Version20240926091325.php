<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240926091325 extends AbstractMigration
{

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
         $tenant = '--tenant--';
         $clientId = '--client-id--';
         $clientSecret = '--client-secret--';

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO identity_provider (name, class_name, scope, tenant, client_id, client_secret, extra_fields) VALUES ('Microsoft Entra ID (formerly Azure Active Directory)', 'MicrosoftEntraId', '[\"openid\", \"profile\", \"offline_access\"]', '{$tenant}', '{$clientId}', '{$clientSecret}', '{}')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM identity_provider WHERE class_name = 'MicrosoftEntraId'");
    }
}

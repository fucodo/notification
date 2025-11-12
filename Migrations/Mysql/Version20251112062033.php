<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251112062033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1027Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1027Platform'."
        );

        $this->addSql('TRUNCATE TABLE fucodo_notification_domain_model_notification');
        $this->addSql('ALTER TABLE fucodo_notification_domain_model_notification ADD account VARCHAR(40) NOT NULL, ADD expirationdate DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP user, CHANGE app app VARCHAR(128) NOT NULL');
        $this->addSql('ALTER TABLE fucodo_notification_domain_model_notification ADD CONSTRAINT FK_790BE3377D3656A4 FOREIGN KEY (account) REFERENCES neos_flow_security_account (persistence_object_identifier) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_fucodo_notification_account ON fucodo_notification_domain_model_notification (account)');
        $this->addSql('CREATE INDEX idx_fucodo_notification_expirationDate ON fucodo_notification_domain_model_notification (expirationDate)');
        $this->addSql('CREATE INDEX idx_fucodo_notification_timestamp ON fucodo_notification_domain_model_notification (timestamp)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1027Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1027Platform'."
        );

        $this->addSql('ALTER TABLE fucodo_notification_domain_model_notification DROP FOREIGN KEY FK_790BE3377D3656A4');
        $this->addSql('DROP INDEX idx_fucodo_notification_account ON fucodo_notification_domain_model_notification');
        $this->addSql('DROP INDEX idx_fucodo_notification_expirationDate ON fucodo_notification_domain_model_notification');
        $this->addSql('DROP INDEX idx_fucodo_notification_timestamp ON fucodo_notification_domain_model_notification');
        $this->addSql('ALTER TABLE fucodo_notification_domain_model_notification ADD user VARCHAR(64) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP account, DROP expirationdate, CHANGE app app VARCHAR(32) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}

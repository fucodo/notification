<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251112064506 extends AbstractMigration
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


        $this->addSql('DROP INDEX idx_fucodo_notification_timestamp ON fucodo_notification_domain_model_notification');
        $this->addSql('ALTER TABLE fucodo_notification_domain_model_notification ADD createdat DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP timestamp');
        $this->addSql('CREATE INDEX idx_fucodo_notification_createdAt ON fucodo_notification_domain_model_notification (createdAt)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1027Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1027Platform'."
        );

        $this->addSql('DROP INDEX idx_fucodo_notification_createdAt ON fucodo_notification_domain_model_notification');
        $this->addSql('ALTER TABLE fucodo_notification_domain_model_notification ADD timestamp INT NOT NULL, DROP createdat');
        $this->addSql('CREATE INDEX idx_fucodo_notification_timestamp ON fucodo_notification_domain_model_notification (timestamp)');
    }
}

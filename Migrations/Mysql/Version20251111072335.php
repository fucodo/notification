<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251111072335 extends AbstractMigration
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

        $this->addSql('ALTER TABLE fucodo_notification_domain_model_notification ADD id INT AUTO_INCREMENT NOT NULL, ADD app VARCHAR(32) NOT NULL, ADD user VARCHAR(64) NOT NULL, ADD timestamp INT NOT NULL, ADD object_type VARCHAR(64) NOT NULL, ADD object_id VARCHAR(64) NOT NULL, ADD subject VARCHAR(64) NOT NULL, ADD subject_parameters LONGTEXT DEFAULT NULL, ADD message VARCHAR(64) DEFAULT NULL, ADD message_parameters LONGTEXT DEFAULT NULL, ADD link VARCHAR(4000) DEFAULT NULL, ADD icon VARCHAR(4000) DEFAULT NULL, ADD actions LONGTEXT DEFAULT NULL, DROP persistence_object_identifier, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1027Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1027Platform'."
        );

        $this->addSql('ALTER TABLE fucodo_notification_domain_model_notification MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE fucodo_notification_domain_model_notification DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE fucodo_notification_domain_model_notification ADD persistence_object_identifier VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP id, DROP app, DROP user, DROP timestamp, DROP object_type, DROP object_id, DROP subject, DROP subject_parameters, DROP message, DROP message_parameters, DROP link, DROP icon, DROP actions');
        $this->addSql('ALTER TABLE fucodo_notification_domain_model_notification ADD PRIMARY KEY (persistence_object_identifier)');

    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201201050652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        //$this->addSql('ALTER TABLE pointing_location ADD CONSTRAINT FK_18267EFA97D1AC3 FOREIGN KEY (enterprise_id) REFERENCES enterprise (id)');
        //$this->addSql('CREATE INDEX IDX_18267EFA97D1AC3 ON pointing_location (enterprise_id)');
        //$this->addSql('ALTER TABLE team CHANGE responsible_id responsible_id INT NOT NULL, CHANGE enterprise_id enterprise_id INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP attribut');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pointing_location DROP FOREIGN KEY FK_18267EFA97D1AC3');
        $this->addSql('DROP INDEX IDX_18267EFA97D1AC3 ON pointing_location');
        $this->addSql('ALTER TABLE team CHANGE responsible_id responsible_id INT DEFAULT NULL, CHANGE enterprise_id enterprise_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD attribut VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}

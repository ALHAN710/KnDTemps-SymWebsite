<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201124114948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        //$this->addSql('CREATE TABLE pointing_location (id INT AUTO_INCREMENT NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, ray DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        //$this->addSql('ALTER TABLE team CHANGE responsible_id responsible_id INT NOT NULL, CHANGE enterprise_id enterprise_id INT NOT NULL');
        $this->addSql('ALTER TABLE user ADD pointing_location_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649EDF54DC0 FOREIGN KEY (pointing_location_id) REFERENCES pointing_location (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649EDF54DC0 ON user (pointing_location_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649EDF54DC0');
        $this->addSql('DROP TABLE pointing_location');
        $this->addSql('ALTER TABLE team CHANGE responsible_id responsible_id INT DEFAULT NULL, CHANGE enterprise_id enterprise_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_8D93D649EDF54DC0 ON user');
        $this->addSql('ALTER TABLE user DROP pointing_location_id');
    }
}

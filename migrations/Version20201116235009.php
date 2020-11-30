<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201116235009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pointing CHANGE time_out time_out DATETIME DEFAULT NULL');
        //$this->addSql('ALTER TABLE team CHANGE responsible_id responsible_id INT NOT NULL, CHANGE enterprise_id enterprise_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pointing CHANGE time_out time_out DATETIME NOT NULL');
        $this->addSql('ALTER TABLE team CHANGE responsible_id responsible_id INT DEFAULT NULL, CHANGE enterprise_id enterprise_id INT DEFAULT NULL');
    }
}

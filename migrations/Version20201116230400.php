<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201116230400 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pointing ADD time_out DATETIME NOT NULL, DROP type, CHANGE time time_in DATETIME NOT NULL');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FA97D1AC3 FOREIGN KEY (enterprise_id) REFERENCES enterprise (id)');
        $this->addSql('CREATE INDEX IDX_C4E0A61FA97D1AC3 ON team (enterprise_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pointing ADD time DATETIME NOT NULL, ADD type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP time_in, DROP time_out');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FA97D1AC3');
        $this->addSql('DROP INDEX IDX_C4E0A61FA97D1AC3 ON team');
    }
}

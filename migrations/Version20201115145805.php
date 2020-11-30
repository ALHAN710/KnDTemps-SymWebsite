<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201115145805 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE office ADD enterprise_id INT NOT NULL');
        $this->addSql('ALTER TABLE office ADD CONSTRAINT FK_74516B02A97D1AC3 FOREIGN KEY (enterprise_id) REFERENCES enterprise (id)');
        $this->addSql('CREATE INDEX IDX_74516B02A97D1AC3 ON office (enterprise_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE office DROP FOREIGN KEY FK_74516B02A97D1AC3');
        $this->addSql('DROP INDEX IDX_74516B02A97D1AC3 ON office');
        $this->addSql('ALTER TABLE office DROP enterprise_id');
    }
}

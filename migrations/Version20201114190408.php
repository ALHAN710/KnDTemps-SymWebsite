<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201114190408 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE enterprise (id INT AUTO_INCREMENT NOT NULL, subscription_id INT DEFAULT NULL, social_reason VARCHAR(255) NOT NULL, niu VARCHAR(255) DEFAULT NULL, rccm VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, logo VARCHAR(255) DEFAULT NULL, subscription_duration INT NOT NULL, subscribe_at DATETIME DEFAULT NULL, country VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, town VARCHAR(255) NOT NULL, is_activated TINYINT(1) NOT NULL, INDEX IDX_B1B36A039A1887DC (subscription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE office (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, hourly_salary DOUBLE PRECISION DEFAULT NULL, monthly_salary DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pointing (id INT AUTO_INCREMENT NOT NULL, employee_id INT NOT NULL, time DATETIME NOT NULL, type VARCHAR(255) NOT NULL, statut VARCHAR(20) NOT NULL, INDEX IDX_368690FD8C03F15C (employee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_user (role_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_332CA4DDD60322AC (role_id), INDEX IDX_332CA4DDA76ED395 (user_id), PRIMARY KEY(role_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscription (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, sheet_number INT NOT NULL, product_ref_number INT NOT NULL, tarifs JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, responsible_id INT NOT NULL, name VARCHAR(180) NOT NULL, INDEX IDX_C4E0A61F602AD315 (responsible_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, office_id INT DEFAULT NULL, enterprise_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, roles JSON NOT NULL, hash VARCHAR(255) NOT NULL, phone_number VARCHAR(255) NOT NULL, country_code VARCHAR(255) DEFAULT NULL, verification_code VARCHAR(255) DEFAULT NULL, verified TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, avatar VARCHAR(255) DEFAULT NULL, grade VARCHAR(10) NOT NULL, attribut VARCHAR(20) NOT NULL, wtperday INT DEFAULT NULL, registration_number VARCHAR(100) DEFAULT NULL, commission DOUBLE PRECISION DEFAULT NULL, statut VARCHAR(20) NOT NULL, hiring_date DATETIME DEFAULT NULL, birth_date DATETIME DEFAULT NULL, sex VARCHAR(15) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649FFA0C224 (office_id), INDEX IDX_8D93D649A97D1AC3 (enterprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE enterprise ADD CONSTRAINT FK_B1B36A039A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id)');
        $this->addSql('ALTER TABLE pointing ADD CONSTRAINT FK_368690FD8C03F15C FOREIGN KEY (employee_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE role_user ADD CONSTRAINT FK_332CA4DDD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_user ADD CONSTRAINT FK_332CA4DDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F602AD315 FOREIGN KEY (responsible_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649FFA0C224 FOREIGN KEY (office_id) REFERENCES office (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A97D1AC3 FOREIGN KEY (enterprise_id) REFERENCES enterprise (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A97D1AC3');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649FFA0C224');
        $this->addSql('ALTER TABLE role_user DROP FOREIGN KEY FK_332CA4DDD60322AC');
        $this->addSql('ALTER TABLE enterprise DROP FOREIGN KEY FK_B1B36A039A1887DC');
        $this->addSql('ALTER TABLE pointing DROP FOREIGN KEY FK_368690FD8C03F15C');
        $this->addSql('ALTER TABLE role_user DROP FOREIGN KEY FK_332CA4DDA76ED395');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F602AD315');
        $this->addSql('DROP TABLE enterprise');
        $this->addSql('DROP TABLE office');
        $this->addSql('DROP TABLE pointing');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE role_user');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE user');
    }
}

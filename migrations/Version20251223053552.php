<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251223053552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE candidacy (id INT AUTO_INCREMENT NOT NULL, date_candidacy DATE NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE candidate (id INT AUTO_INCREMENT NOT NULL, cv_file LONGBLOB NOT NULL, lm_file LONGBLOB NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, company_name VARCHAR(50) NOT NULL, localisation VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE department (id INT AUTO_INCREMENT NOT NULL, department_name VARCHAR(50) NOT NULL, company_id INT DEFAULT NULL, INDEX IDX_CD1DE18A979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE interview (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE job_offer (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(100) NOT NULL, offer_type VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, date_creation DATE NOT NULL, deadline DATE NOT NULL, user_id INT NOT NULL, company_id INT NOT NULL, department_id INT NOT NULL, INDEX IDX_288A3A4EA76ED395 (user_id), INDEX IDX_288A3A4E979B1AD6 (company_id), INDEX IDX_288A3A4EAE80F5DF (department_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, email VARCHAR(50) NOT NULL, password VARCHAR(50) NOT NULL, role_id INT DEFAULT NULL, department_id INT DEFAULT NULL, INDEX IDX_1483A5E9D60322AC (role_id), INDEX IDX_1483A5E9AE80F5DF (department_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4EAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A979B1AD6');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4EA76ED395');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4E979B1AD6');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4EAE80F5DF');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9D60322AC');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9AE80F5DF');
        $this->addSql('DROP TABLE candidacy');
        $this->addSql('DROP TABLE candidate');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP TABLE interview');
        $this->addSql('DROP TABLE job_offer');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260101060048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE job_offer_candidate (job_offer_id INT NOT NULL, candidate_id INT NOT NULL, INDEX IDX_80EBBCEF3481D195 (job_offer_id), INDEX IDX_80EBBCEF91BD8781 (candidate_id), PRIMARY KEY (job_offer_id, candidate_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE job_offer_candidate ADD CONSTRAINT FK_80EBBCEF3481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_offer_candidate ADD CONSTRAINT FK_80EBBCEF91BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE candidacy ADD CONSTRAINT FK_D930569D3481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id)');
        $this->addSql('ALTER TABLE candidate DROP INDEX IDX_C8B28E44A76ED395, ADD UNIQUE INDEX UNIQ_C8B28E44A76ED395 (user_id)');
        $this->addSql('ALTER TABLE candidate ADD nom VARCHAR(191) DEFAULT NULL, ADD prenom VARCHAR(191) DEFAULT NULL, ADD email VARCHAR(191) NOT NULL, ADD telephone VARCHAR(20) DEFAULT NULL, ADD adresse VARCHAR(191) DEFAULT NULL, ADD ville VARCHAR(191) DEFAULT NULL, ADD code_postal VARCHAR(20) DEFAULT NULL, ADD linkedin VARCHAR(191) DEFAULT NULL, ADD facebook VARCHAR(191) DEFAULT NULL, ADD nationalite VARCHAR(100) DEFAULT NULL, ADD genre VARCHAR(20) DEFAULT NULL, ADD date_naissance DATE DEFAULT NULL, ADD password VARCHAR(255) DEFAULT NULL, CHANGE cv_file cv_file LONGBLOB DEFAULT NULL, CHANGE lm_file lm_file LONGBLOB DEFAULT NULL, CHANGE status status VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidate ADD CONSTRAINT FK_C8B28E44A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C8B28E44E7927C74 ON candidate (email)');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4EAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE users CHANGE password password VARCHAR(255) NOT NULL, CHANGE role_id role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_offer_candidate DROP FOREIGN KEY FK_80EBBCEF3481D195');
        $this->addSql('ALTER TABLE job_offer_candidate DROP FOREIGN KEY FK_80EBBCEF91BD8781');
        $this->addSql('DROP TABLE job_offer_candidate');
        $this->addSql('ALTER TABLE candidacy DROP FOREIGN KEY FK_D930569D3481D195');
        $this->addSql('ALTER TABLE candidate DROP INDEX UNIQ_C8B28E44A76ED395, ADD INDEX IDX_C8B28E44A76ED395 (user_id)');
        $this->addSql('ALTER TABLE candidate DROP FOREIGN KEY FK_C8B28E44A76ED395');
        $this->addSql('DROP INDEX UNIQ_C8B28E44E7927C74 ON candidate');
        $this->addSql('ALTER TABLE candidate DROP nom, DROP prenom, DROP email, DROP telephone, DROP adresse, DROP ville, DROP code_postal, DROP linkedin, DROP facebook, DROP nationalite, DROP genre, DROP date_naissance, DROP password, CHANGE status status VARCHAR(50) NOT NULL, CHANGE cv_file cv_file LONGBLOB NOT NULL, CHANGE lm_file lm_file LONGBLOB NOT NULL');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A979B1AD6');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4EA76ED395');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4E979B1AD6');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4EAE80F5DF');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9D60322AC');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9AE80F5DF');
        $this->addSql('ALTER TABLE users CHANGE password password VARCHAR(50) NOT NULL, CHANGE role_id role_id INT NOT NULL');
    }
}

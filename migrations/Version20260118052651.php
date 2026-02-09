<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260118052651 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidacy ADD CONSTRAINT FK_D930569D3481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id)');
        $this->addSql('ALTER TABLE candidacy ADD CONSTRAINT FK_D930569DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE candidate ADD CONSTRAINT FK_C8B28E44A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FD60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE department ADD manager_id INT NOT NULL');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A783E3463 FOREIGN KEY (manager_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_CD1DE18A783E3463 ON department (manager_id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4EAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE job_offer_candidate ADD CONSTRAINT FK_80EBBCEF3481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_offer_candidate ADD CONSTRAINT FK_80EBBCEF91BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE new_user ADD CONSTRAINT FK_797E6294979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE new_user ADD CONSTRAINT FK_797E6294D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE recruitment_request ADD CONSTRAINT FK_1F051FBE783E3463 FOREIGN KEY (manager_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE recruitment_request ADD CONSTRAINT FK_1F051FBEAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE recruitment_request ADD CONSTRAINT FK_1F051FBE979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_57698A6A8CDE5729 ON role (type)');
        $this->addSql('ALTER TABLE users CHANGE email email VARCHAR(100) NOT NULL, CHANGE password password VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidacy DROP FOREIGN KEY FK_D930569D3481D195');
        $this->addSql('ALTER TABLE candidacy DROP FOREIGN KEY FK_D930569DA76ED395');
        $this->addSql('ALTER TABLE candidate DROP FOREIGN KEY FK_C8B28E44A76ED395');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094FD60322AC');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A979B1AD6');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A783E3463');
        $this->addSql('DROP INDEX IDX_CD1DE18A783E3463 ON department');
        $this->addSql('ALTER TABLE department DROP manager_id');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4EA76ED395');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4E979B1AD6');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4EAE80F5DF');
        $this->addSql('ALTER TABLE job_offer_candidate DROP FOREIGN KEY FK_80EBBCEF3481D195');
        $this->addSql('ALTER TABLE job_offer_candidate DROP FOREIGN KEY FK_80EBBCEF91BD8781');
        $this->addSql('ALTER TABLE new_user DROP FOREIGN KEY FK_797E6294979B1AD6');
        $this->addSql('ALTER TABLE new_user DROP FOREIGN KEY FK_797E6294D60322AC');
        $this->addSql('ALTER TABLE recruitment_request DROP FOREIGN KEY FK_1F051FBE783E3463');
        $this->addSql('ALTER TABLE recruitment_request DROP FOREIGN KEY FK_1F051FBEAE80F5DF');
        $this->addSql('ALTER TABLE recruitment_request DROP FOREIGN KEY FK_1F051FBE979B1AD6');
        $this->addSql('DROP INDEX UNIQ_57698A6A8CDE5729 ON role');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9D60322AC');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9AE80F5DF');
        $this->addSql('DROP INDEX UNIQ_1483A5E9E7927C74 ON users');
        $this->addSql('ALTER TABLE users CHANGE email email VARCHAR(50) NOT NULL, CHANGE password password VARCHAR(50) NOT NULL');
    }
}

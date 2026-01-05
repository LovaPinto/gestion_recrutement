<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251228124754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FD60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE new_user ADD role_id INT NOT NULL');
        $this->addSql('ALTER TABLE new_user ADD CONSTRAINT FK_797E6294979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE new_user ADD CONSTRAINT FK_797E6294D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('CREATE INDEX IDX_797E6294D60322AC ON new_user (role_id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094FD60322AC');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A979B1AD6');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4EA76ED395');
        $this->addSql('ALTER TABLE new_user DROP FOREIGN KEY FK_797E6294979B1AD6');
        $this->addSql('ALTER TABLE new_user DROP FOREIGN KEY FK_797E6294D60322AC');
        $this->addSql('DROP INDEX IDX_797E6294D60322AC ON new_user');
        $this->addSql('ALTER TABLE new_user DROP role_id');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9D60322AC');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9AE80F5DF');
    }
}

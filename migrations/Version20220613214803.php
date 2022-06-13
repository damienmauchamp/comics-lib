<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220613214803 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE issue_backup (id INT AUTO_INCREMENT NOT NULL, issue_id INT NOT NULL, number INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, INDEX IDX_CD005B8B5E7AA58C (issue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE issue_backup ADD CONSTRAINT FK_CD005B8B5E7AA58C FOREIGN KEY (issue_id) REFERENCES issue (id)');
        $this->addSql('ALTER TABLE item_issue ADD notes LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE issue_backup');
        $this->addSql('ALTER TABLE item_issue DROP notes');
    }
}

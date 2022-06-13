<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220613152021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE imprint (id INT AUTO_INCREMENT NOT NULL, publisher_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_E4DC41B740C86FCE (publisher_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, collection_id INT DEFAULT NULL, number INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, release_date DATE DEFAULT NULL, isbn VARCHAR(20) DEFAULT NULL, image VARCHAR(255) NOT NULL, special TINYINT(1) NOT NULL, notes LONGTEXT NOT NULL, INDEX IDX_1F1B251E514956FD (collection_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item_collection (id INT AUTO_INCREMENT NOT NULL, imprint_id INT DEFAULT NULL, type_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, official TINYINT(1) NOT NULL, INDEX IDX_41FC4D38C057CDFA (imprint_id), INDEX IDX_41FC4D38C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item_issue (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, issue_id INT NOT NULL, number VARCHAR(10) DEFAULT NULL, INDEX IDX_380A38EE126F525E (item_id), INDEX IDX_380A38EE5E7AA58C (issue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE imprint ADD CONSTRAINT FK_E4DC41B740C86FCE FOREIGN KEY (publisher_id) REFERENCES publisher (id)');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E514956FD FOREIGN KEY (collection_id) REFERENCES item_collection (id)');
        $this->addSql('ALTER TABLE item_collection ADD CONSTRAINT FK_41FC4D38C057CDFA FOREIGN KEY (imprint_id) REFERENCES imprint (id)');
        $this->addSql('ALTER TABLE item_collection ADD CONSTRAINT FK_41FC4D38C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE item_issue ADD CONSTRAINT FK_380A38EE126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE item_issue ADD CONSTRAINT FK_380A38EE5E7AA58C FOREIGN KEY (issue_id) REFERENCES issue (id)');
        $this->addSql('ALTER TABLE publisher ADD affiliated_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE publisher ADD CONSTRAINT FK_9CE8D5463AFABA9D FOREIGN KEY (affiliated_id) REFERENCES publisher (id)');
        $this->addSql('CREATE INDEX IDX_9CE8D5463AFABA9D ON publisher (affiliated_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_collection DROP FOREIGN KEY FK_41FC4D38C057CDFA');
        $this->addSql('ALTER TABLE item_issue DROP FOREIGN KEY FK_380A38EE126F525E');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E514956FD');
        $this->addSql('ALTER TABLE item_collection DROP FOREIGN KEY FK_41FC4D38C54C8C93');
        $this->addSql('DROP TABLE imprint');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE item_collection');
        $this->addSql('DROP TABLE item_issue');
        $this->addSql('DROP TABLE type');
        $this->addSql('ALTER TABLE publisher DROP FOREIGN KEY FK_9CE8D5463AFABA9D');
        $this->addSql('DROP INDEX IDX_9CE8D5463AFABA9D ON publisher');
        $this->addSql('ALTER TABLE publisher DROP affiliated_id');
    }
}

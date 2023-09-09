<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230908200554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE specialty (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD specialty_id INT DEFAULT NULL, ADD name VARCHAR(255) NOT NULL, ADD avatar_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6499A353316 FOREIGN KEY (specialty_id) REFERENCES specialty (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6499A353316 ON user (specialty_id)');
        $this->addSql('INSERT INTO specialty (`name`) VALUES ("Surgery"), ("Psychiatry"), ("Neurology"), ("Pediatrics"), ("Dermatology"), ("Ophthalmology"), ("Urology"), ("Cardiology"), ("Orthopedics"), ("Nephrology")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D6499A353316');
        $this->addSql('DROP TABLE specialty');
        $this->addSql('DROP INDEX IDX_8D93D6499A353316 ON `user`');
        $this->addSql('ALTER TABLE `user` DROP specialty_id, DROP name, DROP avatar_path');
    }
}

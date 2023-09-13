<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230913222554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add schedule_slot table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE schedule_slot (id INT AUTO_INCREMENT NOT NULL, doctor_id INT NOT NULL, patient_id INT DEFAULT NULL, start DATETIME NOT NULL, end DATETIME NOT NULL, price DOUBLE PRECISION NOT NULL, INDEX IDX_4C46003C87F4FB17 (doctor_id), INDEX IDX_4C46003C6B899279 (patient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE schedule_slot ADD CONSTRAINT FK_4C46003C87F4FB17 FOREIGN KEY (doctor_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE schedule_slot ADD CONSTRAINT FK_4C46003C6B899279 FOREIGN KEY (patient_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE schedule_slot DROP FOREIGN KEY FK_4C46003C87F4FB17');
        $this->addSql('ALTER TABLE schedule_slot DROP FOREIGN KEY FK_4C46003C6B899279');
        $this->addSql('DROP TABLE schedule_slot');
    }
}

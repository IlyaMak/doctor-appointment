<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231105230904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_approved column to the user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD is_approved TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP is_approved');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231105192632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add resources folder to the relative avatar_path value only';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE user SET avatar_path=CONCAT("/resources/", avatar_path) WHERE avatar_path NOT LIKE "http%"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE user SET avatar_path=REPLACE(avatar_path, "/resources/", "")');
    }
}

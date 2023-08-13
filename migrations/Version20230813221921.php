<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230813221921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE race CHANGE name title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE result CHANGE time finish_time TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE race CHANGE title name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE result CHANGE finish_time time TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\'');
    }
}

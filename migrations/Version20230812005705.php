<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230812005705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE race (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', average_finish_medium TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', average_finish_long TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE result (id INT AUTO_INCREMENT NOT NULL, race_id INT DEFAULT NULL, full_name VARCHAR(255) NOT NULL, distance VARCHAR(255) NOT NULL, time TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', age_category VARCHAR(255) NOT NULL, overall_placement INT DEFAULT NULL, age_category_placement INT DEFAULT NULL, INDEX IDX_136AC1136E59D40D (race_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE result ADD CONSTRAINT FK_136AC1136E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE result DROP FOREIGN KEY FK_136AC1136E59D40D');
        $this->addSql('DROP TABLE race');
        $this->addSql('DROP TABLE result');
    }
}

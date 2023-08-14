<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230813175527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD nom VARCHAR(255) NOT NULL, ADD prenom VARCHAR(255) NOT NULL, ADD img VARCHAR(255) NOT NULL, ADD role VARCHAR(255) NOT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD date_naissance DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD genre VARCHAR(255) NOT NULL, ADD is_banned INT DEFAULT 0 NOT NULL, ADD is_verified INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP nom, DROP prenom, DROP img, DROP role, DROP created_at, DROP date_naissance, DROP genre, DROP is_banned, DROP is_verified');
    }
}
